<?php 
session_start();
include_once('includes/config.php');
error_reporting(0);

if(isset($_POST['submit']))
{
    $name = trim($_POST['fullname']);
    $email = trim($_POST['emailid']);
    $contactno = trim($_POST['contactnumber']);
    $password = md5($_POST['inputuserpwd']);

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format. Please enter a valid email.');</script>";
    } elseif (strpos($email, '.') === false) {
        echo "<script>alert('Email must contain a dot (.)');</script>";
    } else {
        // Check if email already exists
        $sql = mysqli_query($con, "SELECT id FROM users WHERE email='$email'");
        $count = mysqli_num_rows($sql);

        if($count == 0){
            $query = mysqli_query($con, "INSERT INTO users(name, email, contactno, password) VALUES('$name', '$email', '$contactno', '$password')");
            if($query)
            {
                echo "<script>alert('You are successfully registered');</script>";
                echo "<script type='text/javascript'> document.location ='login.php'; </script>";
                exit;
            }
            else{
                echo "<script>alert('Registration failed, something went wrong');</script>";
                echo "<script type='text/javascript'> document.location ='signup.php'; </script>";
                exit;
            } 
        } else {
            echo "<script>alert('Email id already registered with another account. Please try with another email id.');</script>";
            echo "<script type='text/javascript'> document.location ='signup.php'; </script>";   
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Online Book Store || Signup</title>

<!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="vendor/bootstrap/css/bootstrap.min.css">
<!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
<!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="fonts/iconic/css/material-design-iconic-font.min.css">
<!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="fonts/linearicons-v1.0.0/icon-font.min.css">
<!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="vendor/animate/animate.css">
<!--===============================================================================================-->  
    <link rel="stylesheet" type="text/css" href="vendor/css-hamburgers/hamburgers.min.css">
<!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="vendor/animsition/css/animsition.min.css">
<!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="vendor/select2/select2.min.css">
<!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="vendor/perfect-scrollbar/perfect-scrollbar.css">
<!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="css/util.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">
<!--===============================================================================================-->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function emailAvailability() {
    $("#loaderIcon").show();
    jQuery.ajax({
        url: "check_availability.php",
        data:'email='+$("#emailid").val(),
        type: "POST",
        success:function(data){
            $("#user-email-status").html(data);
            $("#loaderIcon").hide();
        },
        error:function (){}
    });
}
</script>
</head>
<body class="animsition">

    <!-- Title page -->
    <section class="bg-img1 txt-center p-lr-15 p-tb-92" style="background-image: url('images/bg-01.jpg');">
        <h2 class="ltext-105 cl0 txt-center">
            Signup
        </h2>
    </section>  

    <!-- Content page -->
    <section class="bg0 p-t-104 p-b-116">
        <div class="container">
            <div class="flex-w flex-tr">
                <div class="size-210 bor10 p-lr-70 p-t-55 p-b-70 p-lr-15-lg w-full-md">
                    <form method="post" name="signup" onsubmit="return validateEmail();">
                        <h4 class="mtext-105 cl2 txt-center p-b-30">
                            Registrations
                        </h4>

                        <div class="bor8 m-b-20 how-pos4-parent">
                            <label>Full Name</label>
                            <input type="text" name="fullname" class="form-control" required >
                        </div>
                        <div class="bor8 m-b-20 how-pos4-parent">
                            <label>Email Id</label>
                            <input type="email" name="emailid" id="emailid" class="form-control" onBlur="emailAvailability()" required>
                            <small id="user-email-status" class="text-danger"></small>
                            <div id="loaderIcon" style="display:none;"><img src="images/loader.gif" alt="loading..."></div>
                        </div>
                        <div class="bor8 m-b-20 how-pos4-parent">
                            <label>Contact Number</label>
                            <input type="text" name="contactnumber" pattern="[0-9]{10}" title="10 numeric characters only" class="form-control" required>
                        </div>
                        <div class="bor8 m-b-20 how-pos4-parent">
                            <label>Password</label>
                            <input type="password" name="inputuserpwd" class="form-control" required>
                        </div>

                        <button class="flex-c-m stext-101 cl0 size-121 bg3 bor1 hov-btn3 p-lr-15 trans-04 pointer" 
                            type="submit" name="submit" id="submit">
                            Submit
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>  

<!--===============================================================================================-->  
<script src="vendor/jquery/jquery-3.2.1.min.js"></script>
<script src="vendor/animsition/js/animsition.min.js"></script>
<script src="vendor/bootstrap/js/popper.js"></script>
<script src="vendor/bootstrap/js/bootstrap.min.js"></script>
<script src="vendor/select2/select2.min.js"></script>
<script>
    $(".js-select2").each(function(){
        $(this).select2({
            minimumResultsForSearch: 20,
            dropdownParent: $(this).next('.dropDownSelect2')
        });
    })
</script>
<script src="vendor/MagnificPopup/jquery.magnific-popup.min.js"></script>
<script src="vendor/perfect-scrollbar/perfect-scrollbar.min.js"></script>
<script>
    $('.js-pscroll').each(function(){
        $(this).css('position','relative');
        $(this).css('overflow','hidden');
        var ps = new PerfectScrollbar(this, {
            wheelSpeed: 1,
            scrollingThreshold: 1000,
            wheelPropagation: false,
        });

        $(window).on('resize', function(){
            ps.update();
        })
    });
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAKFWBqlKAGCeS1rMVoaNlwyayu0e0YRes"></script>
<script src="js/map-custom.js"></script>
<script src="js/main.js"></script>

<script>
function validateEmail() {
    var email = document.getElementById("emailid").value;
    if (!email.includes('.')) {
        alert("Email must contain a dot (.)");
        return false; // Prevent form submission
    }
    return true;
}
</script>

</body>
</html> 