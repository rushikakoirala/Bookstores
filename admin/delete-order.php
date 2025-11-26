<?php
session_start();
include_once('includes/config.php');

if (strlen($_SESSION['aid']) == 0) {
    header('location:logout.php');
} else {
    if (isset($_GET['id'])) {
        $orderid = intval($_GET['id']);
        $query = mysqli_query($con, "DELETE FROM orders WHERE id='$orderid'");
        if ($query) {
            echo "<script>alert('Order deleted successfully');</script>";
            echo "<script>window.location.href='all-orders.php';</script>";
        } else {
            echo "<script>alert('Something went wrong');</script>";
        }
    }
}
?>
