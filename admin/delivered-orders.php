<?php 
session_start();
include_once('includes/config.php');

if(strlen($_SESSION["aid"])==0) {   
    header('location:logout.php');
} else {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Online Book Store | Delivered Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet" />
</head>
<body class="sb-nav-fixed">

    <!-- Header -->
    <?php include_once('includes/header.php'); ?>

    <div id="layoutSidenav">
        
        <!-- Sidebar -->
        <?php include_once('includes/sidebar.php'); ?>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Manage Delivered Orders</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Delivered Orders</li>
                    </ol>

                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-table me-1"></i>
                            Delivered Order Details
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Order No.</th>
                                        <th>Order By</th>
                                        <th>Order Amount</th>
                                        <th>Order Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
<?php 
$query = mysqli_query($con, "SELECT MIN(orders.id) AS id, orderNumber, totalAmount, orderStatus, orderDate, users.name 
    FROM orders 
    JOIN users ON users.id = orders.userId 
    WHERE orderStatus = 'Delivered' 
    GROUP BY orderNumber, users.id");

$cnt = 1;
while ($row = mysqli_fetch_array($query)) {
?>  
                                    <tr>
                                        <td><?php echo htmlentities($cnt); ?></td>
                                        <td><?php echo htmlentities($row['orderNumber']); ?></td>
                                        <td><?php echo htmlentities($row['name']); ?></td>
                                        <td><?php echo htmlentities($row['totalAmount']); ?></td>
                                        <td><?php echo htmlentities($row['orderDate']); ?></td>
                                        <td class="text-success"><?php echo htmlentities($row['orderStatus']); ?></td>
                                        <td>
                                            <a href="order-details.php?orderid=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                View
                                            </a>
                                        </td>
                                    </tr>
<?php 
    $cnt++;
} 
?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <?php include_once('includes/footer.php'); ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>
</body>
</html>
<?php } ?>
