<?php
session_start();
require_once __DIR__ . '/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim((string)($_POST['FullName'] ?? ''));
    $email = trim((string)($_POST['Email'] ?? ''));
    $password = (string)($_POST['Password'] ?? '');
    $confirm = (string)($_POST['ConfirmPassword'] ?? '');

    if ($fullName === '' || $email === '' || $password === '' || $confirm === '') {
        $error = 'Please complete all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) <= 8 || !preg_match('/[0-9]/', $password) || !preg_match('/[!@#$%^&*()_+\-=[\]{};:\'"\\|,.<>\/?]/', $password)) {
        $error = 'Password must be longer than 8 characters and include at least one number and one special character.';
    } else {
        try {
            $pdo = getPDO();
            $stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                $error = 'An account with that email already exists.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ins = $pdo->prepare('INSERT INTO users (full_name, email, password) VALUES (:full_name, :email, :password)');
                $ins->execute([':full_name' => $fullName, ':email' => $email, ':password' => $hash]);
                $_SESSION['user_email'] = $email;
                $_SESSION['full_name'] = $fullName;
                header('Location: /Assets/PHP/account.php');
                exit;
            }
        } catch (Exception $e) {
            error_log('Signup error: ' . $e->getMessage());
            $error = 'Server error. Please try again later.';
        }
    }
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Sign up - Rolsa Technologies</title>
    <link rel="stylesheet" href="/Assets/CSS/Header.css">
    <link rel="stylesheet" href="/Assets/CSS/Main.css">
    <link rel="stylesheet" href="/Assets/CSS/Footer.css">
    <style>
        .AccountBox { max-width:520px; margin:2.5rem auto; background:#fff; padding:1.25rem; border-radius:10px; box-shadow:0 3px 10px rgba(0,0,0,0.08); }
        .SmallLink { color:#1e6bd8; text-decoration:underline; }
    </style>
</head>
<body>
    <div class="Header">
        <div class="Logo">
            <a href="/index.html"><img class="LogoImg" src="/Assets/Images/RolsaLogo.png" alt="Rolsa Logo"></a>
        </div>
        <nav class="Navigation">
            <a href="/index.html#home">Home</a>
            <a href="/index.html#aboutus">About us</a>
            <a href="/products.html">Products</a>
            <a href="/index.html#consultations">Consultations</a>
            <a href="/index.html#calculator">Calculator</a>
        </nav>
        <a class="Account" href="/Assets/PHP/account.php">
            <img class="AccountImg" src="/Assets/Images/YourAccountImage.png" alt="Your Account">
            <div class="AccountLabel">Your Account</div>
        </a>
    </div>

    <main class="Main" style="padding-top: calc(var(--header-height) + 1rem);">
        <section class="Section">
            <div class="SectionInner">
                <div class="AccountBox">
                    <h2>Create an account</h2>
                    <p id="ErrorMessage" class="eMsg"><?php if ($error) echo htmlspecialchars($error); ?></p>
                    <form method="post" action="/Assets/PHP/signup.php">
                        <div class="FormGroup">
                            <label for="FullName">Full name</label>
                            <input id="FullName" name="FullName" type="text" required>
                        </div>
                        <div class="FormGroup">
                            <label for="Email">Email address</label>
                            <input id="Email" name="Email" type="email" required>
                        </div>
                        <div class="FormGroup">
                            <label for="Password">Password</label>
                            <input id="Password" name="Password" type="password" required>
                        </div>
                        <div class="FormGroup">
                            <label for="ConfirmPassword">Confirm password</label>
                            <input id="ConfirmPassword" name="ConfirmPassword" type="password" required>
                        </div>
                        <div style="text-align:center; margin-top:0.75rem;">
                            <button type="submit" class="PrimaryButton">Create account</button>
                        </div>
                    </form>
                    <p style="text-align:center; margin-top:0.75rem;">Already have an account? <a href="/Assets/PHP/account.php" class="SmallLink">Sign in</a></p>
                </div>
            </div>
        </section>
    </main>

    <script src="/Assets/JS/cookies.js"></script>
    <script src="/Assets/JS/account.js"></script>
    <div class="Footer">
        <div class="FooterInner">
            <div class="FooterLeft">
                <img class="FooterLogo" src="/Assets/Images/WhiteLogo.png" alt="Rolsa White Logo">
                <div class="FooterCopy">© 2026 Rolsa Technologies. All rights reserved.</div>
                <div class="FooterDetails">
                    <div>Stamford Drift Road, Cambridgeshire </div>
                    <div>support@rolsa.com</div>
                    <div>+44 01777 666888</div>
                </div>
            </div>
            <div class="FooterRight">
                <div class="FooterBlock">
                    <div class="FooterTitle">Navigation</div>
                    <div class="FooterBar"></div>
                    <ul class="FooterList">
                        <li><a href="/index.html#home">Home</a></li>
                        <li><a href="/index.html#aboutus">About Us</a></li>
                        <li><a href="/products.html">Products</a></li>
                        <li><a href="/index.html#consultations">Consultations</a></li>
                        <li><a href="/index.html#calculator">Calculator</a></li>
                    </ul>
                </div>
                <div class="FooterBlock">
                    <div class="FooterTitle">Legal</div>
                    <div class="FooterBar"></div>
                    <ul class="FooterList">
                        <li><a href="/privacy.html">Privacy policy</a></li>
                        <li><a href="/terms.html">Terms &amp; Conditions</a></li>
                        <li><a href="/cookies.html">Cookie policy</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
