<?php
// Payment / confirmation page
session_start();
require_once __DIR__ . '/PHP/db.php';

// Prices (fixed)
$PRICES = [
    'adult' => 15.00,
    'child' => 9.00,
    'student' => 6.00,
    'under3' => 0.00,
];

$ROOM_FEES = [
    'single' => 50.00,
    'double' => 80.00,
    'king' => 130.00,
    'luxury' => 200.00,
];

function safe_get($k) { return isset($_REQUEST[$k]) ? trim($_REQUEST[$k]) : ''; }

$type = strtolower(safe_get('type')) ?: 'ticket';

// Helper: redirect with message
function redirect_with_msg($url, $msgKey, $msg) {
    $sep = (strpos($url, '?') === false) ? '?' : '&';
    header('Location: ' . $url . $sep . urlencode($msgKey) . '=' . urlencode($msg));
    exit();
}

// If POST (confirmation), insert into DB
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    try {
        $pdo->beginTransaction();
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        if (!$email) {
            throw new Exception('Please provide a valid email address.');
        }

        $total_price = 0.0;

        if ($type === 'ticket') {
            $date = $_POST['date'] ?? $_SESSION['booking_date'] ?? '';
            if (!$date) throw new Exception('Missing date for ticket booking.');

            $qtys = [
                'adult' => (int)($_POST['qty_adult'] ?? 0),
                'child' => (int)($_POST['qty_child'] ?? 0),
                'student' => (int)($_POST['qty_student'] ?? 0),
                'under3' => (int)($_POST['qty_under3'] ?? 0),
            ];

            // Insert rows into tickets table matching schema: id, user_id, ticket_type, quantity, visit_date, created_at
            $insertTicketStmt = $pdo->prepare('INSERT INTO tickets (user_id, ticket_type, quantity, visit_date, created_at) VALUES (:user_id, :ticket_type, :quantity, :visit_date, :created_at)');
            $user_id = $_SESSION['id'] ?? null;
            if (!$user_id) throw new Exception('User not logged in.');

            foreach ($qtys as $k => $q) {
                if ($q <= 0) continue;
                $insertTicketStmt->execute([
                    ':user_id' => $user_id,
                    ':ticket_type' => $k,
                    ':quantity' => $q,
                    ':visit_date' => $date,
                    ':created_at' => date('Y-m-d H:i:s'),
                ]);
                $total_price += ($PRICES[$k] ?? 0) * $q;
            }

        } else {
            // accommodation
            $checkin = $_POST['checkin'] ?? $_POST['date'] ?? $_SESSION['booking_date'] ?? '';
            $checkout = $_POST['checkout'] ?? '';
            if (!$checkin) throw new Exception('Missing check-in date.');
            $nights = 1;
            if ($checkout) {
                $n1 = strtotime($checkin);
                $n2 = strtotime($checkout);
                $diff = max(1, (int)(($n2 - $n1) / 86400));
                $nights = $diff > 0 ? $diff : 1;
            }

            $room = $_POST['room_type'] ?? '';
            $adults = (int)($_POST['adults_count'] ?? 0);
            $under16 = (int)($_POST['under16_count'] ?? 0);
            if (!$room) throw new Exception('Please select a room type.');

            $roomFee = $ROOM_FEES[$room] ?? 0.0;
            // total: (adults * 50 + under16 * 30 + roomFee) * nights
            $perNightPeople = ($adults * 50.00) + ($under16 * 30.00);
            $accomTotal = ($perNightPeople + $roomFee) * $nights;

            // Insert booking
            // Insert into bookings matching schema: id_, user_id, room_type, check_in, check_out, guests, created_at
            $user_id = $_SESSION['id'] ?? null;
            if (!$user_id) throw new Exception('User not logged in.');
            $guests = $adults + $under16;
            $insertBooking = $pdo->prepare('INSERT INTO bookings (user_id, room_type, check_in, check_out, guests, created_at) VALUES (:user_id, :room_type, :check_in, :check_out, :guests, :created_at)');
            $insertBooking->execute([
                ':user_id' => $user_id,
                ':room_type' => $room,
                ':check_in' => $checkin,
                ':check_out' => $checkout,
                ':guests' => $guests,
                ':created_at' => date('Y-m-d H:i:s'),
            ]);

            $total_price += $accomTotal;
        }

        $pdo->commit();

        // Show confirmation
        ?>
        <!DOCTYPE html>
        <html lang="en"><head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width,initial-scale=1">
            <title>Confirm and Pay</title>
            <link rel="stylesheet" href="CSS/payment.css">
        </head>
        <body>
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
        <div class="Content">
            <p>Experience Riget Zoo Adventures</p>
            <h>Confirm and Pay</h>
            <div class="Dashboard">
                <div class="Dashboard-left">
                    <h3>Booking Confirmed</h3>
                    <p>Thank you. Your booking has been recorded. A confirmation will be sent to <strong><?php echo htmlspecialchars($email); ?></strong> if email delivery is configured.</p>
                    <p><strong>Total charged:</strong> £<?php echo number_format($total_price,2); ?></p>
                </div>
                <div class="PricingPanel">
                    <h4>Next Steps</h4>
                    <p>Placeholder: payment gateway integration goes here.</p>
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
        </body></html>
        <?php
        exit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $err = $e->getMessage();
        redirect_with_msg('booking.php', 'error', $err);
    }
}

