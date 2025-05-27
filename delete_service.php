<?php
session_start();
require_once 'config/db_connect.php';
require_once 'functions.php';

if (!isAdmin()) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit;
}

$service_id = (int)$_GET['id'];

// Get photo path to delete the file
$stmt = $pdo->prepare("SELECT photo FROM services WHERE service_id = ?");
$stmt->execute([$service_id]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if ($service) {
    // Delete photo file if exists
    if (!empty($service['photo']) && file_exists($service['photo'])) {
        unlink($service['photo']);
    }

    // Delete service record
    $stmt = $pdo->prepare("DELETE FROM services WHERE service_id = ?");
    $stmt->execute([$service_id]);
}

header("Location: admin_dashboard.php?success=service_deleted");
exit;
