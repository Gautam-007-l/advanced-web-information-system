<?php
session_start();
require_once 'config/db_connect.php';
require_once 'functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - StreetWave</title>
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
        <h1>About Us</h1>
        <div class="centered-content">
            <p>StreetWave is dedicated to making your events unforgettable. We specialize in planning social, professional, and community events with a focus on creativity and attention to detail.</p>
            <p>Our team is passionate about bringing people together through meaningful experiences. Contact us today to start planning your next event!</p>
        </div>
    </section>

    <footer>
        <p>© Group2/BIT • BITConnect • <a href="#">X</a> • <a href="#">Instagram</a> • <a href="#">YouTube</a> • 2025, Students</p>
    </footer>
</body>
</html>