<?php session_start();
error_reporting(0);
include_once('includes/config.php');
if(strlen($_SESSION["aid"])==0) {
    header('location:logout.php');
} else {

// Code for Take Action
if(isset($_POST['takeaction'])) {
    $oid = $_GET['orderid'];
    $status = $_POST['ostatus'];
    $remark = $_POST['remark'];
    $actionby = $_SESSION['aid'];
    $canceledBy = 'Admin';

    if($status == 'Cancelled') {
        $query = "INSERT INTO ordertrackhistory(orderId, status, remark, actionBy, canceledBy) VALUES ('$oid', '$status', '$remark', '$actionby', '$canceledBy');";
        $query .= "UPDATE orders SET orderStatus='$status' WHERE id='$oid';";
    } else {
        $query = "INSERT INTO ordertrackhistory(orderId, status, remark, actionBy) VALUES ('$oid', '$status', '$remark', '$actionby');";
        $query .= "UPDATE orders SET orderStatus='$status' WHERE id='$oid';";
    }

    $result = mysqli_multi_query($con, $query);
    if ($result) {
        echo '<script>alert("Action has been updated successfully")</script>';
        echo "<script>window.location.href ='all-orders.php'</script>";
    } else {
        echo '<script>alert("Something Went Wrong. Please try again.")</script>';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Online Book Store | Order Details</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="js/all.min.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
<?php include_once('includes/header.php');?>
<div id="layoutSidenav">
<?php include_once('includes/sidebar.php');?>
<div id="layoutSidenav_content">
<main>
<div class="container-fluid px-4">
<?php 
$oid = $_GET['orderid'];
$query = mysqli_query($con, "SELECT DISTINCT orders.id, orders.orderNumber, orders.totalAmount, orders.orderStatus, orders.orderDate,
    orders.txnType, orders.txnNumber,
    users.name, users.email, users.contactno,
    addresses.billingAddress, addresses.biilingCity, addresses.billingState, addresses.billingPincode, addresses.billingCountry,
    addresses.shippingAddress, addresses.shippingCity, addresses.shippingState, addresses.shippingPincode, addresses.shippingCountry
    FROM orders
    JOIN users ON users.id = orders.userId
    JOIN addresses ON addresses.id = orders.addressId
    WHERE orders.id = '$oid' LIMIT 1");
$row = mysqli_fetch_array($query);
?>
<h1 class="mt-4">#<?php echo htmlentities($row['orderNumber']);?> Details</h1>
<div class="card mb-4">
<div class="card-header">
<i class="fas fa-table me-1"></i>
Order Details
</div>
<div class="card-body">
<div class="row">
<div class="col-5">
<table class="table table-bordered">
<tr><th colspan="2" style="text-align:center;">Order Details</th></tr>
<tr><th>Order No.</th><td><?php echo htmlentities($row['orderNumber']);?></td></tr>
<tr><th>Order Amount</th><td><?php echo htmlentities($row['totalAmount']);?></td></tr>
<tr><th>Order Date</th><td><?php echo htmlentities($row['orderDate']);?></td></tr>
<tr><th>Order Status</th><td><?php echo ($row['orderStatus']) ? htmlentities($row['orderStatus']) : 'Not Processed Yet'; ?></td></tr>
<tr><th>Txn Type</th><td><?php echo htmlentities($row['txnType']);?></td></tr>
<tr><th>Txn Number</th><td><?php echo htmlentities($row['txnNumber']);?></td></tr>
</table></div>
<div class="col-7">
<table class="table table-bordered">
<tr><th colspan="2" style="text-align:center;">Customer/User Details</th></tr>
<tr><th>Name</th><td><?php echo htmlentities($row['name']);?></td></tr>
<tr><th>Email</th><td><?php echo htmlentities($row['email']);?></td></tr>
<tr><th>Contact No</th><td><?php echo htmlentities($row['contactno']);?></td></tr>
<tr><th>Billing Address</th><td><?php echo htmlentities($row['billingAddress'] . ", " . $row['biilingCity'] . ", " . $row['billingState'] . ", " . $row['billingCountry'] . " - " . $row['billingPincode']);?></td></tr>
<tr><th>Shipping Address</th><td><?php echo htmlentities($row['shippingAddress'] . ", " . $row['shippingCity'] . ", " . $row['shippingState'] . ", " . $row['shippingCountry'] . " - " . $row['shippingPincode']);?></td></tr>
</table></div>
<div class="col-12">
<table class="table table-bordered">
<tr><th colspan="6" style="text-align:center;">Products / Items Details</th></tr>
<tr><th>Book</th><th>Name</th><th>Price</th><th>Qty</th><th>Total</th><th>Shipping</th></tr>
<?php 
$grandtotalamount = 0; $grandtshipping = 0;
$query = mysqli_query($con, "SELECT tblbooks.id as pid, tblbooks.BookName, tblbooks.BookImage1, tblbooks.BookPriceAfterDiscount, tblbooks.shippingCharge, ordersdetails.quantity FROM ordersdetails 
JOIN orders ON orders.orderNumber = ordersdetails.orderNumber
JOIN tblbooks ON tblbooks.id = ordersdetails.productId
WHERE orders.id = '$oid'");
while($row = mysqli_fetch_array($query)) {
    $totalamount = $row['quantity'] * $row['BookPriceAfterDiscount'];
    $tshipping = $row['shippingCharge'];
    $grandtotalamount += $totalamount;
    $grandtshipping += $tshipping;
?>
<tr>
<td><img src="productimages/<?php echo htmlentities($row['BookImage1']);?>" width="100" height="100"></td>
<td><a href="edit-book.php?id=<?php echo htmlentities($row['pid']);?>" target="_blank"><?php echo htmlentities($row['BookName']);?></a></td>
<td><?php echo htmlentities($row['BookPriceAfterDiscount']);?></td>
<td><?php echo htmlentities($row['quantity']);?></td>
<td><?php echo htmlentities($totalamount);?></td>
<td><?php echo htmlentities($tshipping);?></td>
</tr>
<?php } ?>
<tr><th colspan="4" style="text-align:right;">Sub-Total</th><th><?php echo htmlentities(round($grandtotalamount,2));?></th><th><?php echo htmlentities(round($grandtshipping,2));?></th></tr>
<tr><th colspan="4" style="text-align:right;">Grand-Total</th><th colspan="2" style="text-align:center;"><?php echo htmlentities(round($grandtotalamount+$grandtshipping,2));?></th></tr>
</table></div>
<?php 
$query = mysqli_query($con,"SELECT remark, status, postingDate, tbladmin.username FROM ordertrackhistory
JOIN tbladmin ON tbladmin.id = ordertrackhistory.actionBy
WHERE ordertrackhistory.orderId = '$oid'");
if(mysqli_num_rows($query) > 0) {
?>
<div class="col-12">
<table class="table table-bordered">
<tr><th colspan="4" style="text-align:center;">Order History</th></tr>
<tr><th>Remark</th><th>Status</th><th>By</th><th>Date</th></tr>
<?php while($row = mysqli_fetch_array($query)) { ?>
<tr><td><?php echo htmlentities($row['remark']);?></td><td><?php echo htmlentities($row['status']);?></td><td><?php echo htmlentities($row['username']);?></td><td><?php echo htmlentities($row['postingDate']);?></td></tr>
<?php } ?>
</table></div>
<?php } ?>
<?php if($ostatus == '' || $ostatus == 'Packed' || $ostatus == 'Dispatched'  ) { ?>
<div align="center"><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">Take Action</button></div>
<?php } ?>
</div></div></div>
</main>
<?php include_once('includes/footer.php');?>
</div></div>
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
<div class="modal-dialog" role="document">
<form method="post" name="takeaction">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="exampleModalLabel">Update the Order Status</h5>
<button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
<p><select name="ostatus" class="form-control" required>
<option value="">Select</option>
<option value="Cancelled">Cancel</option>

<option value="Dispatched">Dispatched</option>

<option value="Delivered">Delivered</option>
</select></p>
<p><textarea class="form-control" required name="remark" placeholder="Remark"></textarea></p>
</div>
<div class="modal-footer">
<button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
<button class="btn btn-primary" type="submit" name="takeaction">Save changes</button>
</div>
</div>
</form>
</div>
</div>
<script src="js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="js/scripts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
<script src="js/datatables-simple-demo.js"></script>
</body>
</html>
<?php } ?>
