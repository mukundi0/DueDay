<?php
// API Endpoint - should not output HTML.
// Use the core initializer for DB and session functions.
require_once 'core/init.php';

// Set the content type header to JSON for all responses from this file.
header('Content-Type: application/json');

// Security: API endpoints must be protected.
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
    exit();
}

if (!isset($_GET['poll_id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameter: poll_id.']);
    exit();
}

$poll_id = (int)$_GET['poll_id'];
$results = [];
$poll_title = '';

$sql = "
    SELECT
        p.Poll_Title,
        po.Option_Text,
        COUNT(pd.User_ID) as Vote_Count
    FROM Polls p
    JOIN Poll_Options po ON p.Poll_ID = po.Poll_ID
    LEFT JOIN Poll_Data pd ON po.Option_ID = pd.Option_ID
    WHERE p.Poll_ID = ?
    GROUP BY p.Poll_Title, po.Option_ID, po.Option_Text
    ORDER BY po.Option_ID
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $poll_id);
$stmt->execute();
$result_set = $stmt->get_result();

if ($result_set) {
    while ($row = $result_set->fetch_assoc()) {
        if (empty($poll_title)) {
            $poll_title = $row['Poll_Title'];
        }
        $results[] = [
            'option_text' => $row['Option_Text'],
            'vote_count' => (int)$row['Vote_Count']
        ];
    }
}

$stmt->close();
$conn->close();

$response_data = [
    'status' => 'success',
    'poll_id' => $poll_id,
    'poll_title' => $poll_title,
    'results' => $results
];

echo json_encode($response_data);
exit();