<?php
session_start();
require_once 'config/db_connect.php';
require_once 'functions.php';
require_once 'send_notification.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit;
}

$event_id = (int)$_GET['event_id'];
$stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    header("Location: admin_dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_date = sanitizeInput($_POST['new_date']);
    $stmt = $pdo->prepare("UPDATE events SET event_date = ? WHERE event_id = ?");
    if ($stmt->execute([$new_date, $event_id])) {
        // Notify users with bookings for this event
        $stmt = $pdo->prepare("SELECT u.user_id, u.email, e.title FROM bookings b 
                               JOIN users u ON b.user_id = u.user_id 
                               JOIN events e ON b.event_id = e.event_id 
                               WHERE b.event_id = ?");
        $stmt->execute([$event_id]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($bookings as $booking) {
            $subject = "Event Rescheduled";
            $body = "The event '{$booking['title']}' has been rescheduled to $new_date.";
            sendNotification($booking['user_id'], 'email', $subject, $body);
        }
        header("Location: admin_dashboard.php");
        exit;
    } else {
        echo "Error rescheduling event.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reschedule Event</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="centered-content">
        <h1>Reschedule Event: <?php echo htmlspecialchars($event['title']); ?></h1>
        <p>Current Date: <?php echo $event['event_date']; ?></p>
        <form method="POST">
            <input type="datetime-local" name="new_date" required>
            <button type="submit">Reschedule</button>
        </form>
        <a href="admin_dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>