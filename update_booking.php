<?php
session_start();
require_once 'config/db_connect.php';
require_once 'functions.php';
require_once 'send_notification.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit;
}

$booking_id = (int)$_GET['booking_id'];
$status = sanitizeInput($_GET['status']);

if ($status === 'paid' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_token = sanitizeInput($_POST['payment_token']);
    $stmt = $pdo->prepare("SELECT user_id, event_id FROM bookings WHERE booking_id = ? AND payment_token = ? AND payment_token_expiry > NOW()");
    $stmt->execute([$booking_id, $payment_token]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($booking) {
        $stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'paid', payment_token = NULL, payment_token_expiry = NULL WHERE booking_id = ?");
        $stmt->execute([$booking_id]);

        $stmt = $pdo->prepare("SELECT u.email, e.title FROM bookings b 
                               JOIN users u ON b.user_id = u.user_id 
                               JOIN events e ON b.event_id = e.event_id 
                               WHERE b.booking_id = ?");
        $stmt->execute([$booking_id]);
        $booking_info = $stmt->fetch(PDO::FETCH_ASSOC);
        $body = "Your booking for '{$booking_info['title']}' has been confirmed and marked as paid.";
        sendEmail($booking_info['email'], "Booking Confirmation", $body);
        header("Location: admin_dashboard.php");
        exit;
    } else {
        echo "Invalid or expired payment token.";
    }
} elseif (in_array($status, ['confirmed', 'cancelled'])) {
    $stmt = $pdo->prepare("UPDATE bookings SET booking_status = ? WHERE booking_id = ?");
    $stmt->execute([$status, $booking_id]);
    header("Location: admin_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify Payment</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="centered-content">
        <h1>Verify Payment</h1>
        <?php if ($status === 'paid'): ?>
            <form method="POST">
                <input type="text" name="payment_token" placeholder="Enter Payment Token" required>
                <button type="submit">Mark as Paid</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>