// If GET: render the confirmation page with breakdown and a POST form
// Collect data from GET or session
$date = $_GET['date'] ?? $_GET['checkin'] ?? $_SESSION['booking_date'] ?? '';
$type = strtolower($_GET['type'] ?? $type);

$ticket_qtys = [
    'adult' => (int)($_GET['qty_adult'] ?? $_SESSION['ticket']['qty_adult'] ?? 0),
    'child' => (int)($_GET['qty_child'] ?? $_SESSION['ticket']['qty_child'] ?? 0),
    'student' => (int)($_GET['qty_student'] ?? $_SESSION['ticket']['qty_student'] ?? 0),
    'under3' => (int)($_GET['qty_under3'] ?? $_SESSION['ticket']['qty_under3'] ?? 0),
];

$room_type = $_GET['room_type'] ?? '';
$adults = (int)($_GET['adults_count'] ?? 0);
$under16 = (int)($_GET['under16_count'] ?? 0);
$checkin = $_GET['checkin'] ?? $_GET['date'] ?? '';
$checkout = $_GET['checkout'] ?? '';

// Compute totals
$ticket_total = 0.0;
foreach ($ticket_qtys as $k => $q) {
    $ticket_total += ($PRICES[$k] ?? 0) * $q;
}

$accom_total = 0.0;
$nights = 1;
if ($checkout && $checkin) {
    $n1 = strtotime($checkin);
    $n2 = strtotime($checkout);
    $diff = (int)(($n2 - $n1) / 86400);
    $nights = $diff > 0 ? $diff : 1;
}
if ($room_type) {
    $roomFee = $ROOM_FEES[$room_type] ?? 0.0;
    $perNightPeople = ($adults * 50.00) + ($under16 * 30.00);
    $accom_total = ($perNightPeople + $roomFee) * $nights;
}

$grand_total = $ticket_total + $accom_total;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Confirm and Pay</title>
    <link rel="stylesheet" href="CSS/payment.css">
</head>
<body>
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

    <div class="Content">
        <p>Experience Riget Zoo Adventures</p>
        <h>Confirm and Pay</h>

        <form method="post">
            <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">
            <input type="hidden" name="date" value="<?php echo htmlspecialchars($date); ?>">
            <input type="hidden" name="checkin" value="<?php echo htmlspecialchars($checkin); ?>">
            <input type="hidden" name="checkout" value="<?php echo htmlspecialchars($checkout); ?>">
            <input type="hidden" name="room_type" value="<?php echo htmlspecialchars($room_type); ?>">
            <input type="hidden" name="adults_count" value="<?php echo htmlspecialchars($adults); ?>">
            <input type="hidden" name="under16_count" value="<?php echo htmlspecialchars($under16); ?>">
            <input type="hidden" name="qty_adult" value="<?php echo htmlspecialchars($ticket_qtys['adult']); ?>">
            <input type="hidden" name="qty_child" value="<?php echo htmlspecialchars($ticket_qtys['child']); ?>">
            <input type="hidden" name="qty_student" value="<?php echo htmlspecialchars($ticket_qtys['student']); ?>">
            <input type="hidden" name="qty_under3" value="<?php echo htmlspecialchars($ticket_qtys['under3']); ?>">

            <div class="Dashboard">
                <div class="Dashboard-left">
                    <div class="BoardTitle">Order Summary</div>
                    <?php if ($ticket_total > 0): ?>
                        <h4>Tickets</h4>
                        <ul>
                            <?php foreach ($ticket_qtys as $k => $q): if ($q<=0) continue; ?>
                                <li><?php echo ucfirst($k); ?> x <?php echo $q; ?> @ £<?php echo number_format($PRICES[$k],2); ?> = £<?php echo number_format($PRICES[$k]*$q,2); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <p><strong>Tickets total:</strong> £<?php echo number_format($ticket_total,2); ?></p>
                    <?php endif; ?>

                    <?php if ($room_type): ?>
                        <h4>Accommodation</h4>
                        <p>Room: <?php echo htmlspecialchars(ucfirst($room_type)); ?></p>
                        <p>Check-in: <?php echo htmlspecialchars($checkin ?: $date); ?><?php if ($checkout) echo ' - Check-out: '.htmlspecialchars($checkout); ?></p>
                        <p>Nights: <?php echo $nights; ?></p>
                        <p>Adults: <?php echo $adults; ?> at £50.00/night</p>
                        <p>Under 16: <?php echo $under16; ?> at £30.00/night</p>
                        <p>Room fee: £<?php echo number_format($ROOM_FEES[$room_type] ?? 0,2); ?> per night</p>
                        <p><strong>Accommodation total:</strong> £<?php echo number_format($accom_total,2); ?></p>
                    <?php endif; ?>

                    <h3>Total: £<?php echo number_format($grand_total,2); ?></h3>
                </div>

                <div class="PricingPanel">
                    <h4>Email Confirmation</h4>
                    <p>Enter an email to receive the booking confirmation.</p>
                    <input type="email" name="email" class="dashboard-select" placeholder="you@example.com" required>
                    <div style="margin-top:1vh;">
                        <button type="submit" name="confirm_payment" class="continue-btn">Confirm and Pay</button>
                    </div>
                </div>
            </div>
        </form>
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


