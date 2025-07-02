<?php
require_once __DIR__ . '/core/init.php';

header('Content-Type: application/json');

if (!isset($_GET['assignment_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Assignment ID not provided.']);
    exit();
}

$assignment_id = (int)$_GET['assignment_id'];

$stmt = $conn->prepare("
    SELECT ac.Comment_Text, ac.Comment_Date, u.F_Name
    FROM Assignment_Comments ac
    JOIN Users u ON ac.User_ID = u.User_ID
    WHERE ac.Assignment_ID = ?
    ORDER BY ac.Comment_Date ASC
");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$result = $stmt->get_result();
$comments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(['status' => 'success', 'comments' => $comments]);
$conn->close();
?>