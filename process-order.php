<?php
session_start();
include('includes/config.php');

// 1. Require login
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

// 2. Validate required POST fields
if (empty($_POST['txnType'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Error</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            .alert {
                border-radius: .75rem;
                border: 1px solid #f5c2c7;
            }
            .btn-danger {
                border-radius: .5rem;
                padding: 0.5rem 1.5rem;
            }
        </style>
    </head>
    <body>
        <div class="container mt-5">
            <div class="alert alert-danger text-center">
                <h4 class="alert-heading">Transaction type not selected.</h4>
                <p>Please go back and choose a payment method.</p>
                <a href="checkout.php" class="btn btn-danger mt-3">Return to Checkout</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// 3. Gather data
$userId      = $_SESSION['id'];
$orderNumber = random_int(100000000, 999999999);
$txnType     = mysqli_real_escape_string($con, $_POST['txnType']);
$orderDate   = date('Y-m-d H:i:s');
$orderStatus = 'Pending';
$totalAmount = $_SESSION['total'] ?? 0;

// If cart is empty, redirect
if (empty($_SESSION['cart']) || $totalAmount == 0) {
    header('Location: cart.php');
    exit;
}

// 4. Insert order
$sqlOrder = "
    INSERT INTO orders
        (userId, orderNumber, txnType, totalAmount, orderStatus, orderDate)
    VALUES
        ('$userId', '$orderNumber', '$txnType', '$totalAmount', '$orderStatus', '$orderDate')
";
if (!mysqli_query($con, $sqlOrder)) {
    die('Order insert failed: ' . mysqli_error($con));
}
$orderId = mysqli_insert_id($con);

// 5. Insert order items
$stmt = mysqli_prepare(
    $con,
    "INSERT INTO order_items (orderId, productId, quantity, price)
     VALUES (?, ?, ?, ?)"
);
foreach ($_SESSION['cart'] as $item) {
    mysqli_stmt_bind_param(
        $stmt,
        'iiid',
        $orderId,
        $item['product_id'],
        $item['qty'],
        $item['price']
    );
    if (!mysqli_stmt_execute($stmt)) {
        die('Order item insert failed: ' . mysqli_error($con));
    }
}
mysqli_stmt_close($stmt);

// 6. Clear the cart
unset($_SESSION['cart'], $_SESSION['total']);

// 7. Thank-you page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta http-equiv="refresh" content="3;url=my-orders.php">
    <style>
        .alert {
            border-radius: .75rem;
            border: 1px solid #badbcc;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="alert alert-success text-center">
            <h4 class="alert-heading">Thank you for your order!</h4>
            <p>Your order number is <strong>#<?= $orderNumber ?></strong>.</p>
            <hr>
            <p>We’re redirecting you to <strong>My Orders</strong>…</p>
        </div>
    </div>
</body>
</html>
