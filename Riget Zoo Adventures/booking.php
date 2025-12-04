<?php
session_start();

// If not logged in → redirect to login page
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RZA | Book Your Tickets</title>
    <link rel="stylesheet" href="CSS/booking.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Italiana&family=Just+Me+Again+Down+Here&display=swap" rel="stylesheet">
</head>
<body>

<!-- Display -->
<div class="Display">
    <div class="Header">
        <div class="TopBar">
            <div class="Logo">RZA</div>
            <div class="SiteTitle"> <a href="index.html">Riget Zoo Adventures</a></div>
        </div>

        <div class="BottomBar">
            <a href="booking.php" style="color: rgb(202, 182, 154);">Book Your Tickets</a>
            <a href="educational.html">Educational Visits</a>
            <a href="information.html">Information</a>
            <a href="account.php">Account</a>
        </div>
    </div>
</div>

<!-- Content -->
<div class="Content">
    <p>Riget Zoo Adventures</p>
    <h>Book Your Tickets</h>

    <p>Select your booking type below to continue to date selection.</p>

    <div class="BookingBoxArea">
        
        <!-- Ticket Box -->
        <div class="BookingBox">
            <div class="BookingImage ticket-img"></div>

            <div class="BookingBottomRow">
                <span class="BookingLabel">Book a Ticket</span>
                <a href="date_selection.php?type=ticket" class="GoButton">Go</a>
            </div>
        </div>

        <!-- Accommodation Box -->
        <div class="BookingBox">
            <div class="BookingImage accommodation-img"></div>

            <div class="BookingBottomRow">
                <span class="BookingLabel">Book an Accommodation</span>
                <a href="date_selection.php?type=accommodation" class="GoButton">Go</a>
            </div>
        </div>

    </div>
</div>

<!-- Footer -->
<div class="Footer">
    <img src="Images/Footer.png" class="FooterImage">
    <div class="FooterContent">
        <div class="FooterLogo">RZA</div>
        <div class="FooterInfo">
            <p>Call us: 01790-00000</p>
            <p>Email: <a href="mailto:rzasupport@gmail.com">rzasupport@gmail.com</a></p>
            <p><a href="#">Privacy Policy</a></p>
        </div>
    </div>
</div>

</body>
</html>
