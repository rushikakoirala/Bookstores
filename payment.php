<?php
// Debug mode - remove in production
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include_once('includes/config.php');

// Check if user is logged in
if(strlen($_SESSION['id'])==0){
    header('location:logout.php');
    exit();
}

// Check if address is selected
if(!isset($_SESSION['address']) || $_SESSION['address']==0){
    header('location:checkout.php');
    exit();
}

// Generate order number
$orderno = mt_rand(100000000,999999999);
$_SESSION['orderno'] = $orderno;

// Get total amount from cart
$user_id = $_SESSION['id'];

// Check database connection
if (!isset($con) || !$con) {
    die("Database connection failed. Please check your configuration.");
}

// DIRECT FIX: Using your exact column names
$sql = "SELECT SUM(cart.productQty * books.bookPrice) as total 
        FROM cart 
        JOIN books ON cart.productId = books.id 
        WHERE cart.userID = '$user_id'";

// Debug: Show the SQL query
// echo "SQL Query: " . $sql . "<br>";

$query = mysqli_query($con, $sql);

if ($query === false) {
    // Show detailed error
    echo "<div style='padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'>";
    echo "<h3>SQL Error Details:</h3>";
    echo "<p><strong>Error:</strong> " . mysqli_error($con) . "</p>";
    echo "<p><strong>Query:</strong> " . htmlspecialchars($sql) . "</p>";
    
    // Show table structure
    echo "<p><strong>Checking cart table structure:</strong></p>";
    $structure = mysqli_query($con, "DESCRIBE cart");
    if ($structure) {
        echo "<ul>";
        while($col = mysqli_fetch_assoc($structure)) {
            echo "<li>" . $col['Field'] . " - " . $col['Type'] . "</li>";
        }
        echo "</ul>";
    }
    
    echo "</div>";
    die();
}

// Check if we got results
$result = mysqli_fetch_array($query);
$amount = $result['total'] ?? 0;

// If amount is 0 or cart is empty, redirect to cart
if($amount <= 0) {
    header('location:my-cart.php?error=empty_cart');
    exit();
}

$tax_amount = 0;
$total_amount = $amount + $tax_amount;

// ✅ eSewa SANDBOX/TEST Credentials (for testing)
$esewa_environment = 'sandbox'; // Change to 'live' for production

if($esewa_environment == 'sandbox') {
    $esewa_url = "https://rc-epay.esewa.com.np/api/epay/main/v2/form";
    $product_code = "EPAYTEST";
    $secret = "8gBm/:&EnhH.1/q";
} else {
    // Live/production credentials (you'll get these from eSewa)
    $esewa_url = "https://epay.esewa.com.np/api/epay/main/v2/form";
    $product_code = "YOUR_LIVE_PRODUCT_CODE"; // Get from eSewa
    $secret = "YOUR_LIVE_SECRET_KEY"; // Get from eSewa
}

// eSewa payment parameters
$transaction_uuid = $orderno . "_" . time();
$success_url = "http://localhost/Bookstore/esewa-success.php?order=$orderno";
$failure_url = "http://localhost/Bookstore/esewa-failure.php?order=$orderno";

// Create signature for eSewa
$message = "total_amount=$total_amount,transaction_uuid=$transaction_uuid,product_code=$product_code";
$signature = base64_encode(hash_hmac('sha256', $message, $secret, true));

// Store order details in session for later use
$_SESSION['esewa_order'] = [
    'order_no' => $orderno,
    'amount' => $amount,
    'total_amount' => $total_amount,
    'transaction_uuid' => $transaction_uuid,
    'product_code' => $product_code,
    'signature' => $signature
];

// Get cart count for navbar
$cart_query = mysqli_query($con, "SELECT COUNT(*) as count FROM cart WHERE userID='$user_id'");
if ($cart_query) {
    $cart_result = mysqli_fetch_array($cart_query);
    $cart_count = $cart_result['count'] ?? 0;
} else {
    $cart_count = 0;
}

