<?php
session_start();
require_once 'config/db_connect.php';
require_once 'functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$recent_bookings = $pdo->query("SELECT b.*, e.title, e.event_date, e.price 
                                FROM bookings b 
                                JOIN events e ON b.event_id = e.event_id 
                                WHERE b.user_id = $user_id 
                                ORDER BY b.booking_date DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - StreetWave</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <div class="logo">Event Planner</div>
        <nav>
            <a href="index.php">Home</a>
            <a href="services.php">Services</a>
            <a href="booking_cart.php">Event Booking Cart</a>
            <a href="booking_summary.php">Booking Summary</a>
            <a href="user_dashboard.php">Dashboard</a>
            <a href="contact.php">Contact Us</a>
            <a href="logout.php">Logout</a>
            <a href="about.php">About Us</a>
        </nav>
    </header>

    <section class="event-page">
        <h1>User Dashboard</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['user_id']); ?>!</p>
        <h2>Quick Links</h2>
        <p><a href="booking_cart.php">View Booking Cart</a></p>
        <p><a href="booking_summary.php">View Booking Summary</a></p>
        <p><a href="index.php">Browse Events</a></p>
        <h2>Recent Bookings</h2>
        <?php if (empty($recent_bookings)): ?>
            <p>You have no recent bookings.</p>
        <?php else: ?>
            <table border="1">
                <tr>
                    <th>Event</th>
                    <th>Date</th>
                    <th>Price</th>
                    <th>Status</th>
                </tr>
                <?php foreach ($recent_bookings as $booking): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($booking['title']); ?></td>
                        <td><?php echo htmlspecialchars($booking['event_date']); ?></td>
                        <td>$<?php echo number_format($booking['price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($booking['booking_status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </section>

    <footer>
        <p>© Group2/BIT • BITConnect • <a href="#">X</a> • <a href="#">Instagram</a> • <a href="#">YouTube</a> • 2025, Students</p>
    </footer>
</body>
</html>