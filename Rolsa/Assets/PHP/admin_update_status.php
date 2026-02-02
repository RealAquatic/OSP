<?php
session_start();
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

$result = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode($result);
    exit;
}

if (empty($_SESSION['user_email']) || empty($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    http_response_code(403);
    echo json_encode($result);
    exit;
}

$id = (int)($_POST['consultation_id'] ?? 0);
$status = trim((string)($_POST['status'] ?? ''));

if ($id <= 0 || !in_array($status, ['pending','complete','cancelled'], true)) {
    http_response_code(400);
    echo json_encode($result);
    exit;
}

try {
    $pdo = getPDO();
    // verify status column exists
    $col = $pdo->prepare("SHOW COLUMNS FROM consultations LIKE 'status'");
    $col->execute();
    $hasStatus = (bool)$col->fetch();

    if (!$hasStatus) {
        http_response_code(412);
        echo json_encode(['success' => false, 'error' => 'Server schema missing required column `status`. Please run database migration.']);
        exit;
    }

    $upd = $pdo->prepare('UPDATE consultations SET status = :status WHERE consultation_id = :id');
    $upd->execute([':status' => $status, ':id' => $id]);

    $result['success'] = true;
    echo json_encode($result);
    exit;

} catch (Exception $e) {
    error_log('admin_update_status error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode($result);
    exit;
}

?>
