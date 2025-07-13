<?php
// This script handles reverting an impersonation session back to the original admin.
require_once __DIR__ . '/../core/init.php';

// Security Check 1: Ensure a session exists.
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Security Check 2: Ensure we are actually in an impersonation session.
// The 'admin_id' is our key. If it doesn't exist, this page shouldn't do anything.
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../home.php?error=not_impersonating");
    exit();
}

// --- CORE REVERT LOGIC ---
$admin_id = $_SESSION['admin_id'];

// Fetch the original admin's data.
$stmt = $conn->prepare("SELECT User_ID, F_Name, Role_ID FROM Users WHERE User_ID = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin_user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($admin_user) {
    // 1. Restore the original admin's details to the main session variables.
    $_SESSION['user_id'] = $admin_user['User_ID'];
    $_SESSION['user_fname'] = $admin_user['F_Name'];

    $role_stmt = $conn->prepare("SELECT Role_Name FROM Role WHERE Role_ID = ?");
    $role_stmt->bind_param("i", $admin_user['Role_ID']);
    $role_stmt->execute();
    $_SESSION['role_name'] = $role_stmt->get_result()->fetch_assoc()['Role_Name'];
    $role_stmt->close();

    // 2. Unset the 'admin_id' session variable. This officially ends the impersonation.
    unset($_SESSION['admin_id']);
    
    // 3. Redirect back to the admin dashboard.
    header("Location: admin_dashboard.php");
    exit();
} else {
    // Failsafe: If the admin account was deleted during impersonation, log out completely.
    session_unset();
    session_destroy();
    header("Location: ../auth/login.php?error=admin_not_found");
    exit();
}
?>