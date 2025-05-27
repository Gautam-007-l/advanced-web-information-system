<?php
require_once 'config/db_connect.php'; // For database access if needed

// Main function to send notifications
function sendNotification($user_id, $type, $subject, $message) {
    global $pdo;

    // Fetch user email
    $stmt = $pdo->prepare("SELECT email FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        logNotificationError("User not found for user_id: $user_id");
        return false;
    }

    $to = $user['email'];
    $success = false;

    switch ($type) {
        case 'email':
            $success = sendEmail($to, $subject, $message);
            break;
        // Add other notification types in the future (e.g., SMS, push)
        default:
            logNotificationError("Invalid notification type: $type");
            return false;
    }

    if ($success) {
        logNotificationSuccess("Notification sent to $to: $subject");
    } else {
        logNotificationError("Failed to send notification to $to: $subject");
    }

    return $success;
}

// Email sending function (same as in functions.php, but moved here for centralization)
function sendEmail($to, $subject, $body) {
    // Replace this with actual email sending logic (e.g., using PHPMailer)
    // For now, we'll simulate success and log the attempt
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: no-reply@streetwave.com\r\n";

    // Simulate email sending (replace with actual mail() or PHPMailer in production)
    // For demo purposes, we'll assume it succeeds if the email format is valid
    if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
        // Uncomment the line below to use actual mail() function in a real setup
        // return mail($to, $subject, $body, $headers);
        return true; // Simulated success
    }

    return false; // Simulated failure if email is invalid
}

// Log successful notifications (for debugging)
function logNotificationSuccess($message) {
    $log_message = "[" . date('Y-m-d H:i:s') . "] SUCCESS: $message\n";
    file_put_contents('logs/notifications.log', $log_message, FILE_APPEND);
}

// Log notification errors (for debugging)
function logNotificationError($message) {
    $log_message = "[" . date('Y-m-d H:i:s') . "] ERROR: $message\n";
    file_put_contents('logs/notifications.log', $log_message, FILE_APPEND);
}
?>