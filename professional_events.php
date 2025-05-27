<?php
session_start();
require_once 'config/db_connect.php';
require_once 'functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$events = $pdo->query("SELECT e.* FROM events e JOIN categories c ON e.category_id = c.category_id WHERE c.category_name = 'Professional'")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Events - StreetWave</title>
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
        <h1>Professional Events</h1>
        <div class="event-list">
            <?php foreach ($events as $event): ?>
                <div class="event-item">
                    <?php if ($event['photo']): ?>
                        <img src="<?php echo htmlspecialchars($event['photo']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>">
                    <?php else: ?>
                        <p>No photo available</p>
                    <?php endif; ?>
                    <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                    <p><?php echo htmlspecialchars($event['event_date']); ?></p>
                    <p>$<?php echo number_format($event['price'], 2); ?></p>
                    <a href="book_event.php?event_id=<?php echo $event['event_id']; ?>">Book Now</a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <footer>
        <p>© Group2/BIT • BITConnect • <a href="#">X</a> • <a href="#">Instagram</a> • <a href="#">YouTube</a> • 2025, Students</p>
    </footer>
</body>
</html>