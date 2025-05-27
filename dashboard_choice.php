<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Not logged in or not admin — redirect to homepage or login
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Choose Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="centered-content">
        <h1>Choose Dashboard View</h1>
        <p>Welcome, Admin! Please select which dashboard you'd like to view:</p>
		<p> 
		</p>

        <form action="admin_dashboard.php" method="get" style="display:inline;">
            <button type="submit">Admin Dashboard</button>
        </form>

        <form action="user_dashboard.php" method="get" style="display:inline; margin-left: 20px;">
            <button type="submit">User Dashboard</button>
        </form>

        <p><a href="logout.php">Logout</a></p>
    </div>
</body>
</html>
