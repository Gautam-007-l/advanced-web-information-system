<?php
session_start();
require_once 'config/db_connect.php';
require_once 'functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$feedback = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $message = sanitizeInput($_POST['message']);
    $user_id = $_SESSION['user_id'];

    // Save to database
    $stmt = $pdo->prepare("INSERT INTO contact_messages (user_id, name, email, message) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$user_id, $name, $email, $message])) {
        $feedback = "✅ Message sent and saved successfully.";

        // Send optional email to admin
        $subject = "Contact Form Submission from $name";
        $body = "Name: $name<br>Email: $email<br>Message: $message";
        sendEmail('admin@streetwave.com', $subject, $body);
    } else {
        $feedback = "❌ Error saving your message.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - StreetWave</title>
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
        <h1>Contact Us</h1>
        <div class="centered-content">
            <?php if ($feedback): ?>
                <p class="feedback"><?php echo htmlspecialchars($feedback); ?></p>
            <?php endif; ?>
            <form method="POST">
                <input type="text" name="name" placeholder="Your Name" required>
                <input type="email" name="email" placeholder="Your Email" required>
                <textarea name="message" placeholder="Your Message" required></textarea>
                <button type="submit">Send Message</button>
            </form>
        </div>
    </section>

    <footer>
        <p>© Group2/BIT • BITConnect • <a href="#">X</a> • <a href="#">Instagram</a> • <a href="#">YouTube</a> • 2025, Students</p>
    </footer>
</body>
</html>
