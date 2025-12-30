<?php
session_start();
include_once('includes/config.php');

$order_no = $_GET['order'] ?? $_SESSION['orderno'] ?? '';

// Update order status to failed
if(!empty($order_no)) {
    mysqli_query($con, "UPDATE orders SET payment_status = 'failed' WHERE order_no = '$order_no' AND payment_status = 'pending'");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed | Muna Madan</title>
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
        .error-card {
            background: white;
            border-radius: 20px;
            padding: 50px 40px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
        }
        .error-icon {
            width: 100px;
            height: 100px;
            background: #e74c3c;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
        }
        .error-icon i {
            font-size: 50px;
            color: white;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon">
            <i class="fas fa-times"></i>
        </div>
        <h1 class="mb-3" style="color: #e74c3c;">Payment Failed</h1>
        <p class="mb-4">Your payment could not be processed. Please try again.</p>
        
        <?php if(!empty($order_no)): ?>
        <div class="alert alert-warning mb-4">
            <i class="fas fa-info-circle me-2"></i>
            Order #<?php echo $order_no; ?> - Payment Failed
        </div>
        <?php endif; ?>
        
        <div class="d-grid gap-2">
            <a href="payment.php" class="btn btn-primary btn-lg">
                <i class="fas fa-redo me-2"></i> Try Again
            </a>
            <a href="index.php" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-home me-2"></i> Return to Home
            </a>
        </div>
        
        <div class="mt-4 text-muted small">
            <i class="fas fa-question-circle me-1"></i>
            Need help? <a href="contact.php">Contact Support</a>
        </div>
    </div>
</body>
</html>