<?php
session_start();
require 'db.php'; // same folder

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../account.php");
    exit();
}

$email = trim($_POST['email']);
$password = $_POST['password'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error'] = "Email not found.";
    header("Location: ../account.php");
    exit();
}

if (!password_verify($password, $user['password'])) {
    $_SESSION['error'] = "Incorrect password.";
    header("Location: ../account.php");
    exit();
}

$_SESSION['loggedin'] = true;
$_SESSION['id'] = $user['id'];
$_SESSION['firstname'] = $user['firstname'];
$_SESSION['lastname'] = $user['lastname'];
$_SESSION['email'] = $user['email'];

header("Location: ../account.php");
exit();
