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

// Fetch existing service data
$stmt = $pdo->prepare("SELECT * FROM services WHERE service_id = ?");
$stmt->execute([$service_id]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    die("Service not found.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $photoPath = $service['photo']; // keep old photo by default

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $tmpName = $_FILES['photo']['tmp_name'];
        $originalName = basename($_FILES['photo']['name']);
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $uniqueName = uniqid('svc_', true) . '.' . $extension;
        $newPhotoPath = $uploadDir . $uniqueName;

        if (move_uploaded_file($tmpName, $newPhotoPath)) {
            // Delete old photo file if exists
            if (!empty($photoPath) && file_exists($photoPath)) {
                unlink($photoPath);
            }
            $photoPath = $newPhotoPath;
        }
    }

    $stmt = $pdo->prepare("UPDATE services SET title = ?, description = ?, price = ?, photo = ? WHERE service_id = ?");
    $stmt->execute([$title, $description, $price, $photoPath, $service_id]);

    header("Location: admin_dashboard.php?success=service_updated");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head><title>Edit Service</title></head>
<body>
    <h2>Edit Service</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>Title: <input type="text" name="title" value="<?php echo htmlspecialchars($service['title']); ?>" required></label><br>
        <label>Description: <textarea name="description" required><?php echo htmlspecialchars($service['description']); ?></textarea></label><br>
        <label>Price: <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($service['price']); ?>" required></label><br>
        <label>Photo: <input type="file" name="photo" accept="image/*"></label><br>
        <?php if (!empty($service['photo'])): ?>
            <img src="<?php echo htmlspecialchars($service['photo']); ?>" alt="Photo" width="100"><br>
        <?php endif; ?>
        <button type="submit">Update Service</button>
    </form>
    <p><a href="admin_dashboard.php">Back to Dashboard</a></p>
</body>
</html>
