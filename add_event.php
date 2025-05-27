<?php
session_start();
require_once 'config/db_connect.php';
require_once 'functions.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
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

    $photo_path = null;
    if ($photo && $photo['name']) {
        $upload_result = uploadPhoto($photo);
        if ($upload_result['success']) {
            $photo_path = $upload_result['path'];
        } else {
            echo "Photo upload failed: " . $upload_result['error'];
        }
    }

    $stmt = $pdo->prepare("INSERT INTO events (user_id, category_id, title, description, event_date, location, price, photo) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$_SESSION['user_id'], $category_id, $title, $description, $event_date, $location, $price, $photo_path])) {
        header("Location: admin_dashboard.php");
        exit;
    } else {
        echo "Error adding event.";
    }
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Event</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="centered-content">
        <h1>Add New Event</h1>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Title" required>
            <textarea name="description" placeholder="Description" required></textarea>
            <input type="datetime-local" name="event_date" required>
            <input type="text" name="location" placeholder="Location" required>
            <input type="number" name="price" step="0.01" placeholder="Price" required>
            <select name="category_id" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="file" name="photo" accept="image/jpeg,image/png">
            <button type="submit">Add Event</button>
        </form>
        <a href="admin_dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>