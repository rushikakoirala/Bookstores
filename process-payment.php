<?php
session_start();
include_once('includes/config.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log the received data (for debugging)
error_log("Received POST data: " . print_r($_POST, true));
error_log("Session ID: " . $_SESSION['id']);

// Check if user is logged in
if(!isset($_SESSION['id']) || strlen($_SESSION['id']) == 0){
    echo json_encode(['success' => false, 'message' => 'User not logged in', 'debug' => 'session_id: ' . ($_SESSION['id'] ?? 'empty')]);
    exit();
}

// Get POST data
$order_no = $_POST['order'] ?? '';
$amount = $_POST['amount'] ?? 0;
$payment_method = $_POST['payment_method'] ?? 'cod';
$action = $_POST['action'] ?? '';
$user_id = $_SESSION['id'];

error_log("Parsed data - Order: $order_no, Amount: $amount, UserID: $user_id");

// Validate data
if(empty($order_no)) {
    echo json_encode(['success' => false, 'message' => 'Order number is empty', 'debug' => 'order_no: ' . $order_no]);
    exit();
}

if($amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Amount must be greater than 0', 'debug' => 'amount: ' . $amount]);
    exit();
}

if(empty($user_id)) {
    echo json_encode(['success' => false, 'message' => 'User ID is empty', 'debug' => 'user_id: ' . $user_id]);
    exit();
}

// Check database connection
if(!$con) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Start transaction
mysqli_begin_transaction($con);

try {
    // Check if user exists
    $check_user = mysqli_query($con, "SELECT id FROM users WHERE id = '$user_id'");
    if(!$check_user || mysqli_num_rows($check_user) == 0) {
        throw new Exception("User not found in database");
    }
    
    // 1. Insert order (simplified - remove shipping_address if column doesn't exist)
    $sql_order = "INSERT INTO orders (order_no, user_id, total_amount, payment_method, payment_status, order_status, created_at) 
                  VALUES ('$order_no', '$user_id', '$amount', '$payment_method', 'pending', 'confirmed', NOW())";
    
    error_log("SQL Order Query: " . $sql_order); // Debug log
    
    if(!mysqli_query($con, $sql_order)) {
        $error_msg = "Failed to create order: " . mysqli_error($con);
        error_log($error_msg);
        
        // Try alternative query without shipping_address
        $sql_order_alt = "INSERT INTO orders (order_no, user_id, total_amount, payment_method, payment_status, order_status) 
                          VALUES ('$order_no', '$user_id', '$amount', '$payment_method', 'pending', 'confirmed')";
        
        error_log("Trying alternative query: " . $sql_order_alt);
        
        if(!mysqli_query($con, $sql_order_alt)) {
            throw new Exception("Failed to create order (alt): " . mysqli_error($con));
        }
    }
    
    $order_id = mysqli_insert_id($con);
    error_log("Order created with ID: " . $order_id);
    
    // 2. Get cart items
    $cart_query = mysqli_query($con, "SELECT cart.*, books.bookName, books.bookPrice 
                                     FROM cart 
                                     JOIN books ON cart.bookID = books.id 
                                     WHERE cart.userID = '$user_id'");
    
    if(!$cart_query) {
        error_log("Cart query failed: " . mysqli_error($con));
        throw new Exception("Failed to fetch cart items: " . mysqli_error($con));
    }
    
    $cart_count = mysqli_num_rows($cart_query);
    error_log("Cart items found: " . $cart_count);
    
    if($cart_count > 0) {
        // Insert order items
        while($item = mysqli_fetch_array($cart_query)) {
            $item_total = $item['bookPrice'] * $item['quantity'];
            
            // Check if order_items table exists and has correct columns
            $sql_item = "INSERT INTO order_items (order_id, book_id, book_name, quantity, price, total_price) 
                         VALUES ('$order_id', '{$item['bookID']}', '" . mysqli_real_escape_string($con, $item['bookName']) . "', 
                                 '{$item['quantity']}', '{$item['bookPrice']}', '$item_total')";
            
            error_log("Inserting item: " . $sql_item);
            
            if(!mysqli_query($con, $sql_item)) {
                error_log("Failed to insert item: " . mysqli_error($con));
                
                // Try alternative without book_id if column doesn't exist
                $sql_item_alt = "INSERT INTO order_items (order_id, book_name, quantity, price, total_price) 
                                 VALUES ('$order_id', '" . mysqli_real_escape_string($con, $item['bookName']) . "', 
                                         '{$item['quantity']}', '{$item['bookPrice']}', '$item_total')";
                
                if(!mysqli_query($con, $sql_item_alt)) {
                    throw new Exception("Failed to add order item: " . mysqli_error($con));
                }
            }
        }
    }
    
    // 3. Clear cart
    $sql_clear_cart = "DELETE FROM cart WHERE userID = '$user_id'";
    if(!mysqli_query($con, $sql_clear_cart)) {
        error_log("Failed to clear cart: " . mysqli_error($con));
        // Don't throw exception here - order is already created
    }
    
    // 4. Clear session data
    unset($_SESSION['cart_count']);
    unset($_SESSION['gtotal']);
    unset($_SESSION['address']);
    unset($_SESSION['orderno']);
    
    // Commit transaction
    mysqli_commit($con);
    
    error_log("Order processed successfully: " . $order_no);
    
    echo json_encode([
        'success' => true,
        'order_no' => $order_no,
        'order_id' => $order_id,
        'message' => 'Order placed successfully'
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($con);
    
    error_log("Error in process-payment: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Order processing failed: ' . $e->getMessage(),
        'debug' => [
            'order_no' => $order_no,
            'amount' => $amount,
            'user_id' => $user_id
        ]
    ]);
}
?>