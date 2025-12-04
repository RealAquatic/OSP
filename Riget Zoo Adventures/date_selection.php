<?php
session_start();
require_once "PHP/db.php";

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.html");
    exit();
}

// Booking type
$type = $_GET['type'] ?? 'ticket';

// Stage: checkin or checkout (only relevant for accommodation)
$stage = $_GET['stage'] ?? 'checkin';
$checkin = $_GET['checkin'] ?? null;

// Availability rules
$max_tickets = 10;
$max_rooms   = 3;

// Fetch bookings
if ($type === "ticket") {
    $stmt = $pdo->prepare("SELECT visit_date, SUM(quantity) AS total FROM tickets GROUP BY visit_date");
} else {
    $stmt = $pdo->prepare("SELECT check_in, COUNT(*) AS total FROM bookings GROUP BY check_in");
}
$stmt->execute();
$dates = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convert to lookup
$booked = [];
foreach ($dates as $d) {
    if ($type === "ticket") {
        $booked[$d['visit_date']] = (int)$d['total'];
    } else {
        $booked[$d['check_in']] = (int)$d['total'];
    }
}

// Current year/month
$month = date("n");
$year  = date("Y");

// Calendar calculations
$first_day_of_month = date("w", strtotime("$year-$month-01"));
$days_in_month      = date("t", strtotime("$year-$month-01"));

// Previous month
$prev_month = $month - 1;
$prev_year  = $year;
if ($prev_month == 0) {
    $prev_month = 12;
    $prev_year--;
}
$days_in_prev_month = date("t", strtotime("$prev_year-$prev_month-01"));

$total_boxes = 42;

function getStatus($date, $type, $booked, $max_tickets, $max_rooms) {
    if (strtotime($date) < strtotime(date("Y-m-d"))) return "unavailable";

    if (!isset($booked[$date])) return "available";

    $count = $booked[$date];

    if ($type === "ticket") {
        return $count >= $max_tickets ? "full" : "available";
    } else {
        return $count >= $max_rooms ? "full" : "available";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RZA | Select a Date</title>
    <link rel="stylesheet" href="CSS/date_selection.css">
</head>
<body>

<!-- Header -->
<div class="Display">
    <div class="Header">
        <div class="TopBar">
            <div class="Logo">RZA</div>
            <div class="SiteTitle"> <a href="index.html">Riget Zoo Adventures</a></div>
        </div>

        <div class="BottomBar">
            <a href="booking.php">Book Your Tickets</a>
            <a href="educational.html">Educational Visits</a>
            <a href="information.html">Information</a>
            <a href="account.php">Account</a>
        </div>
    </div>
</div>

<!-- Content -->
<div class="Content">
    <p>Experience Riget Zoo Adventures</p>
    <h>Select a Date</h>

    <!-- Instruction -->
    <div class="Instruction">
        <?php
        if ($type === "accommodation" && $stage === "checkout") {
            echo "<p>Select your check-out date (max 7 nights after check-in)</p>";
        } elseif ($type === "accommodation") {
            echo "<p>Select your check-in date</p>";
        } else {
            echo "<p>Select your visit date</p>";
        }
        ?>
    </div>

    <!-- Legend -->
    <div class="Legend">
        <div class="legend-block">
            <div class="legend-colour legend-unavailable"></div>
            <div class="legend-label">Unavailable</div>
        </div>
        <div class="legend-block">
            <div class="legend-colour legend-available"></div>
            <div class="legend-label">Available</div>
        </div>
        <div class="legend-block">
            <div class="legend-colour legend-full"></div>
            <div class="legend-label">Fully Booked</div>
        </div>
    </div>

    <!-- Calendar Box -->
    <div class="CalendarBox">
        <div class="calendar-title"><?php echo date("F Y"); ?></div>

        <div class="calendar-grid">

            <!-- Day Names -->
            <div class="calendar-day-name">Sun</div>
            <div class="calendar-day-name">Mon</div>
            <div class="calendar-day-name">Tue</div>
            <div class="calendar-day-name">Wed</div>
            <div class="calendar-day-name">Thu</div>
            <div class="calendar-day-name">Fri</div>
            <div class="calendar-day-name">Sat</div>

            <?php
            // 1) Previous month days
            for ($i = $first_day_of_month - 1; $i >= 0; $i--) {
                $num = $days_in_prev_month - $i;
                echo "<div class='calendar-day unavailable'>$num</div>";
            }

            // 2) Current month days
            for ($d = 1; $d <= $days_in_month; $d++) {
                $date = "$year-$month-" . str_pad($d, 2, '0', STR_PAD_LEFT);
                $status = getStatus($date, $type, $booked, $max_tickets, $max_rooms);

                if ($status === "available") {
                    if ($type === "accommodation") {
                        if ($stage === "checkin") {
                            // First step: choose check-in
                            echo "<a class='calendar-day available' href='date_selection.php?type=accommodation&stage=checkout&checkin=$date'>$d</a>";
                        } else {
                            // Second step: choose check-out
                            $max_checkout = date("Y-m-d", strtotime("$checkin +7 days"));
                            if (strtotime($date) > strtotime($checkin) && strtotime($date) <= strtotime($max_checkout)) {
                                echo "<a class='calendar-day available' href='select_accomodation.php?checkin=$checkin&checkout=$date'>$d</a>";
                            } else {
                                echo "<div class='calendar-day unavailable'>$d</div>";
                            }
                        }
                    } else {
                        // Ticket flow
                        echo "<a class='calendar-day available' href='select_ticket.php?date=$date&type=$type'>$d</a>";
                    }
                } else {
                    echo "<div class='calendar-day $status'>$d</div>";
                }
            }

            // 3) Next month days until total = 42
            $box_count = $first_day_of_month + $days_in_month;
            $remaining = 42 - $box_count;

            for ($i = 1; $i <= $remaining; $i++) {
                echo "<div class='calendar-day unavailable'>$i</div>";
            }
            ?>

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