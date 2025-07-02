<?php
// --- LOGIC ---
require_once 'templates/header.php'; // Includes connection, session, security, and sidebar

// Fetch stats
$user_count = $conn->query("SELECT COUNT(*) as count FROM Users")->fetch_assoc()['count'];
$venue_count = $conn->query("SELECT COUNT(*) as count FROM Venues")->fetch_assoc()['count'];
$class_count = $conn->query("SELECT COUNT(*) as count FROM Classes")->fetch_assoc()['count'];
$announcement_count = $conn->query("SELECT COUNT(*) as count FROM Announcements")->fetch_assoc()['count'];
$conn->close();

// --- PRESENTATION ---
?>
<head>
    <title>Admin Dashboard</title>
</head>

<h1 class="page-title">Dashboard</h1>
<div class="stat-card-container">
    <div class="stat-card"><h3>Total Users</h3><p><?php echo $user_count; ?></p></div>
    <div class="stat-card"><h3>Total Venues</h3><p><?php echo $venue_count; ?></p></div>
    <div class="stat-card"><h3>Total Classes</h3><p><?php echo $class_count; ?></p></div>
    <div class="stat-card"><h3>Announcements</h3><p><?php echo $announcement_count; ?></p></div>
</div>

<?php require_once 'templates/footer.php'; ?>