<?php
session_start();
require_once 'config/db_connect.php';
require_once 'functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Upload venue photos (admin only)
$uploadMessages = [];
$uploadDir = 'uploads/';
if (isset($_POST['upload_photos']) && isset($_FILES['photos'])) {
    foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
        $name = basename($_FILES['photos']['name'][$key]);
        $targetPath = $uploadDir . $name;

        if (move_uploaded_file($tmp_name, $targetPath)) {
            $uploadMessages[] = "Uploaded: $name";
        } else {
            $uploadMessages[] = "Failed to upload: $name";
        }
    }
}

// Delete photo (admin only)
if (isset($_POST['delete_photo']) && $_SESSION['role'] === 'admin') {
    $photoToDelete = basename($_POST['photo_name']);
    $pathToDelete = $uploadDir . $photoToDelete;
    if (file_exists($pathToDelete)) {
        unlink($pathToDelete);
    }
}

// Load uploaded venue photos
$uploadedPhotos = array_values(array_filter(scandir($uploadDir), function ($file) use ($uploadDir) {
    return in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']) && is_file($uploadDir . $file);
}));

// Load events by category
$social_events = $pdo->query("SELECT e.* FROM events e JOIN categories c ON e.category_id = c.category_id WHERE c.category_name = 'Social' LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
$professional_events = $pdo->query("SELECT e.* FROM events e JOIN categories c ON e.category_id = c.category_id WHERE c.category_name = 'Professional' LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
$community_events = $pdo->query("SELECT e.* FROM events e JOIN categories c ON e.category_id = c.category_id WHERE c.category_name = 'Community' LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>StreetWave - Event Planner</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/slideshow.css">
</head>
<body>
    <header>
        <div class="logo">Event Planner</div>
        <nav>
            <a href="index.php">Home</a>
            <a href="services.php">Services</a>
            <a href="booking_cart.php">Event Booking Cart</a>
            <a href="booking_summary.php">Booking Summary</a>
            <a href="user_dashboard.php">Dashboard</a>
            <a href="contact.php">Contact Us</a>
            <?php if (isLoggedIn()): ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
            <a href="about.php">About Us</a>
        </nav>
    </header>

    <section class="welcome">
        <h1>Welcome to StreetWave</h1>

        <!-- Slideshow -->
        <div class="slideshow-container">
            <?php foreach ($uploadedPhotos as $photo): ?>
                <div class="slide">
                    <img src="uploads/<?php echo htmlspecialchars($photo); ?>" alt="Venue Photo">
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <form method="POST" class="delete-form">
                            <input type="hidden" name="photo_name" value="<?php echo htmlspecialchars($photo); ?>">
                            <button class="delete-button" type="submit" name="delete_photo">Delete</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Admin upload form -->
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <form method="POST" enctype="multipart/form-data" class="upload-form">
                <label><strong>Upload Venue Photos:</strong></label><br>
                <input type="file" name="photos[]" multiple accept="image/*" required>
                <button type="submit" name="upload_photos">Upload</button>
            </form>
            <?php foreach ($uploadMessages as $msg): ?>
                <p><?php echo htmlspecialchars($msg); ?></p>
            <?php endforeach; ?>
        <?php endif; ?>

        <p>StreetWave is your ultimate partner in planning unforgettable events. Whether it’s a social gathering, a professional event, or a community celebration, we specialize in creating moments that last.</p>
    </section>

    <!-- Featured Events (unchanged) -->
    <section class="featured-events">
        <h2>Featured Events</h2>
        <div class="event-category">
            <h3>Social Events</h3>
            <a href="social_events.php">Explore Social Events</a>
            <div class="event-list">
                <?php foreach ($social_events as $event): ?>
                    <div class="event-item">
                        <?php if ($event['photo']): ?>
                            <img src="<?php echo htmlspecialchars($event['photo']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>">
                        <?php endif; ?>
                        <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                        <p><?php echo htmlspecialchars($event['event_date']); ?></p>
                        <p>$<?php echo number_format($event['price'], 2); ?></p>
                        <a href="book_event.php?event_id=<?php echo $event['event_id']; ?>">Book Now</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
   <!-- Professional -->
    <div class="event-category">
        <h3>Professional Events</h3>
        <div class="event-list">
            <?php foreach ($professional_events as $event): ?>
                <div class="event-item">
                    <?php if ($event['photo']): ?>
                        <img src="<?php echo htmlspecialchars($event['photo']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>">
                    <?php endif; ?>
                    <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                    <p><?php echo htmlspecialchars($event['event_date']); ?></p>
                    <p>$<?php echo number_format($event['price'], 2); ?></p>
                    <a href="book_event.php?event_id=<?php echo $event['event_id']; ?>">Book Now</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Community -->
    <div class="event-category">
        <h3>Community Events</h3>
        <div class="event-list">
            <?php foreach ($community_events as $event): ?>
                <div class="event-item">
                    <?php if ($event['photo']): ?>
                        <img src="<?php echo htmlspecialchars($event['photo']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>">
                    <?php endif; ?>
                    <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                    <p><?php echo htmlspecialchars($event['event_date']); ?></p>
                    <p>$<?php echo number_format($event['price'], 2); ?></p>
                    <a href="book_event.php?event_id=<?php echo $event['event_id']; ?>">Book Now</a>
                </div>
            <?php endforeach; ?>
        </div>
	</div>
    </section>

    <footer>
        <p>© Group2/BIT • BITConnect • <a href="#">X</a> • <a href="#">Instagram</a> • <a href="#">YouTube</a> • 2025, Students</p>
    </footer>

    <script>
        let slideIndex = 0;
        function showSlides() {
            const slides = document.querySelectorAll(".slide");
            slides.forEach(slide => slide.classList.remove("active-slide"));
            if (slides.length > 0) {
                slideIndex = (slideIndex + 1) % slides.length;
                slides[slideIndex].classList.add("active-slide");
            }
            setTimeout(showSlides, 3000);
        }
        window.addEventListener("DOMContentLoaded", showSlides);
    </script>
</body>
</html>
