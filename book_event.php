<?php
session_start();
require_once 'config/db_connect.php';
require_once 'functions.php';
require_once 'send_notification.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$event_id = (int)$_GET['event_id'];
$stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = false;

// Get already booked datetimes for this event
$bookedSlots = [];
$bookedStmt = $pdo->prepare("SELECT booking_date FROM bookings WHERE event_id = ?");
$bookedStmt->execute([$event_id]);
foreach ($bookedStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $bookedSlots[] = $row['booking_date'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $date = $_POST['booking_date'] ?? '';
    $time = $_POST['booking_time'] ?? '';

    if (!$date || !$time) {
        $error = "Please select both date and time.";
    } else {
        $booking_date = $date . ' ' . $time;

        if (strtotime($booking_date) <= time()) {
            $error = "Booking must be in the future.";
        } elseif (in_array($booking_date, $bookedSlots)) {
            $error = "This slot is already booked. Please choose another.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO bookings (event_id, user_id, booking_date, booking_status) 
                                   VALUES (?, ?, ?, 'pending')");
            if ($stmt->execute([$event_id, $user_id, $booking_date])) {
                $booking_id = $pdo->lastInsertId();

                // Send notification (optional)
                $subject = "Booking Confirmation for {$event['title']}";
                $body = "You successfully booked '{$event['title']}' for $booking_date.<br>
                         Location: {$event['location']}<br>
                         Price: $" . number_format($event['price'], 2);
                sendNotification($user_id, 'email', $subject, $body);

                header("Location: booking_cart.php?booking_id=$booking_id");
                exit;
            } else {
                $error = "Booking failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Event</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .disabled-option {
            color: #aaa;
        }
    </style>
</head>
<body>
    <div class="centered-content">
        <h1>Book: <?php echo htmlspecialchars($event['title']); ?></h1>
        <p><?php echo htmlspecialchars($event['description']); ?></p>
        <p>Date: <?php echo $event['event_date']; ?></p>
        <p>Location: <?php echo htmlspecialchars($event['location']); ?></p>
        <p>Price: $<?php echo number_format($event['price'], 2); ?></p>
        <?php if ($event['photo']): ?>
            <img src="<?php echo htmlspecialchars($event['photo']); ?>" alt="Event Photo" class="event-image-large">
        <?php endif; ?>

        <?php if ($error): ?>
            <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="booking_date">Select Date:</label><br>
            <input type="date" name="booking_date" min="<?php echo date('Y-m-d'); ?>" required><br><br>

            <label for="booking_time">Select Time:</label><br>
            <select name="booking_time" required>
                <option value="">-- Select Time --</option>
                <?php
                for ($h = 9; $h <= 17; $h++) {
                    $time = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00:00';
                    $label = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
                    $fullSlot = isset($_POST['booking_date']) ? $_POST['booking_date'] . ' ' . $time : '';
                    $disabled = in_array($fullSlot, $bookedSlots);
                    echo '<option value="' . $time . '"' . ($disabled ? ' disabled class="disabled-option"' : '') . '>' .
                         ($disabled ? "$label (Booked)" : $label) . '</option>';
                }
                ?>
            </select><br><br>

            <button type="submit">Confirm Booking</button>
        </form>
    </div>
</body>
</html>
