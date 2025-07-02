<?php
// This script marks all comments for a given assignment as read by the current user.
require_once __DIR__ . '/core/init.php';

// We expect a POST request for this action
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    exit();
}

header('Content-Type: application/json');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$assignment_id = (int)($_POST['assignment_id'] ?? 0);

if ($assignment_id === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Assignment ID.']);
    exit();
}

/*
 * This is the core logic.
 * It finds all Comment_IDs for the given Assignment_ID and attempts to insert
 * a (user_id, comment_id) pair into the user_read_comments table.
 * "INSERT IGNORE" is used so that if a record already exists (meaning the user
 * has already read that comment), the query doesn't fail. It simply ignores it.
 * This is a very efficient way to mark multiple comments as read at once.
 */
$sql = "INSERT IGNORE INTO user_read_comments (User_ID, Comment_ID) 
        SELECT ?, Comment_ID FROM assignment_comments WHERE Assignment_ID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $assignment_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database operation failed.']);
}

$stmt->close();
$conn->close();
?>