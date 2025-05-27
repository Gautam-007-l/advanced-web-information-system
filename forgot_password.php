<?php
session_start();
require_once 'config/db_connect.php';
require_once 'functions.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizeInput($_POST['email']);
    $new_password_raw = $_POST['new_password'];

    // Validate input
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
        exit;
    }

    if (strlen($new_password_raw) < 8) {
        echo "Password must be at least 8 characters.";
        exit;
    }

    // Check if user exists
    $stmt = $pdo->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        $new_password_hashed = password_hash($new_password_raw, PASSWORD_DEFAULT);

        $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        if ($updateStmt->execute([$new_password_hashed, $email])) {
            echo "Password updated successfully. <a href='login.php'>Login</a>";
        } else {
            echo "Error updating password.";
        }
    } else {
        echo "Email not found.";
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
            <input type="email" name="email" placeholder="Your Email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            <input type="password" name="new_password" placeholder="New Password" required>
            <button type="submit">Reset Password</button>
        </form>
        <a href="login.php">Back to Login</a>
    </div>
</body>
</html>
