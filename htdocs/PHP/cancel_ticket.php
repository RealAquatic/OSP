<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../account.php');
    exit();
}

if (!isset($_SESSION['loggedin']) || empty($_SESSION['id'])) {
    $_SESSION['error'] = 'You must be logged in to cancel tickets.';
    header('Location: ../account.php');
    exit();
}

$ticket_id = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
$user_id = $_SESSION['id'];

if ($ticket_id <= 0) {
    $_SESSION['error'] = 'Invalid ticket.';
    header('Location: ../account.php');
    exit();
}

// Verify ownership and that visit_date is in future or today
$stmt = $pdo->prepare('SELECT visit_date FROM tickets WHERE id = ? AND user_id = ?');
$stmt->execute([$ticket_id, $user_id]);
$row = $stmt->fetch();

if (!$row) {
    $_SESSION['error'] = 'Ticket not found or not owned by you.';
    header('Location: ../account.php');
    exit();
}

$visit_date = $row['visit_date'];
if (strtotime($visit_date) < strtotime(date('Y-m-d'))) {
    $_SESSION['error'] = 'Cannot cancel past tickets.';
    header('Location: ../account.php');
    exit();
}

$del = $pdo->prepare('DELETE FROM tickets WHERE id = ? AND user_id = ?');
$ok = $del->execute([$ticket_id, $user_id]);

if ($ok) {
    $_SESSION['message'] = 'Ticket cancelled successfully.';
} else {
    $_SESSION['error'] = 'Failed to cancel ticket. Try again.';
}

header('Location: ../account.php');
exit();
