<?php
session_start();
require_once 'config/db_connect.php';
require_once 'functions.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email          = sanitizeInput($_POST['email']);
    $newPwdPlain    = $_POST['password'];

    /* ---------- basic validation ---------- */
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
        exit;
    }
    if (strlen($newPwdPlain) < 8) {
        echo "Password must be at least 8 characters.";
        exit;
    }

    /* ---------- locate account ---------- */
    $check = $pdo->prepare("SELECT email FROM users WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() === 0) {
        echo "Email not found.";
        exit;
    }

    /* ---------- update password ---------- */
    $newPwdHash = password_hash($newPwdPlain, PASSWORD_DEFAULT);
    $update     = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    if ($update->execute([$newPwdHash, $email])) {
        echo "Password updated successfully. <a href='login.php'>Login</a>";
    } else {
        echo "Error updating password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="centered-content">
        <h1>Reset Password</h1>
        <form method="POST">
            <input type="email"
                   name="email"
                   placeholder="Your Email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                   required>

            <input type="password"
                   name="password"
                   placeholder="New Password"
                   required>

            <button type="submit">Reset Password</button>
        </form>
        <a href="login.php">Back to Login</a>
    </div>
</body>
</html>
