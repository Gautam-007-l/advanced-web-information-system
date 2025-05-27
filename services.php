<?php
session_start();
require_once 'config/db_connect.php';
require_once 'functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT role FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$role = $user['role'] ?? 'user';

$message = '';

// Admin can add a new service
if ($role === 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/services/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = basename($_FILES['photo']['name']);
        // Clean filename and prepend timestamp to avoid conflicts
        $safeFilename = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $filename);
        $targetFile = $uploadDir . $safeFilename;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
            $stmt = $pdo->prepare("INSERT INTO services (title, description, photo) VALUES (?, ?, ?)");
            if ($stmt->execute([$title, $description, $targetFile])) {
                $message = "Service added successfully.";
            } else {
                $message = "Database error: could not save service.";
                unlink($targetFile); // Delete uploaded file on DB failure
            }
        } else {
            $message = "Failed to upload photo.";
        }
    } else {
        $message = "Please upload a valid photo.";
    }
}

// Fetch all services for display
$services = $pdo->query("SELECT * FROM services ORDER BY service_id DESC")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Services - <?= htmlspecialchars(ucfirst($role)) ?></title>
    <link rel="stylesheet" href="css/styles.css" />
</head>
<body>
    <header>
        <div class="logo">Event Planner</div>
        <nav>
            <a href="index.php">Home</a>
            <a href="services.php">Services</a>
            <?php if ($role === 'admin'): ?>
                <a href="admin_dashboard.php">Admin Dashboard</a>
            <?php endif; ?>
            <a href="user_dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main class="centered-content">
        <h1>Our Services</h1>

        <?php if ($message): ?>
            <p style="color: green;"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <?php if ($role === 'admin'): ?>
            <section>
                <h2>Add New Service</h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <input type="text" name="title" placeholder="Service Title" required><br><br>
                    <textarea name="description" placeholder="Service Description" required rows="4" cols="50"></textarea><br><br>
                    <input type="file" name="photo" accept="image/*" required><br><br>
                    <button type="submit">Add Service</button>
                </form>
            </section>
            <hr>
        <?php endif; ?>

        <section>
            <?php if ($services): ?>
                <ul>
                    <?php foreach ($services as $service): ?>
                        <li>
                            <h3><?= htmlspecialchars($service['title']) ?></h3>
                            <p><?= nl2br(htmlspecialchars($service['description'])) ?></p>
                            <?php if (!empty($service['photo']) && file_exists($service['photo'])): ?>
                                <img src="<?= htmlspecialchars($service['photo']) ?>" alt="<?= htmlspecialchars($service['title']) ?>" style="max-width:200px;">
                            <?php endif; ?>

                            <?php if ($role === 'admin'): ?>
                                <!-- You can add edit/delete links/buttons here -->
                                <form method="POST" action="delete_service.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this service?');">
                                    <input type="hidden" name="service_id" value="<?= (int)$service['service_id'] ?>">
                                    <button type="submit">Delete</button>
                                </form>
                                <!-- Edit could be another page or modal -->
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No services available at the moment.</p>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>© Group2/BIT • BITConnect • 2025, Students</p>
    </footer>
</body>
</html>
