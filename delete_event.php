<?php
session_start();
require_once 'config/db_connect.php';
require_once 'functions.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit;
}

$event_id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT photo FROM events WHERE event_id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if ($event && $event['photo'] && file_exists($event['photo'])) {
    unlink($event['photo']);
}

$stmt = $pdo->prepare("DELETE FROM events WHERE event_id = ?");
if ($stmt->execute([$event_id])) {
    header("Location: admin_dashboard.php");
    exit;
} else {
    echo "Error deleting event.";
}
?>