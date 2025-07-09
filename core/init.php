<?php
// CORE INITIALIZATION SCRIPT

// --- UPDATED: Professional Error Handling for Production ---
error_reporting(E_ALL);
// For a live server, 'display_errors' should be 'Off'. For development, 'On' is fine.
ini_set('display_errors', 'On'); 
// Log errors to a file instead of showing them to users on a live server.
ini_set('log_errors', 'On'); 
// --- FIX: Define the path to your error log file inside the gitignore folder ---
ini_set('error_log', __DIR__ . '/../.gitignore/error.log');
// --- END: UPDATED ERROR HANDLING ---


// --- 1. DATABASE CONNECTION ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "DueDay";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    // We log the detailed error and show a generic message to the user.
    error_log("Database connection failed: " . $conn->connect_error);
    die("A connection error occurred. Please try again later.");
}

// --- 2. SESSION MANAGEMENT ---
session_start();

// --- 3. GLOBAL HELPER FUNCTIONS ---

/**
 * --- NEW: Centralized Active User Check ---
 * Checks if the logged-in user is still active. Kills session if not.
 * @param mysqli $db_connection The database connection object.
 * @param int $user_id The ID of the logged-in user.
 */
function verify_user_is_active(mysqli $db_connection, int $user_id) {
    $stmt = $db_connection->prepare("SELECT status FROM Users WHERE User_ID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $status = $stmt->get_result()->fetch_assoc()['status'] ?? 'inactive';
    $stmt->close();

    if ($status !== 'active') {
        // Log the user out completely
        session_unset();
        session_destroy();
        // Redirect to login page with a message
        header("Location: /dueday/auth/login.php?deactivated=true");
        exit();
    }
}

/**
 * Awards an achievement to a user if they don't already have it.
 * @param mysqli $db_connection The database connection object.
 * @param int $user_id The user to award the achievement to.
 * @param int $achievement_id The ID of the achievement.
 */
function award_achievement(mysqli $db_connection, int $user_id, int $achievement_id) {
    $check_stmt = $db_connection->prepare("SELECT User_ID FROM User_Achievements WHERE User_ID = ? AND Achievement_ID = ?");
    $check_stmt->bind_param("ii", $user_id, $achievement_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $check_stmt->close();

    if ($result->num_rows == 0) {
        $insert_stmt = $db_connection->prepare("INSERT INTO User_Achievements (User_ID, Achievement_ID) VALUES (?, ?)");
        $insert_stmt->bind_param("ii", $user_id, $achievement_id);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
}

/**
 * Creates a notification and links it to a specific user.
 * @param mysqli $db_connection The database connection object.
 * @param int $user_id The user to send the notification to.
 * @param string $content The text content of the notification.
 */
function create_notification_for_user(mysqli $db_connection, int $user_id, string $content) {
    $db_connection->begin_transaction();
    try {
        $stmt_notification = $db_connection->prepare("INSERT INTO Notifications (Notification_Content, Notification_Date) VALUES (?, NOW())");
        $stmt_notification->bind_param("s", $content);
        $stmt_notification->execute();
        $notification_id = $db_connection->insert_id;
        $stmt_notification->close();

        $stmt_user_notification = $db_connection->prepare("INSERT INTO Notification_User (Notification_ID, User_ID) VALUES (?, ?)");
        $stmt_user_notification->bind_param("ii", $notification_id, $user_id);
        $stmt_user_notification->execute();
        $stmt_user_notification->close();

        $db_connection->commit();
    } catch (mysqli_sql_exception $exception) {
        $db_connection->rollback();
        error_log("Failed to create notification for User ID $user_id: " . $exception->getMessage());
    }
}
?>