<?php
session_start();
require_once 'config/db_connect.php';
require_once 'functions.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit;
}

// Fetch data
$events = $pdo->query("SELECT e.*, c.category_name, u.username FROM events e 
                       JOIN categories c ON e.category_id = c.category_id 
                       JOIN users u ON e.user_id = u.user_id 
                       ORDER BY e.event_date DESC")->fetchAll(PDO::FETCH_ASSOC);

$users = $pdo->query("SELECT user_id, username, email, role, email_verified FROM users")->fetchAll(PDO::FETCH_ASSOC);

$bookings = $pdo->query("SELECT b.*, e.title, u.username FROM bookings b 
                         JOIN events e ON b.event_id = e.event_id 
                         JOIN users u ON b.user_id = u.user_id")->fetchAll(PDO::FETCH_ASSOC);

$services = $pdo->query("SELECT s.*, u.username FROM services s 
                         JOIN users u ON s.created_by = u.user_id 
                         ORDER BY s.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Statistics
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_bookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();

$booking_stats = $pdo->query("SELECT booking_status, COUNT(*) as count 
                              FROM bookings 
                              GROUP BY booking_status")->fetchAll(PDO::FETCH_ASSOC);

$stats_data = ['pending' => 0, 'confirmed' => 0, 'paid' => 0, 'cancelled' => 0];
foreach ($booking_stats as $stat) {
    $stats_data[$stat['booking_status']] = $stat['count'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="centered-content">
        <h1>Admin Dashboard</h1>
        <a href="logout.php">Logout</a> | 
        <a href="add_event.php">Add New Event</a> |
        <a href="add_service.php">Add New Service</a>

        <!-- Statistics Section -->
        <h2>Statistics</h2>
        <p>Total Users: <?php echo $total_users; ?></p>
        <p>Total Bookings: <?php echo $total_bookings; ?></p>
        <h3>Bookings by Status</h3>
        <canvas id="bookingChart" width="400" height="200"></canvas>
        <script>
            const ctx = document.getElementById('bookingChart').getContext('2d');
            const bookingChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Pending', 'Confirmed', 'Paid', 'Cancelled'],
                    datasets: [{
                        label: 'Number of Bookings',
                        data: [
                            <?php echo $stats_data['pending']; ?>,
                            <?php echo $stats_data['confirmed']; ?>,
                            <?php echo $stats_data['paid']; ?>,
                            <?php echo $stats_data['cancelled']; ?>
                        ],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(255, 206, 86, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(255, 206, 86, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        </script>

        <!-- Manage Events -->
        <h2>Manage Events</h2>
        <table border="1">
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Creator</th>
                <th>Date</th>
                <th>Price</th>
                <th>Photo</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($events as $event): ?>
                <tr>
                    <td><?php echo htmlspecialchars($event['title']); ?></td>
                    <td><?php echo htmlspecialchars($event['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($event['username']); ?></td>
                    <td><?php echo $event['event_date']; ?></td>
                    <td>$<?php echo number_format($event['price'], 2); ?></td>
                    <td><?php echo $event['photo'] ? '<img src="' . htmlspecialchars($event['photo']) . '" width="50">' : 'No photo'; ?></td>
                    <td>
                        <a href="edit_event.php?id=<?php echo $event['event_id']; ?>">Edit</a> |
                        <a href="delete_event.php?id=<?php echo $event['event_id']; ?>" onclick="return confirm('Are you sure?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <!-- Manage Users -->
        <h2>Manage Users</h2>
        <table border="1">
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Verified</th>
            </tr>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td><?php echo $user['email_verified'] ? 'Yes' : 'No'; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <!-- Manage Bookings -->
        <h2>Manage Bookings</h2>
        <table border="1">
            <tr>
                <th>Event</th>
                <th>User</th>
                <th>Status</th>
                <th>Payment Token</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><?php echo htmlspecialchars($booking['title']); ?></td>
                    <td><?php echo htmlspecialchars($booking['username']); ?></td>
                    <td><?php echo htmlspecialchars($booking['booking_status']); ?></td>
                    <td><?php echo htmlspecialchars($booking['payment_token'] ?? 'N/A'); ?></td>
                    <td>
                        <a href="mark_paid.php?booking_id=<?php echo $booking['booking_id']; ?>" onclick="return confirm('Mark as paid?');">Mark Paid</a> |
                        <a href="update_booking.php?booking_id=<?php echo $booking['booking_id']; ?>&status=cancelled">Cancel</a> |
                        <a href="reschedule_event.php?event_id=<?php echo $booking['event_id']; ?>">Reschedule</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <!-- Manage Services -->
        <h2>Manage Services</h2>
		  <p><a href="add_service.php">Add New Service</a> | <a href="logout.php">Logout</a></p>
        <table border="1">
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Price</th>
                <th>Photo</th>
                <th>Created By</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($services as $service): ?>
                <tr>
                    <td><?php echo htmlspecialchars($service['title']); ?></td>
                    <td><?php echo htmlspecialchars($service['description']); ?></td>
                    <td>$<?php echo number_format($service['price'], 2); ?></td>
                    <td>
                        <?php if ($service['photo']): ?>
                            <img src="<?php echo htmlspecialchars($service['photo']); ?>" width="50">
                        <?php else: ?>
                            No photo
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($service['username']); ?></td>
                    <td>
                        <a href="edit_service.php?id=<?php echo $service['service_id']; ?>">Edit</a> |
                        <a href="delete_service.php?id=<?php echo $service['service_id']; ?>" onclick="return confirm('Are you sure?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
