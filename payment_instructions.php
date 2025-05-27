<?php
session_start();
require_once 'config/db_connect.php';
require_once 'functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$booking_id = (int)($_GET['booking_id'] ?? 0);
if (!$booking_id) {
    header("Location: index.php");
    exit;
}

// Fetch booking info
$stmt = $pdo->prepare("SELECT e.title, e.price FROM bookings b JOIN events e ON b.event_id = e.event_id WHERE b.booking_id = ? AND b.user_id = ?");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    header("Location: index.php");
    exit;
}

$success = false;
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_id = trim($_POST['transaction_id'] ?? '');

    if (empty($transaction_id)) {
        $error = "Please enter the transaction/reference number.";
    } else {
        // Here you could save the payment info in DB if you want:
        // $stmt = $pdo->prepare("UPDATE bookings SET payment_reference = ?, payment_status = 'paid' WHERE booking_id = ?");
        // $stmt->execute([$transaction_id, $booking_id]);

        $success = true;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment Instructions</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="centered-content">
    <h1>Payment Instructions</h1>

    <?php if ($success): ?>
        <p><strong>Payment successful!</strong> Thank you for your payment.</p>
        <p><a href="index.php">Back to Home</a></p>
    <?php else: ?>
        <p>Please complete your payment for the booking of "<strong><?php echo htmlspecialchars($booking['title']); ?></strong>".</p>
        <p>Amount to pay: <strong>$<?php echo number_format($booking['price'], 2); ?></strong></p>
        <p>Make a bank transfer to the following account:</p>
        <p><strong>Bank Name:</strong> Acme Bank</p>
        <p><strong>Account Number:</strong> 9876543210</p>
        <p>After payment, please enter your transaction/reference number below to confirm your payment.</p>

        <?php if ($error): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="transaction_id">Transaction/Reference Number:</label><br>
            <input type="text" name="transaction_id" id="transaction_id" required>
            <br><br>
            <button type="submit">Confirm Payment</button>
        </form>

        <p><a href="index.php">Back to Home</a></p>
    <?php endif; ?>
</div>
</body>
</html>
