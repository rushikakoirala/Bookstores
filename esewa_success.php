<?php
session_start();
include('connection/connect.php');

// After successful eSewa payment
if(isset($_SESSION['esewa_cart'])){
    $status = "Ordered";
    $payment_status = "Paid"; 
    $cart = $_SESSION['esewa_cart'];
    $order_date = $_SESSION['esewa_order_date'];
    $delivery_date = $_SESSION['esewa_delivery_date'];
    $customer = $_SESSION['esewa_customer'];

    foreach($cart as $item){
        $food = $item['title'];
        $price = $item['price'];
        $qty = $item['qty'];
        $subtotal = $price * $qty;

        mysqli_query($con, "INSERT INTO order_tbl
            (food, price, qty, total, order_date, delivery_date, status, c_name, c_phone, c_address, payment_method,payment_status)
            VALUES
            ('$food','$price','$qty','$subtotal','$order_date','$delivery_date','$status','{$customer['name']}','{$customer['phone']}','{$customer['address']}','ESEWA','$payment_status')")
            or die(mysqli_error($con));
    }

    unset($_SESSION['esewa_cart']);
    unset($_SESSION['esewa_total']);
    unset($_SESSION['esewa_order_date']);
    unset($_SESSION['esewa_delivery_date']);
    unset($_SESSION['esewa_customer']);

    echo "<script>alert('Payment successful! Order placed.'); window.location='index.php';</script>";
    exit();
}
?>