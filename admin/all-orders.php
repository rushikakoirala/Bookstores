<?php
session_start();
include_once('includes/config.php');

// Redirect if not logged in
if (!isset($_SESSION["aid"]) || strlen($_SESSION["aid"]) == 0) {
    header('location:logout.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Orders - Online Bookstore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet"> <!-- Optional custom CSS -->
</head>
<body class="sb-nav-fixed">

<?php include_once('includes/header.php'); ?>

<div id="layoutSidenav">
    <?php include_once('includes/sidebar.php'); ?>

    <div id="layoutSidenav_content">
        <main class="container-fluid px-4">
            <h2 class="mt-4 mb-4">All Orders</h2>

            <div class="card mb-4">
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Order No.</th>
                                <th>Order By</th>
                                <th>Total Amount</th>
                                <th>Order Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = mysqli_query($con, "
                                SELECT MIN(orders.id) as id, orderNumber, totalAmount, orderStatus, orderDate, users.name 
                                FROM orders 
                                JOIN users ON users.id = orders.userId
                                GROUP BY orderNumber
                            ");
                            $cnt = 1;
                            while ($row = mysqli_fetch_array($query)) {
                            ?>
                            <tr>
                                <td><?= htmlentities($cnt); ?></td>
                                <td><?= htmlentities($row['orderNumber']); ?></td>
                                <td><?= htmlentities($row['name']); ?></td>
                                <td>Rs. <?= htmlentities($row['totalAmount']); ?></td>
                                <td><?= htmlentities($row['orderDate']); ?></td>
                                <td><?= $row['orderStatus'] ?: 'Not Processed Yet'; ?></td>
                                <td>
                                    <a href="order-details.php?orderid=<?= $row['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                </td>
                            </tr>
                            <?php $cnt++; } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <?php include_once('includes/footer.php'); ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/scripts.js"></script> <!-- Optional if needed for sidebar toggle -->
</body>
</html>
