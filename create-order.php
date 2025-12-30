<?php
session_start();
include_once('includes/config.php');

// Get POST data
$order_no = $_POST['order'] ?? '';
$amount = $_POST['amount'] ?? 0;
$payment_method = $_POST['payment_method'] ?? '';
$status = $_POST['status'] ?? 'pending';
$user_id = $_SESSION['id'] ?? 0;

// Validate
if(empty($user_id)) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

if(empty($order_no) || $amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order data']);
    exit();
}

// Save order to database
$sql = "INSERT INTO orders (order_no, user_id, total_amount, payment_method, payment_status, order_status, created_at) 
        VALUES ('$order_no', '$user_id', '$amount', '$payment_method', '$status', 'confirmed', NOW())";

if(mysqli_query($con, $sql)) {
    $order_id = mysqli_insert_id($con);
    
    // Save order items
    $cart_query = mysqli_query($con, "SELECT cart.*, books.bookName, books.bookPrice 
                                     FROM cart 
                                     JOIN books ON cart.bookID = books.id 
                                     WHERE cart.userID = '$user_id'");
    
    while($item = mysqli_fetch_array($cart_query)) {
        $item_total = $item['bookPrice'] * $item['quantity'];
        mysqli_query($con, "INSERT INTO order_items (order_id, book_id, book_name, quantity, price, total_price) 
                           VALUES ('$order_id', '{$item['bookID']}', '" . mysqli_real_escape_string($con, $item['bookName']) . "', 
                                   '{$item['quantity']}', '{$item['bookPrice']}', '$item_total')");
    }
    
    // For eSewa, don't clear cart yet (will clear after successful payment)
    if($payment_method != 'esewa') {
        // Clear cart for COD
        mysqli_query($con, "DELETE FROM cart WHERE userID = '$user_id'");
        unset($_SESSION['cart_count']);
    }
    
    echo json_encode([
        'success' => true,
        'order_no' => $order_no,
        'order_id' => $order_id,
        'message' => 'Order created successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create order: ' . mysqli_error($con)
    ]);
}
?>