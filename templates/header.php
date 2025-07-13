<?php
date_default_timezone_set('Africa/Nairobi');
require_once __DIR__ . '/../core/init.php';

$base_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace('\\', '/', dirname(__DIR__)));

if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_path . "/auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
verify_user_is_active($conn, $user_id);

$stmt_header = $conn->prepare("SELECT u.F_Name, r.Role_Name, u.Profile_Picture_Path FROM Users u JOIN Role r ON u.Role_ID = r.Role_ID WHERE u.User_ID = ?");
$stmt_header->bind_param("i", $user_id);
$stmt_header->execute();
$header_user_data = $stmt_header->get_result()->fetch_assoc();
$stmt_header->close();

$user_fname = $header_user_data['F_Name'];
$user_role = $header_user_data['Role_Name'];
$user_pfp_path = $header_user_data['Profile_Picture_Path'];

$profile_icon_src = !empty($user_pfp_path) && file_exists(__DIR__ . '/../' . $user_pfp_path) 
    ? $base_path . '/' . $user_pfp_path 
    : $base_path . '/assets/icons/admin.png';

$active_page = basename($_SERVER['PHP_SELF']);

$current_hour = (int)date('H');
$time_based_greeting = "Welcome back,";
if ($current_hour >= 5 && $current_hour < 12) {
    $time_based_greeting = "Good Morning,";
} elseif ($current_hour >= 12 && $current_hour < 17) {
    $time_based_greeting = "Good Afternoon,";
} else {
    $time_based_greeting = "Good Evening,";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo $base_path; ?>/assets/css/style.css">
    <link rel="icon" href="<?php echo $base_path; ?>/assets/icons/dueday.png" type="image/x-icon">
    <title><?php echo ucfirst(str_replace('.php', '', $active_page)); ?> - DueDay</title>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="logo">
                <img src="<?php echo $base_path; ?>/assets/icons/dueday.png" alt="DueDay Logo" class="logo-icon">
                <span>DUEDAY</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-item <?php if ($active_page == 'home.php') echo 'active'; ?>"><a href="<?php echo $base_path; ?>/home.php"><img src="<?php echo $base_path; ?>/assets/icons/home.png" class="nav-icon"><span>Home</span></a></li>
                <li class="nav-item <?php if ($active_page == 'assignment.php') echo 'active'; ?>"><a href="<?php echo $base_path; ?>/assignment.php"><img src="<?php echo $base_path; ?>/assets/icons/assignment.png" class="nav-icon"><span>Assignments</span></a></li>
                <li class="nav-item <?php if ($active_page == 'poll.php') echo 'active'; ?>"><a href="<?php echo $base_path; ?>/poll.php"><img src="<?php echo $base_path; ?>/assets/icons/poll.png" class="nav-icon"><span>Polls</span></a></li>
                <li class="nav-item <?php if ($active_page == 'event.php') echo 'active'; ?>"><a href="<?php echo $base_path; ?>/event.php"><img src="<?php echo $base_path; ?>/assets/icons/event.png" class="nav-icon"><span>Events</span></a></li>
                <li class="nav-item <?php if ($active_page == 'timetable.php') echo 'active'; ?>"><a href="<?php echo $base_path; ?>/timetable.php"><img src="<?php echo $base_path; ?>/assets/icons/table.png" class="nav-icon"><span>Timetable</span></a></li>
            </ul>
        </div>
        <div class="main-content">
            <header class="welcome-header">
                <div class="greeting">
                    <h1><?php echo $time_based_greeting . " " . htmlspecialchars($user_fname); ?>!</h1>
                    <p>Role: <?php echo htmlspecialchars($user_role); ?></p>
                </div>
                <div class="profile-section">
                     <a href="<?php echo $base_path; ?>/profile.php" title="Profile & Settings">
                        <img src="<?php echo $profile_icon_src; ?>" alt="Profile" class="profile-icon">
                    </a>
                </div>
            </header>