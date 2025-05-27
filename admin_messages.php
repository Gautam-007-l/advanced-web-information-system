<?php
session_start();
require_once 'config/db_connect.php';
require_once 'functions.php';

if (!isAdmin()) {
    header("Location: login.php");
    exit;
}

$messages = $pdo->query("SELECT cm.*, u.username FROM contact_messages cm 
                         LEFT JOIN users u ON cm.user_id = u.user_id 
                         ORDER BY cm.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Contact Messages</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h1>Contact Messages</h1>
    <table>
        <tr>
            <th>User</th>
            <th>Name</th>
            <th>Email</th>
            <th>Message</th>
            <th>Submitted</th>
        </tr>
        <?php foreach ($messages as $msg): ?>
        <tr>
            <td><?php echo htmlspecialchars($msg['username'] ?? 'Guest'); ?></td>
            <td><?php echo htmlspecialchars($msg['name']); ?></td>
            <td><?php echo htmlspecialchars($msg['email']); ?></td>
            <td><?php echo nl2br(htmlspecialchars($msg['message'])); ?></td>
            <td><?php echo $msg['created_at']; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
