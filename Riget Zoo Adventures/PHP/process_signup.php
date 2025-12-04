<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../signup.html");
    exit();
}

$firstname = trim($_POST["firstname"]);
$lastname = trim($_POST["lastname"]);
$email = trim($_POST["email"]);
$password = $_POST["password"];
$confirm = $_POST["confirm_password"];

if ($password !== $confirm) {
    $_SESSION['error'] = "Passwords do not match.";
    header("Location: ../signup.html");
    exit();
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);

if ($stmt->rowCount() > 0) {
    $_SESSION['error'] = "Email already registered.";
    header("Location: ../signup.html");
    exit();
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (firstname, lastname, email, password) VALUES (?, ?, ?, ?)");
$stmt->execute([$firstname, $lastname, $email, $hash]);

$_SESSION['loggedin'] = true;
$_SESSION['firstname'] = $firstname;
$_SESSION['email'] = $email;
$_SESSION['id'] = $pdo->lastInsertId();

header("Location: ../account.php");
exit();
