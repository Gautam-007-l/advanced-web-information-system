<?php
session_start();
require_once 'config/db_connect.php';
require_once 'functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle removal of single booking
if (isset($_GET['remove_booking_id'])) {
    $booking_id = (int)$_GET['remove_booking_id'];
    $stmt = $pdo->prepare("DELETE FROM bookings WHERE booking_id = ? AND user_id = ? AND booking_status = 'pending'");
    $stmt->execute([$booking_id, $user_id]);
    header("Location: booking_cart.php");
    exit;
}

// Handle clear all bookings
if (isset($_GET['clear_all']) && $_GET['clear_all'] == '1') {
    $stmt = $pdo->prepare("DELETE FROM bookings WHERE user_id = ? AND booking_status = 'pending'");
    $stmt->execute([$user_id]);
    header("Location: booking_cart.php");
    exit;
}

// Fetch pending bookings with event location and creator username
$stmt = $pdo->prepare("
    SELECT b.*, e.title, e.price, e.photo, e.location, u.username AS creator
    FROM bookings b
    JOIN events e ON b.event_id = e.event_id
    JOIN users u ON e.user_id = u.user_id
    WHERE b.user_id = ? AND b.booking_status = 'pending'
    ORDER BY b.booking_date DESC
");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total price
$total_price = 0;
foreach ($bookings as $booking) {
    $total_price += $booking['price'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Booking Cart</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .event-list { display: flex; flex-wrap: wrap; gap: 20px; }
        .event-item { border: 1px solid #ccc; padding: 15px; width: 300px; }
        .event-item img { max-width: 100%; height: auto; }
        .actions { margin-top: 10px; }
        .actions a { margin-right: 10px; }
        .total-price { font-weight: bold; font-size: 1.2em; margin-top: 20px; }
        .clear-all-btn { margin-top: 20px; display: inline-block; padding: 10px 15px; background: #d9534f; color: #fff; text-decoration: none; border-radius: 5px; }
        .clear-all-btn:hover { background: #c9302c; }
    </style>
</head>
<body>
    <header>
        <div class="logo">Event Planner</div>
        <nav>
            <a href="index.php">Home</a>
            <a href="services.php">Services</a>
            <a href="booking_cart.php" class="active">Event Booking Cart</a>
            <a href="booking_summary.php">Booking Summary</a>
            <a href="user_dashboard.php">Dashboard</a>
            <a href="contact.php">Contact</a>
            <a href="about.php">About</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main class="event-page">
        <h1>Your Booking Cart</h1>

        <?php if (empty($bookings)): ?>
            <p>Your booking cart is currently empty.</p>
        <?php else: ?>
            <div class="event-list">
                <?php foreach ($bookings as $booking): ?>
                    <div class="event-item">
                        <?php if ($booking['photo']): ?>
                            <img src="<?php echo htmlspecialchars($booking['photo']); ?>" alt="<?php echo htmlspecialchars($booking['title']); ?>">
                        <?php endif; ?>

                        <h3><?php echo htmlspecialchars($booking['title']); ?></h3>

                        <p><strong>Booking Date:</strong> 
                            <?php 
                                $dateTime = new DateTime($booking['booking_date']);
                                echo $dateTime->format('F j, Y \a\t g:i A');
                            ?>
                        </p>

                        <p><strong>Location:</strong> <?php echo htmlspecialchars($booking['location']); ?></p>
                        <p><strong>Host:</strong> <?php echo htmlspecialchars($booking['creator']); ?></p>

                        <p><strong>Price:</strong> $<?php echo number_format($booking['price'], 2); ?></p>

                        <p><strong>Status:</strong> <?php echo htmlspecialchars($booking['booking_status']); ?></p>

                        <div class="actions">
                            <a href="payment_instructions.php?booking_id=<?php echo $booking['booking_id']; ?>">Proceed to Payment</a>
                            <a href="booking_cart.php?remove_booking_id=<?php echo $booking['booking_id']; ?>" onclick="return confirm('Remove this booking?');" style="color:red;">Remove</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <p class="total-price">Total Price: $<?php echo number_format($total_price, 2); ?></p>

            <a href="booking_cart.php?clear_all=1" class="clear-all-btn" onclick="return confirm('Are you sure you want to clear all bookings from your cart?');">Clear All Bookings</a>
        <?php endif; ?>
    </main>

    <footer>
        <p>© Group2/BIT • BITConnect • 2025</p>
    </footer>
</body>
</html>
