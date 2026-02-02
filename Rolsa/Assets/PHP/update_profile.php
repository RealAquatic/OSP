<?php
session_start();
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

$data = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode($data);
    exit;
}

if (empty($_SESSION['user_email'])) {
    http_response_code(401);
    echo json_encode($data);
    exit;
}

$email = $_SESSION['user_email'];
$fullName = trim((string)($_POST['full_name'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));

if ($fullName === '') {
    http_response_code(400);
    echo json_encode($data);
    exit;
}

try {
    $pdo = getPDO();
    $colCheck = $pdo->prepare("SHOW COLUMNS FROM users LIKE 'phone'");
    $colCheck->execute();
    $hasPhone = (bool)$colCheck->fetch();
    if ($hasPhone) {
        $stmt = $pdo->prepare('UPDATE users SET full_name = :full_name, phone = :phone WHERE email = :email');
        $stmt->execute([':full_name' => $fullName, ':phone' => $phone ?: null, ':email' => $email]);
        $data['phone'] = $phone;
    } else {
        $stmt = $pdo->prepare('UPDATE users SET full_name = :full_name WHERE email = :email');
        $stmt->execute([':full_name' => $fullName, ':email' => $email]);
        $data['phone'] = null;
    }
    $data['success'] = true;
    $data['full_name'] = $fullName;
    $data['phone'] = $phone;
    $_SESSION['full_name'] = $fullName;
    echo json_encode($data);
    exit;
} catch (Exception $e) {
    error_log('update_profile error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode($data);
    exit;
}

?>
