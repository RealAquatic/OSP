<?php
session_start();
require 'PHP/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RZA | Account</title>

    <!-- Use the account CSS you provided -->
    <link rel="stylesheet" href="CSS/account.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Italiana&family=Just+Me+Again+Down+Here&display=swap" rel="stylesheet">
</head>
<body>

<!-- HEADER TEMPLATE -->
<div class="Display">
    <div class="Header">
        <div class="TopBar">
            <div class="Logo">RZA</div>
            <div class="SiteTitle"><a href="index.html">Riget Zoo Adventures</a></div>
        </div>

        <div class="BottomBar">
            <a href="booking.php">Book Your Tickets</a>
            <a href="educational.html">Educational Visits</a>
            <a href="information.html">Information</a>
            <a href="account.php" style="color: rgb(202,182,154);">Account</a>
        </div>
    </div>
</div>

<!-- PAGE CONTENT -->
<div class="Content">

    <p>Experience Riget Zoo Adventures</p>
    <h class="page-title">Manage Your Account</h>

    <!-- NOT LOGGED IN -->
    <?php if (!isset($_SESSION['loggedin'])): ?>

        <p class="info-text">Login to access your account information</p>
        <br>
        <a href="login.html" class="login-btn">Login</a>
        <br>
        <br>
        <p class="info-text">
            Don't have an account?
            <a href="signup.html">Sign up here</a>
        </p>

    <?php else: ?>
        <!-- LOGGED IN -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="info-text" style="color:green;"><?= htmlspecialchars($_SESSION['message']); ?></div>
            <?php unset($_SESSION['message']); endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="info-text" style="color:red;"><?= htmlspecialchars($_SESSION['error']); ?></div>
            <?php unset($_SESSION['error']); endif; ?>
        <p class="welcome-inline">
            Welcome, <?= htmlspecialchars($_SESSION['firstname']); ?>!
        </p>

        <?php $user_id = $_SESSION['id']; ?>

        <div class="account-box">

            <!-- ACTIVE TICKETS -->
            <section class="account-section">
                <h3>Active Tickets</h3>

                <?php
                $activeStmt = $pdo->prepare("
                    SELECT id, ticket_type, quantity, visit_date
                    FROM tickets
                    WHERE user_id = ? AND visit_date >= CURDATE()
                ");
                $activeStmt->execute([$user_id]);
                $activeTickets = $activeStmt->fetchAll();
                ?>

                <table class="account-table">
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Date</th>
                        <th class="action-cell">Action</th>
                    </tr>

                    <?php if ($activeTickets): ?>
                        <?php foreach ($activeTickets as $t): ?>
                        <tr>
                            <td><?= $t['id']; ?></td>
                            <td><?= ucfirst($t['ticket_type']); ?></td>
                            <td><?= $t['quantity']; ?></td>
                            <td><?= $t['visit_date']; ?></td>
                            <td class="action-cell">
                                <form action="PHP/cancel_ticket.php" method="post" onsubmit="return confirm('Cancel this ticket?');">
                                    <input type="hidden" name="ticket_id" value="<?= $t['id']; ?>">
                                    <button type="submit" class="cancel-btn" title="Cancel ticket">✕</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                            <tr><td colspan="5" class="empty-row">No active tickets.</td></tr>
                    <?php endif; ?>
                </table>
            </section>

            <!-- PAST TICKET HISTORY -->
            <section class="account-section">
                <h3>Ticket History</h3>

                <?php
                $historyStmt = $pdo->prepare("
                    SELECT id, ticket_type, quantity, visit_date
                    FROM tickets
                    WHERE user_id = ? AND visit_date < CURDATE()
                ");
                $historyStmt->execute([$user_id]);
                $historyTickets = $historyStmt->fetchAll();
                ?>

                <table class="account-table">
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Date Used</th>
                    </tr>

                    <?php if ($historyTickets): ?>
                        <?php foreach ($historyTickets as $t): ?>
                        <tr>
                            <td><?= $t['id']; ?></td>
                            <td><?= ucfirst($t['ticket_type']); ?></td>
                            <td><?= $t['quantity']; ?></td>
                            <td><?= $t['visit_date']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="empty-row">No past tickets.</td></tr>
                    <?php endif; ?>
                </table>
            </section>

            <!-- ACCOMMODATION BOOKINGS -->
            <section class="account-section">
                <h3>Accommodation Bookings</h3>

                <?php
                $bookingStmt = $pdo->prepare("
                    SELECT id, room_type, check_in, check_out, guests
                    FROM bookings
                    WHERE user_id = ?
                    ORDER BY check_in DESC
                ");
                $bookingStmt->execute([$user_id]);
                $bookingHistory = $bookingStmt->fetchAll();
                ?>

                <table class="account-table">
                    <tr>
                        <th>ID</th>
                        <th>Room</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Guests</th>
                        <th class="action-cell">Action</th>
                    </tr>

                    <?php if ($bookingHistory): ?>
                        <?php foreach ($bookingHistory as $b): ?>
                        <tr>
                            <td><?= $b['id']; ?></td>
                            <td><?= ucfirst($b['room_type']); ?></td>
                            <td><?= $b['check_in']; ?></td>
                            <td><?= $b['check_out']; ?></td>
                            <td><?= $b['guests']; ?></td>
                            <td class="action-cell">
                                <form action="PHP/cancel_booking.php" method="post" onsubmit="return confirm('Cancel this booking?');">
                                    <input type="hidden" name="booking_id" value="<?= $b['id']; ?>">
                                    <button type="submit" class="cancel-btn" title="Cancel booking">✕</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                            <tr><td colspan="6" class="empty-row">No bookings made.</td></tr>
                    <?php endif; ?>
                </table>
            </section>

        </div>

        <a href="PHP/logout.php" class="login-btn logout-btn">Logout</a>

    <?php endif; ?>

</div>

<!-- FOOTER TEMPLATE -->
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
