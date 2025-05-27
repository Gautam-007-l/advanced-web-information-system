<?php
session_start();
require_once 'config/db_connect.php';
require_once 'functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$booking_id = (int)$_GET['booking_id'];

// Fetch booking details
$stmt = $pdo->prepare("SELECT b.*, e.title, e.event_date, e.price, e.location, u.username, u.email 
                       FROM bookings b 
                       JOIN events e ON b.event_id = e.event_id 
                       JOIN users u ON b.user_id = u.user_id 
                       WHERE b.booking_id = ? AND b.user_id = ? AND b.booking_status = 'paid'");
$stmt->execute([$booking_id, $user_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    echo "Booking not found or not paid.";
    exit;
}

$invoice_date = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - Booking #<?= htmlspecialchars($booking_id) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            color: #333;
        }
        .header, .footer {
            text-align: center;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            border: 1px solid #eee;
            padding: 30px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        .section {
            margin-top: 20px;
        }
        h2 {
            margin-bottom: 10px;
        }
        .label {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <h1>StreetWave Event Planner</h1>
            <p>Invoice Date: <?= $invoice_date ?></p>
            <p>Booking ID: <?= htmlspecialchars($booking_id) ?></p>
        </div>

        <div class="section">
            <h2>Billed To</h2>
            <p><?= htmlspecialchars($booking['username']) ?><br>
               <?= htmlspecialchars($booking['email']) ?></p>
        </div>

        <div class="section">
            <h2>Event Details</h2>
            <p><span class="label">Title:</span> <?= htmlspecialchars($booking['title']) ?><br>
               <span class="label">Date:</span> <?= htmlspecialchars($booking['event_date']) ?><br>
               <span class="label">Location:</span> <?= htmlspecialchars($booking['location']) ?><br>
               <span class="label">Price:</span> $<?= htmlspecialchars($booking['price']) ?></p>
        </div>

        <div class="section">
            <h2>Payment</h2>
            <p><span class="label">Status:</span> Paid<br>
               <span class="label">Booking Date:</span> <?= htmlspecialchars($booking['booking_date']) ?></p>
        </div>

        <div class="footer">
            <p><i>Thank you for choosing StreetWave Event Planner!</i></p>
			    <p>
        <a href="booking_summary.php" style="
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        ">← Back to Booking Summary</a>
        </div>
    </div>
</body>
</html>

