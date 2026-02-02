<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

session_start();

$email = trim((string)($_GET['email'] ?? ''));
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([]);
    exit;
}

if (empty($_SESSION['user_email']) || strcasecmp($_SESSION['user_email'], $email) !== 0) {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT consultation_id, first_name, second_name, email, phone_number, form_type, postcode, address, reason, submitted_at FROM consultations WHERE email = :email ORDER BY submitted_at DESC');
    $stmt->execute([':email' => $email]);
    $rows = $stmt->fetchAll();
    echo json_encode($rows);
} catch (Exception $e) {
    error_log('get_consultations_by_email error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([]);
}

?>
