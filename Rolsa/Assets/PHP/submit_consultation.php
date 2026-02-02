<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /index.html');
    exit;
}

$firstName = trim((string)($_POST['FirstName'] ?? ''));
$lastName  = trim((string)($_POST['LastName'] ?? ''));
$email     = trim((string)($_POST['Email'] ?? ''));
$phone     = trim((string)($_POST['Phone'] ?? ''));
$formType  = trim((string)($_POST['FormType'] ?? 'consultation'));
$postcode  = trim((string)($_POST['Postcode'] ?? ''));
$address   = trim((string)($_POST['Address'] ?? ''));
$reason    = trim((string)($_POST['Reason'] ?? ''));

$errors = [];
if ($firstName === '') $errors[] = 'First name is required.';
if ($lastName === '') $errors[] = 'Second name is required.';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
if ($phone === '') $errors[] = 'Phone number is required.';
if ($postcode === '') $errors[] = 'Postcode is required.';
if ($reason === '') $errors[] = 'Reason is required.';

if (!in_array($formType, ['installation', 'consultation'], true)) {
    $formType = 'consultation';
}

if (!empty($errors)) {
    $msg = urlencode(implode(' ', $errors));
    header("Location: /index.html?error={$msg}");
    exit;
}

try {
    $pdo = getPDO();

    $insert = $pdo->prepare('INSERT INTO consultations (first_name, second_name, email, phone_number, form_type, postcode, address, reason) VALUES (:first_name, :second_name, :email, :phone_number, :form_type, :postcode, :address, :reason)');
    $insert->execute([
        ':first_name' => $firstName,
        ':second_name' => $lastName,
        ':email' => $email,
        ':phone_number' => $phone,
        ':form_type' => $formType,
        ':postcode' => $postcode,
        ':address' => $address,
        ':reason' => $reason,
    ]);

    header('Location: /index.html?success=1');
    exit;

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    error_log('Consultation submit error: ' . $e->getMessage());
    $msg = urlencode('Server error saving consultation.');
    header("Location: /index.html?error={$msg}");
    exit;
}

?>
