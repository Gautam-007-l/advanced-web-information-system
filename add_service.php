<?php
session_start();
require_once 'config/db_connect.php';
require_once 'functions.php';

if (!isAdmin()) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $photoPath = '';

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $tmpName = $_FILES['photo']['tmp_name'];
        $originalName = basename($_FILES['photo']['name']);
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $uniqueName = uniqid('svc_', true) . '.' . $extension;
        $photoPath = $uploadDir . $uniqueName;

        move_uploaded_file($tmpName, $photoPath);
    }

    $stmt = $pdo->prepare("INSERT INTO services (title, description, price, photo, created_by, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$title, $description, $price, $photoPath, $_SESSION['user_id']]);

    header("Location: admin_dashboard.php?success=service_added");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head><title>Add Service</title></head>
<body>
    <h2>Add Service</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>Title: <input type="text" name="title" required></label><br>
        <label>Description: <textarea name="description" required></textarea></label><br>
        <label>Price: <input type="number" step="0.01" name="price" required></label><br>
        <label>Photo: <input type="file" name="photo" accept="image/*"></label><br>
        <button type="submit">Add Service</button>
    </form>
    <p><a href="admin_dashboard.php">Back to Dashboard</a></p>
</body>
</html>
