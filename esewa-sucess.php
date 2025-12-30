<?php
session_start();
include_once('includes/config.php');

// Get data from eSewa response
$data = $_POST['data'] ?? '';
$order_no = $_GET['order'] ?? '';

// For sandbox testing, you might get data in POST
// In production, eSewa sends data as POST parameter 'data'

if(empty($data) && isset($_POST)) {
    // Try to get data from POST parameters
    $data = json_encode($_POST);
}

if(empty($order_no)) {
    // Try to get order from session
    $order_no = $_SESSION['orderno'] ?? '';
}

// Verify eSewa response (simplified for now)
// In production, you should verify the signature

// Update order status to paid
if(!empty($order_no)) {
    $sql = "UPDATE orders SET payment_status = 'paid', order_status = 'processing' 
            WHERE order_no = '$order_no' AND payment_status = 'pending'";
    
    if(mysqli_query($con, $sql)) {
        // Clear cart
        $user_id = $_SESSION['id'];
        mysqli_query($con, "DELETE FROM cart WHERE userID = '$user_id'");
        unset($_SESSION['cart_count']);
        unset($_SESSION['gtotal']);
        unset($_SESSION['orderno']);
        
        // Clear eSewa session
        unset($_SESSION['esewa_order']);
        
        $success = true;
    } else {
        $success = false;
    }
} else {
    $success = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful | Muna Madan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .success-card {
            background: white;
            border-radius: 20px;
            padding: 50px 40px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
        }
        .success-icon {
            width: 100px;
            height: 100px;
            background: #27ae60;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
        }
        .success-icon i {
            font-size: 50px;
            color: white;
        }
    </style>
</head>
<body>
    <div class="success-card">
        <?php if($success): ?>
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        <h1 class="mb-3" style="color: #27ae60;">Payment Successful!</h1>
        <p class="mb-4">Your order <strong>#<?php echo $order_no; ?></strong> has been confirmed.</p>
        <div class="alert alert-success mb-4">
            <i class="fas fa-info-circle me-2"></i>
            Thank you for your payment. Your order is now being processed.
        </div>
        <?php else: ?>
        <div class="success-icon" style="background: #e74c3c;">
            <i class="fas fa-exclamation"></i>
        </div>
        <h1 class="mb-3" style="color: #e74c3c;">Payment Verification Failed</h1>
        <p class="mb-4">We couldn't verify your payment. Please contact support.</p>
        <?php endif; ?>
        
        <div class="d-grid gap-2">
            <a href="index.php" class="btn btn-primary btn-lg">
                <i class="fas fa-home me-2"></i> Return to Home
            </a>
            <a href="my-orders.php" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-history me-2"></i> View Orders
            </a>
        </div>
        
        <div class="mt-4 text-muted small">
            <i class="fas fa-question-circle me-1"></i>
            Need help? <a href="contact.php">Contact Support</a>
        </div>
    </div>
</body>
</html>