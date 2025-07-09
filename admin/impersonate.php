<?php
// This script handles the logic for an admin to start impersonating a user.
require_once __DIR__ . '/../core/init.php';

// Security Check 1: Ensure the person attempting this is logged in.
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Security Check 2: Ensure the person is an Admin.
if ($_SESSION['role_name'] !== 'Admin') {
    header("Location: ../home.php?error=unauthorized");
    exit();
}

// Security Check 3: Ensure a target user ID was provided.
$target_user_id = $_GET['id'] ?? null;
if (!$target_user_id) {
    header("Location: admin_users.php?error=no_user_specified");
    exit();
}

// Security Check 4: Admin cannot impersonate themselves.
if ($target_user_id == $_SESSION['user_id']) {
    header("Location: admin_users.php?error=self_impersonation");
    exit();
}

// Fetch the target user's data from the database.
$stmt = $conn->prepare("SELECT User_ID, F_Name, Role_ID FROM Users WHERE User_ID = ?");
$stmt->bind_param("i", $target_user_id);
$stmt->execute();
$target_user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Security Check 5: Ensure the target user actually exists.
if (!$target_user) {
    header("Location: admin_users.php?error=user_not_found");
    exit();
}

// --- CORE IMPERSONATION LOGIC ---

// 1. Save the original admin's ID in a separate session variable.
// This is our "return ticket" back to the admin account.
$_SESSION['admin_id'] = $_SESSION['user_id'];

// 2. Overwrite the current session with the target user's details.
$_SESSION['user_id'] = $target_user['User_ID'];
$_SESSION['user_fname'] = $target_user['F_Name'];

// We need the role name, so we fetch it.
$role_stmt = $conn->prepare("SELECT Role_Name FROM Role WHERE Role_ID = ?");
$role_stmt->bind_param("i", $target_user['Role_ID']);
$role_stmt->execute();
$_SESSION['role_name'] = $role_stmt->get_result()->fetch_assoc()['Role_Name'];
$role_stmt->close();

// 3. Redirect to the user's dashboard.
header("Location: ../home.php");
exit();
?>