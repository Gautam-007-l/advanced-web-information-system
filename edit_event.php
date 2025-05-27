<?php
session_start();
require_once 'config/db_connect.php';
require_once 'functions.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit;
}

$event_id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    header("Location: admin_dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $event_date = sanitizeInput($_POST['event_date']);
    $location = sanitizeInput($_POST['location']);
    $price = sanitizeInput($_POST['price']);
    $category_id = (int)$_POST['category_id'];
    $photo = $_FILES['photo'] ?? null;

    $photo_path = $event['photo'];
    if ($photo && $photo['name']) {
        $upload_result = uploadPhoto($photo);
        if ($upload_result['success']) {
            $photo_path = $upload_result['path'];
            // Optionally delete old photo if it exists
            if ($event['photo'] && file_exists($event['photo'])) {
                unlink($event['photo']);
            }
        } else {
            echo "Photo upload failed: " . $upload_result['error'];
        }
    }

    $stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, location = ?, price = ?, category_id = ?, photo = ? WHERE event_id = ?");
    if ($stmt->execute([$title, $description, $event_date, $location, $price, $category_id, $photo_path, $event_id])) {
        header("Location: admin_dashboard.php");
        exit;
    } else {
        echo "Error updating event.";
    }
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Event</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="centered-content">
        <h1>Edit Event</h1>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="title" value="<?php echo htmlspecialchars($event['title']); ?>" required>
            <textarea name="description" required><?php echo htmlspecialchars($event['description']); ?></textarea>
            <input type="datetime-local" name="event_date" value="<?php echo $event['event_date']; ?>" required>
            <input type="text" name="location" value="<?php echo htmlspecialchars($event['location']); ?>" required>
            <input type="number" name="price" step="0.01" value="<?php echo $event['price']; ?>" required>
            <select name="category_id" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['category_id']; ?>" <?php echo $category['category_id'] == $event['category_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['category_name']); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="file" name="photo" accept="image/jpeg,image/png">
            <?php if ($event['photo']): ?>
                <p>Current Photo: <img src="<?php echo htmlspecialchars($event['photo']); ?>" class="event-image-large"></p>
            <?php endif; ?>
            <button type="submit">Update Event</button>
        </form>
        <a href="admin_dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>