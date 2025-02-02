<?php
session_start();
error_reporting(0);
include('includes/config.php');

if (isset($_POST['submit2'])) {
    $pid = intval($_GET['pkgid']);
    $useremail = $_SESSION['login'];
    $fromdate = $_POST['fromdate'];
    $todate = $_POST['todate'];
    $adult = $_POST['adult'];
    $children = $_POST['children'];
    $comment = $_POST['comment'];
    $status = 0; // Default status is 0 (Pending)
  
    // Fetch the package price from the database
    $sql = "SELECT PackagePrice FROM tbltourpackages WHERE PackageId = :pid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':pid', $pid, PDO::PARAM_INT);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);

    if ($result) {
        $packagePrice = $result->PackagePrice;

        // Assuming children get a 50% discount on the adult price
        $childrenPrice = $packagePrice * 0.5;

        // Calculate the total price based on the number of adults and children
        $totalPrice = ($adult * $packagePrice) + ($children * $childrenPrice);

        // Insert the booking data including the calculated total price
        $sql = "INSERT INTO tblbooking(PackageId, UserEmail, FromDate, ToDate, children, adult, Comment, status, TotalPrice) 
                VALUES(:pid, :useremail, :fromdate, :todate, :children, :adult, :comment, :status, :totalPrice)";
        
        $query = $dbh->prepare($sql);
        $query->bindParam(':pid', $pid, PDO::PARAM_INT);
        $query->bindParam(':useremail', $useremail, PDO::PARAM_STR);
        $query->bindParam(':fromdate', $fromdate, PDO::PARAM_STR);
        $query->bindParam(':todate', $todate, PDO::PARAM_STR);
        $query->bindParam(':children', $children, PDO::PARAM_INT);
        $query->bindParam(':adult', $adult, PDO::PARAM_INT);
        $query->bindParam(':comment', $comment, PDO::PARAM_STR);
        $query->bindParam(':status', $status, PDO::PARAM_INT);
        $query->bindParam(':totalPrice', $totalPrice, PDO::PARAM_STR);

        $query->execute();
        $lastInsertId = $dbh->lastInsertId();

        if ($lastInsertId) {
            $msg = "Booked Successfully";
        } else {
            $error = "Something went wrong. Please try again";
        }
    }
}
?>

<!DOCTYPE HTML>
<html>
<head>
    <title>TMS | Package Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <script type="application/x-javascript"> 
        addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false);
        function hideURLbar(){ window.scrollTo(0,1); } 
    </script>
    <link href="css/bootstrap.css" rel="stylesheet" type="text/css" />
    <link href="css/style.css" rel="stylesheet" type="text/css" />
    <link href="css/font-awesome.css" rel="stylesheet">
    <script src="js/jquery-1.12.0.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <link href="css/animate.css" rel="stylesheet" type="text/css" media="all">
    <script src="js/wow.min.js"></script>
    <link rel="stylesheet" href="css/jquery-ui.css" />
    <script>
        new WOW().init();
    </script>
    <script src="js/jquery-ui.js"></script>
    <script>
        $(function() {
            $("#datepicker, #datepicker1").datepicker();
        });

        // Function to calculate the total price
        function calculateTotal() {
            var adultPrice = parseFloat($("#adultPrice").val());
            var adultCount = parseInt($("#adult").val());
            var childrenPrice = parseFloat($("#childrenPrice").val());
            var childrenCount = parseInt($("#children").val());

            var total = (adultPrice * adultCount) + (childrenPrice * childrenCount);
            $("#totalPrice").text(total.toFixed(2)); // Display the total price
        }

        // Update the total price when adult or children numbers change
        $(document).ready(function() {
            $("#adult, #children").on("input", function() {
                calculateTotal();
            });

            // Initial total calculation on page load
            calculateTotal();
        });
    </script>
    <style>
        .errorWrap {
            padding: 10px;
            margin: 0 0 20px 0;
            background: #fff;
            border-left: 4px solid #dd3d36;
            box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
        }
        .succWrap {
            padding: 10px;
            margin: 0 0 20px 0;
            background: #fff;
            border-left: 4px solid #5cb85c;
            box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
        }
    </style>
</head>
<body>
    <?php include('includes/header.php'); ?>
    <div class="banner-3">
        <div class="container">
            <h1 class="wow zoomIn animated" data-wow-delay=".5s"> TMS - Package Details</h1>
        </div>
    </div>
    <div class="selectroom">
        <div class="container">
            <?php if ($error) { ?>
                <div class="errorWrap"><strong>ERROR</strong>: <?php echo htmlentities($error); ?> </div>
            <?php } else if ($msg) { ?>
                <div class="succWrap"><strong>SUCCESS</strong>: <?php echo htmlentities($msg); ?> </div>
            <?php } ?>

            <?php 
            $pid = intval($_GET['pkgid']);
            $sql = "SELECT * FROM tbltourpackages WHERE PackageId = :pid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':pid', $pid, PDO::PARAM_INT);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_OBJ);
            if ($query->rowCount() > 0) {
                foreach ($results as $result) { 
            ?>
            <form name="book" method="post">
                <div class="selectroom_top">
                    <div class="col-md-4 selectroom_left wow fadeInLeft animated" data-wow-delay=".5s">
                        <img src="admin/pacakgeimages/<?php echo htmlentities($result->PackageImage); ?>" class="img-responsive" alt="">
                    </div>
                    <div class="col-md-8 selectroom_right wow fadeInRight animated" data-wow-delay=".5s">
                        <h2><?php echo htmlentities($result->PackageName); ?></h2>
                        <p><b>Package Type:</b> <?php echo htmlentities($result->PackageType); ?></p>
                        <p><b>Package Location:</b> <?php echo htmlentities($result->PackageLocation); ?></p>
                        <p><b>Features:</b> <?php echo htmlentities($result->PackageFetures); ?></p>
                        <div class="ban-bottom">
                            <div class="bnr-right">
                                <label class="inputLabel">From</label>
                                <input class="date" id="datepicker" type="text" placeholder="dd-mm-yyyy" name="fromdate" required="">
                            </div>
                            <div class="bnr-right">
                                <label class="inputLabel">To</label>
                                <input class="date" id="datepicker1" type="text" placeholder="dd-mm-yyyy" name="todate" required="">
                            </div>
                        </div>
                        <div class="grand">
                            <h2><b>Grand Total:</b> <span id="totalPrice"><?php echo htmlentities($result->PackagePrice); ?></span></h2>
                        </div>
                    </div>
                    <h3>Package Details</h3>
                    <p><?php echo htmlentities($result->PackageDetails); ?> </p>
                    <div class="clearfix"></div>
                </div>

                <div class="selectroom_top">
                    <h2>Travels</h2>
                    <div class="selectroom-info">
                        <ul>
                            <li class="spe">
                                <label class="inputLabel">Adults</label>
                                <input class="special" type="number" name="adult" id="adult" value="1" min="1" oninput="calculateTotal()">
                            </li>
                            <li>
                                <label class="inputLabel">Children</label>
                                <input class="special" type="number" name="children" id="children" value="0" min="0" oninput="calculateTotal()">
                            </li>
                            <li>
                                <label class="inputLabel">Comment</label>
                                <textarea name="comment" rows="5"></textarea>
                            </li>
                            <li>
                                <input type="submit" name="submit2" value="Book Now">
                            </li>
                        </ul>
                    </div>
                </div>
            </form>
            <?php }} ?>
        </div>
    </div>
</body>
</html>
