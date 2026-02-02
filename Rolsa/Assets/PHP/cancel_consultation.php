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

if (empty($_SESSION['user_email'])) {
    http_response_code(401);
    echo json_encode($result);
    exit;
}

$userEmail = $_SESSION['user_email'];
$id = (int)($_POST['consultation_id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo json_encode($result);
    exit;
}

try {
    $pdo = getPDO();
    $pdo = getPDO();
    $col = $pdo->prepare("SHOW COLUMNS FROM consultations LIKE 'status'");
    $col->execute();
    $hasStatus = (bool)$col->fetch();

    if ($hasStatus) {
        $stmt = $pdo->prepare('SELECT email, status FROM consultations WHERE consultation_id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
    } else {
        $stmt = $pdo->prepare('SELECT email FROM consultations WHERE consultation_id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if ($row) $row['status'] = 'pending';
    }
    if (!$row) {
        http_response_code(404);
        echo json_encode($result);
        exit;
    }
    if (strcasecmp($row['email'], $userEmail) !== 0) {
        http_response_code(403);
        echo json_encode($result);
        exit;
    }
    if ($row['status'] !== 'pending') {
        http_response_code(400);
        echo json_encode($result);
        exit;
    }

    if ($hasStatus) {
        $upd = $pdo->prepare('UPDATE consultations SET status = "cancelled" WHERE consultation_id = :id');
        $upd->execute([':id' => $id]);
    } else {
        http_response_code(412);
        error_log('cancel_consultation: schema missing status column');
        echo json_encode(['success' => false, 'error' => 'Server schema missing required column `status`. Please run database migration.']);
        exit;
    }
    $result['success'] = true;
    echo json_encode($result);
    exit;

} catch (Exception $e) {
    error_log('cancel_consultation error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode($result);
    exit;
}

?>
