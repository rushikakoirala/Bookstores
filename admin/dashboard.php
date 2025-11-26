<?php 
session_start();
include_once('includes/config.php');

if (strlen($_SESSION["aid"]) == 0) {   
    header('location:logout.php');
    exit();
} else { 

// Order counts
$ret = mysqli_query($con, "
    SELECT 
        COUNT(id) AS totalorders,
        COUNT(IF(orderStatus IS NULL OR orderStatus = '', 1, NULL)) AS neworders,
        COUNT(IF(orderStatus = 'Dispatched', 1, NULL)) AS dispatchedorders,
        COUNT(IF(orderStatus = 'Delivered', 1, NULL)) AS deliveredorders
    FROM orders
");
$results = mysqli_fetch_array($ret);
$torders = $results['totalorders'];
$norders = $results['neworders'];
$dtorders = $results['dispatchedorders'];
$deliveredorders = $results['deliveredorders'];

// Registered users
$ret1 = mysqli_query($con, "SELECT COUNT(id) AS totalusers FROM users");
$results1 = mysqli_fetch_array($ret1);
$tregusers = $results1['totalusers'];

// Listed books
$ret2 = mysqli_query($con, "SELECT COUNT(id) AS totalbooks FROM tblbooks");
$results2 = mysqli_fetch_array($ret2);
$listedbooks = $results2['totalbooks'];

// Listed categories
$ret3 = mysqli_query($con, "SELECT COUNT(id) AS totalcats FROM category");
$results3 = mysqli_fetch_array($ret3);
$listedcats = $results3['totalcats'];

// Listed sub-categories
$ret4 = mysqli_query($con, "SELECT COUNT(id) AS totalsubcats FROM subcategory");
$results4 = mysqli_fetch_array($ret4);
$listedsubcats = $results4['totalsubcats'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>OBSMS | Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
    <?php include_once('includes/header.php'); ?>
    <div id="layoutSidenav">
        <?php include_once('includes/sidebar.php'); ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Dashboard</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>

                    <div class="row">
                        <?php 
                        $dashboardItems = [
                            ["Total Orders", $torders, "bg-primary", "all-orders.php"],
                            ["New Orders", $norders, "bg-danger", "new-order.php"],
                            ["Delivered Orders", $deliveredorders, "bg-success", "delivered-orders.php"],
                            ["Registered Users", $tregusers, "bg-dark", "registered-users.php"],
                            ["Listed Books", $listedbooks, "bg-secondary", "manage-books.php"],
                            ["Listed Categories", $listedcats, "bg-info", "manage-categories.php"],
                            ["Listed Sub-Categories", $listedsubcats, "bg-primary", "manage-subcategories.php"]
                        ];

                        foreach ($dashboardItems as [$title, $count, $color, $link]) {
                            echo <<<HTML
                            <div class="col-lg-6 col-xl-3 mb-4">
                                <div class="card $color text-white h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="me-3">
                                                <div class="text-white-75 small">$title</div>
                                                <div class="text-lg fw-bold">$count</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between small">
                                        <a class="text-white stretched-link" href="$link">View Details</a>
                                    </div>
                                </div>
                            </div>
                            HTML;
                        }
                        ?>
                    </div>
                </div>
            </main>
            <?php include_once('includes/footer.php'); ?>
        </div>
    </div>

    <!-- JS Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="assets/demo/chart-area-demo.js"></script>
    <script src="assets/demo/chart-bar-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
    <script src="js/datatables-simple-demo.js"></script>
</body>
</html>
<?php } ?>