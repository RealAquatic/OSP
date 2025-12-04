<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../account.php');
    exit();
}

if (!isset($_SESSION['loggedin']) || empty($_SESSION['id'])) {
    $_SESSION['error'] = 'You must be logged in to cancel bookings.';
    header('Location: ../account.php');
    exit();
}

$booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
$user_id = $_SESSION['id'];

if ($booking_id <= 0) {
    $_SESSION['error'] = 'Invalid booking.';
    header('Location: ../account.php');
    exit();
}

// Verify ownership and that check_in is in future
$stmt = $pdo->prepare('SELECT check_in FROM bookings WHERE id = ? AND user_id = ?');
$stmt->execute([$booking_id, $user_id]);
$row = $stmt->fetch();

if (!$row) {
    $_SESSION['error'] = 'Booking not found or not owned by you.';
    header('Location: ../account.php');
    exit();
}

$check_in = $row['check_in'];
if (strtotime($check_in) < strtotime(date('Y-m-d'))) {
    $_SESSION['error'] = 'Cannot cancel past bookings.';
    header('Location: ../account.php');
    exit();
}

$del = $pdo->prepare('DELETE FROM bookings WHERE id = ? AND user_id = ?');
$ok = $del->execute([$booking_id, $user_id]);

if ($ok) {
    $_SESSION['message'] = 'Booking cancelled successfully.';
} else {
    $_SESSION['error'] = 'Failed to cancel booking. Try again.';
}

header('Location: ../account.php');
exit();
