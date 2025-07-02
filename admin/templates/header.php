<?php
// Correctly navigate two directories up to find the core folder
require_once __DIR__ . '/../../core/init.php';

// Security check: Must be a logged-in Admin to access any of these pages
if (!isset($_SESSION['user_id']) || $_SESSION['role_name'] !== 'Admin') {
    header("Location: ../auth/login.php"); // Redirect to login if not authorized
    exit();
}

// Get admin data for display
$admin_name = $_SESSION['user_fname'] ?? 'Admin';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin: <?php echo ucfirst(str_replace(['admin_', '.php'], '', $current_page)); ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="icon" href="../assets/icons/dueday.png" type="image/x-icon">
</head>
<body>
    <div class="admin-container">
        <div class="admin-sidebar">
            <div class="admin-logo"><span>DUEDAY ADMIN</span></div>
            <ul class="admin-nav-menu">
                <li class="<?php if ($current_page == 'admin_dashboard.php') echo 'active'; ?>">
                    <a href="admin_dashboard.php">Dashboard</a>
                </li>
                <li class="<?php if ($current_page == 'admin_users.php' || $current_page == 'edit_user.php') echo 'active'; ?>">
                    <a href="admin_users.php">Users</a>
                </li>
                <li class="<?php if ($current_page == 'admin_venues.php') echo 'active'; ?>">
                    <a href="admin_venues.php">Venues</a>
                </li>
                <li class="<?php if ($current_page == 'admin_classes.php') echo 'active'; ?>">
                    <a href="admin_classes.php">Classes</a>
                </li>
                <li class="<?php if ($current_page == 'admin_announcements.php') echo 'active'; ?>">
                    <a href="admin_announcements.php">Announcements</a>
                </li>
                <li><a href="../home.php" target="_blank">View Main Site</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </div>
        <div class="admin-main-content">
            <header class="admin-header">
                <div class="profile-info">Welcome, <?php echo htmlspecialchars($admin_name); ?></div>
            </header>