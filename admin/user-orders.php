<?php 
session_start();
include_once('includes/config.php');
if(strlen($_SESSION["aid"]) == 0) {   
    header('location:logout.php');
} else {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Online Book Store | User Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="js/all.min.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
<?php include_once('includes/header.php'); ?>
<div id="layoutSidenav">
    <?php include_once('includes/sidebar.php'); ?>
    <div id="layoutSidenav_content">
        <main>
            <?php 
                $userid = $_GET['uid'];
                $username = $_GET['uname'];
            ?>
            <div class="container-fluid px-4">
                <h1 class="mt-4">Manage <?php echo htmlentities($username); ?>'s Orders</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Manage <?php echo htmlentities($username); ?>'s Orders</li>
                </ol>
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-table me-1"></i> All Order Details
                    </div>
                    <div class="card-body">
                        <table id="datatablesSimple">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Order No.</th>
                                    <th>Order By</th>
                                    <th>Order Amount</th>
                                    <th>Order Date</th>
                                    <th>Order Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th>Order No.</th>
                                    <th>Order By</th>
                                    <th>Order Amount</th>
                                    <th>Order Date</th>
                                    <th>Order Status</th>
                                    <th>Action</th>
                                </tr>
                            </tfoot>
                            <tbody>
<?php 
$query = mysqli_query($con, "
    SELECT MIN(orders.id) AS id, orderNumber, totalAmount, orderStatus, orderDate, users.name, users.contactno
    FROM orders 
    JOIN users ON users.id = orders.userId 
    WHERE orders.userId = '$userid' 
    GROUP BY orderNumber
");

$cnt = 1;
$count = mysqli_num_rows($query);
if ($count > 0) {
    while ($row = mysqli_fetch_array($query)) {
?>  
    <tr>
        <td><?php echo htmlentities($cnt); ?></td>
        <td><?php echo htmlentities($row['orderNumber']); ?></td>
        <td><?php echo htmlentities($row['name']); ?></td>
        <td>Rs. <?php echo htmlentities($row['totalAmount']); ?></td>
        <td><?php echo htmlentities($row['orderDate']); ?></td>
        <td>
            <?php echo $row['orderStatus'] ?: 'Not Processed Yet'; ?>
        </td>
        <td>
            <a href="order-details.php?orderid=<?php echo $row['id']; ?>" target="_blank">
                <i class="fas fa-file fa-2x" title="View Order Details"></i>
            </a>
        </td>
    </tr>
<?php 
        $cnt++; 
    } 
} else { 
?>
    <tr>
        <th colspan="7" style="color:red;">No Order Found</th>
    </tr>
<?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
        <?php include_once('includes/footer.php'); ?>
    </div>
</div>
<script src="js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="js/scripts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
<script src="js/datatables-simple-demo.js"></script>
</body>
</html>
<?php } ?>