// Get wishlist count for navbar
$wishlist_query = mysqli_query($con, "SELECT COUNT(*) as count FROM wishlist WHERE userId='$user_id'");
if ($wishlist_query) {
    $wishlist_result = mysqli_fetch_array($wishlist_query);
    $wishlist_count = $wishlist_result['count'] ?? 0;
} else {
    $wishlist_count = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment | Muna Madan Book Store</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="css/main.css">
    
    <style>
        :root {
            --primary: #30D5C8;
            --success: #27ae60;
        }
        
        body {
            background: #f5f5f5;
            font-family: 'Poppins', sans-serif;
            padding-top: 80px;
        }
        
        .payment-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .esewa-logo {
            height: 50px;
            margin-right: 15px;
        }
        
        .payment-method {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .payment-method:hover, .payment-method.active {
            border-color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(48, 213, 200, 0.2);
        }
        
        .pay-button {
            background: var(--primary);
            border: none;
            color: white;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 5px;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .pay-button:hover {
            background: #28c4b7;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(48, 213, 200, 0.3);
        }
        
        .security-badge {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #e9ecef;
            margin-top: 20px;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }
        
        .card {
            border-radius: 15px;
            border: none;
        }
        
        .card-header {
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        
        .order-summary {
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <!-- Simple Header for payment page -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-book text-primary"></i> Muna Madan Book Store
            </a>
            <div class="d-flex align-items-center">
                <a href="my-cart.php" class="btn btn-outline-primary me-2">
                    <i class="fas fa-shopping-cart"></i> Cart (<?php echo $cart_count; ?>)
                </a>
                <a href="my-wishlist.php" class="btn btn-outline-primary me-2">
                    <i class="fas fa-heart"></i> Wishlist (<?php echo $wishlist_count; ?>)
                </a>
                <a href="logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="payment-container">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow">
                    <div class="card-header bg-white">
                        <h3 class="mb-0"><i class="fas fa-lock me-2"></i> Secure Payment</h3>
                    </div>
                    <div class="card-body">
                        
                        <!-- Order Summary -->
                        <div class="order-summary mb-4 p-3 border rounded">
                            <h5 class="mb-3"><i class="fas fa-receipt me-2"></i> Order Summary</h5>
                            <?php
                            // Display cart items
                            $cart_items_query = "SELECT cart.*, books.bookName, books.bookPrice 
                                                 FROM cart 
                                                 JOIN books ON cart.productId = books.id 
                                                 WHERE cart.userID = '$user_id'";
                            $cart_items = mysqli_query($con, $cart_items_query);
                            
                            if ($cart_items === false) {
                                echo '<div class="alert alert-warning">Error loading cart items: ' . mysqli_error($con) . '</div>';
                            } else {
                                if (mysqli_num_rows($cart_items) > 0) {
                                    while($item = mysqli_fetch_array($cart_items)) {
                                        $item_quantity = $item['productQty'] ?? 1;
                                        $item_total = $item['bookPrice'] * $item_quantity;
                                        echo '<div class="d-flex justify-content-between mb-2">
                                                <span>'.$item['bookName'].' x '.$item_quantity.'</span>
                                                <span>NPR '.number_format($item_total, 2).'</span>
                                              </div>';
                                    }
                                } else {
                                    echo '<div class="alert alert-warning">Your cart is empty.</div>';
                                }
                            }
                            ?>
                            
                            <hr>
                            <div class="d-flex justify-content-between">
                                <strong>Subtotal:</strong>
                                <strong>NPR <?php echo number_format($amount, 2); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <strong>Tax:</strong>
                                <strong>NPR <?php echo number_format($tax_amount, 2); ?></strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <strong>Total Amount:</strong>
                                <strong class="text-success">NPR <?php echo number_format($total_amount, 2); ?></strong>
                            </div>
                            <div class="mt-2 text-muted small">
                                <i class="fas fa-info-circle me-1"></i> Order ID: <?php echo $orderno; ?>
                            </div>
                        </div>
                        
                        <!-- Payment Methods -->
                        <h5 class="mb-3"><i class="fas fa-credit-card me-2"></i> Select Payment Method</h5>
                        
                        <div class="payment-method active" id="esewaMethod" onclick="selectPayment('esewa')">
                            <div class="d-flex align-items-center">
                                <img src="https://esewa.com.np/common/images/esewa_logo.png" alt="eSewa" class="esewa-logo">
                                <div>
                                    <h6 class="mb-1">eSewa Wallet</h6>
                                    <p class="mb-0 text-muted small">Pay instantly with your eSewa account</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="payment-method" id="codMethod" onclick="selectPayment('cod')">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                    <i class="fas fa-money-bill-wave fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Cash on Delivery</h6>
                                    <p class="mb-0 text-muted small">Pay when you receive your order</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- eSewa Payment Form (Hidden - Auto-submits via JavaScript) -->
                        <form id="esewaForm" action="<?php echo $esewa_url; ?>" method="POST" style="display: none;">
                            <input type="hidden" name="amount" value="<?php echo $amount; ?>">
                            <input type="hidden" name="tax_amount" value="<?php echo $tax_amount; ?>">
                            <input type="hidden" name="total_amount" value="<?php echo $total_amount; ?>">
                            <input type="hidden" name="transaction_uuid" value="<?php echo $transaction_uuid; ?>">
                            <input type="hidden" name="product_code" value="<?php echo $product_code; ?>">
                            <input type="hidden" name="product_service_charge" value="0">
                            <input type="hidden" name="product_delivery_charge" value="0">
                            <input type="hidden" name="success_url" value="<?php echo $success_url; ?>">
                            <input type="hidden" name="failure_url" value="<?php echo $failure_url; ?>">
                            <input type="hidden" name="signed_field_names" value="total_amount,transaction_uuid,product_code">
                            <input type="hidden" name="signature" value="<?php echo $signature; ?>">
                        </form>
                        
                        <!-- Payment Action -->
                        <div class="mt-4">
                            <div id="esewaPayment">
                                <button type="button" class="pay-button" onclick="processEsewaPayment()">
                                    <i class="fas fa-wallet me-2"></i>
                                    Pay NPR <?php echo number_format($total_amount, 2); ?> with eSewa
                                </button>
                            </div>
                            
                            <div id="codPayment" class="d-none">
                                <button type="button" class="pay-button" style="background: #ffc107; color: #000;" onclick="confirmCOD()">
                                    <i class="fas fa-check-circle me-2"></i>
                                    Confirm COD Order
                                </button>
                            </div>
                        </div>
                        
                        <!-- Security Info -->
                        <div class="security-badge">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-shield-alt text-success fa-lg me-3"></i>
                                <div>
                                    <h6 class="mb-1">Secure Payment</h6>
                                    <p class="mb-0 small">Your payment is secured with eSewa's 256-bit SSL encryption</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Test Credentials Info (for sandbox only) -->
                        <?php if($esewa_environment == 'sandbox'): ?>
                        <div class="alert alert-info mt-3">
                            <h6><i class="fas fa-info-circle me-2"></i> Test Mode Active</h6>
                            <p class="mb-2 small">You're using eSewa Sandbox/Test environment. Use these test credentials:</p>
                            <ul class="mb-0 small">
                                <li><strong>Mobile/ID:</strong> 9800000000, 9800000001, 9800000002</li>
                                <li><strong>MPIN:</strong> 1234</li>
                                <li><strong>OTP:</strong> 123456</li>
                            </ul>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mt-3 text-center">
                            <a href="my-cart.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i> Back to Cart
                            </a>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let selectedPayment = 'esewa';
        
        function selectPayment(method) {
            selectedPayment = method;
            
            // Update active payment method
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('active');
            });
            document.getElementById(method + 'Method').classList.add('active');
            
            // Show corresponding payment button
            document.querySelectorAll('#esewaPayment, #codPayment').forEach(el => {
                el.classList.add('d-none');
            });
            document.getElementById(method + 'Payment').classList.remove('d-none');
        }
        
        function processEsewaPayment() {
            const button = document.querySelector('#esewaPayment .pay-button');
            const originalText = button.innerHTML;
            
            // Show processing state
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Redirecting to eSewa...';
            button.disabled = true;
            
            // Store order in database before redirecting
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'create-order.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (this.status === 200) {
                    try {
                        const response = JSON.parse(this.responseText);
                        if (response.success) {
                            // Submit eSewa form after order is created
                            setTimeout(() => {
                                document.getElementById('esewaForm').submit();
                            }, 500);
                        } else {
                            alert('Error: ' + response.message);
                            button.innerHTML = originalText;
                            button.disabled = false;
                        }
                    } catch (e) {
                        console.error('JSON Parse Error:', e);
                        // Still try to submit to eSewa
                        setTimeout(() => {
                            document.getElementById('esewaForm').submit();
                        }, 500);
                    }
                } else {
                    // Still try to submit to eSewa
                    setTimeout(() => {
                        document.getElementById('esewaForm').submit();
                    }, 500);
                }
            };
            
            // Send order data
            xhr.send('order=<?php echo $orderno; ?>&amount=<?php echo $total_amount; ?>&payment_method=esewa&status=pending');
        }
        
        function confirmCOD() {
            if (confirm('Confirm Cash on Delivery order?\n\nOrder Total: NPR <?php echo number_format($total_amount, 2); ?>')) {
                const button = document.querySelector('#codPayment .pay-button');
                const originalText = button.innerHTML;
                
                button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Processing...';
                button.disabled = true;
                
                // Create COD order
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'create-order.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (this.status === 200) {
                        try {
                            const response = JSON.parse(this.responseText);
                            if (response.success) {
                                window.location.href = 'order-success.php?order=' + response.order_no + '&type=cod';
                            } else {
                                alert('Error: ' + response.message);
                                button.innerHTML = originalText;
                                button.disabled = false;
                            }
                        } catch (e) {
                            alert('Error processing response. Please try again.');
                            button.innerHTML = originalText;
                            button.disabled = false;
                        }
                    } else {
                        alert('Server error. Please try again.');
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }
                };
                
                xhr.onerror = function() {
                    alert('Network error. Please check your connection.');
                    button.innerHTML = originalText;
                    button.disabled = false;
                };
                
                xhr.send('order=<?php echo $orderno; ?>&amount=<?php echo $total_amount; ?>&payment_method=cod&status=confirmed');
            }
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            selectPayment('esewa');
        });
    </script>
</body>
</html>