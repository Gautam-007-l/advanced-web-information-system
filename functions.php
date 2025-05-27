<?php
require_once 'config/db_connect.php';

// Sanitize input to prevent injection
function sanitizeInput($data) {
    return htmlspecialchars(trim($data));
}

// Generate a random token for payment or verification
function generateToken() {
    return bin2hex(random_bytes(16));
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is an admin
function isAdmin() {
    global $pdo;
    if (!isLoggedIn()) return false;
    $stmt = $pdo->prepare("SELECT role FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user['role'] === 'admin';
}

// Upload photo for events
function uploadPhoto($file) {
    $target_dir = "photos/";
    $target_file = $target_dir . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png'];

    if (!in_array($imageFileType, $allowed_types)) {
        return ['success' => false, 'error' => 'Invalid file type. Only JPG, JPEG, PNG allowed.'];
    }

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['success' => true, 'path' => $target_file];
    } else {
        return ['success' => false, 'error' => 'Error uploading file.'];
    }
}

// Calculate total price of bookings for a user
function calculateTotalPrice($user_id, $status = null) {
    global $pdo;
    
    $query = "SELECT SUM(e.price) as total_price 
              FROM bookings b 
              JOIN events e ON b.event_id = e.event_id 
              WHERE b.user_id = ?";
    
    $params = [$user_id];
    
    if ($status) {
        $query .= " AND b.booking_status = ?";
        $params[] = $status;
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['total_price'] ?? 0.00;
}
?>