<?php
session_start();
require_once 'config/db_connect.php';
require_once 'functions.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['booking_id'])) {
    die("Booking ID not provided.");
}

$booking_id = intval($_GET['booking_id']);

// Fetch booking details
$stmt = $pdo->prepare("
    SELECT b.*, u.username, u.email, e.title, e.price, e.event_date 
    FROM bookings b 
    JOIN users u ON b.user_id = u.user_id 
    JOIN events e ON b.event_id = e.event_id 
    WHERE b.booking_id = ?
");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    die("Booking not found.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Invoice</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="centered-content">
        <h1>Invoice</h1>
        <p><strong>Booking ID:</strong> <?php echo $booking['booking_id']; ?></p>
        <p><strong>User:</strong> <?php echo htmlspecialchars($booking['username']); ?> (<?php echo htmlspecialchars($booking['email']); ?>)</p>
        <p><strong>Event:</strong> <?php echo htmlspecialchars($booking['title']); ?></p>
        <p><strong>Event Date:</strong> <?php echo htmlspecialchars($booking['event_date']); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($booking['booking_status']); ?></p>
        <p><strong>Amount Paid:</strong> $<?php echo number_format($booking['price'], 2); ?></p>

        <br><br>
        <a href="admin_dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>
