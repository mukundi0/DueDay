<?php
// Set headers for Server-Sent Events
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// We need the database connection from our core file.
require_once __DIR__ . '/core/init.php';

// This function formats the data into the required SSE format.
function send_sse_message($id, $data) {
    echo "id: $id\n";
    echo "data: " . json_encode($data) . "\n\n";
    ob_flush();
    flush();
}

// Get the ID of the last event the client received.
// This prevents sending old news when a user first connects.
$last_event_id = $_SERVER["HTTP_LAST_EVENT_ID"] ?? 0;

// This script will run in a loop to continuously check for new data.
while (true) {
    // Query for any new announcements created after the last one we sent.
    $stmt = $conn->prepare("SELECT Announcement_ID, Announcement_Title, Announcement_Description FROM Announcements WHERE Status = 'Active' AND Announcement_ID > ? ORDER BY Announcement_ID ASC");
    $stmt->bind_param("i", $last_event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($announcement = $result->fetch_assoc()) {
        // If we find a new announcement, send it to the client.
        send_sse_message($announcement['Announcement_ID'], $announcement);
        
        // Update the last event ID so we don't send this one again.
        $last_event_id = $announcement['Announcement_ID'];
    }
    $stmt->close();

    // If the connection to the client is broken (e.g., they closed the tab), stop the script.
    if (connection_aborted()) {
        break;
    }

    // Wait for a few seconds before checking again to avoid overloading the server.
    sleep(5);
}

$conn->close();
?>