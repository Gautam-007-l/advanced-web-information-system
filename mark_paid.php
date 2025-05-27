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

// Update booking status to "paid"
$stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'paid' WHERE booking_id = ?");
$stmt->execute([$booking_id]);

// Redirect to invoice page
header("Location: invoice.php?booking_id=$booking_id");
exit;
