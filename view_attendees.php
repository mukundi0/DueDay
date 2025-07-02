<?php
// INITIALIZATION - The header file now handles init.php, session, and security.
require_once 'templates/header.php';

// --- PAGE-SPECIFIC SECURITY ---
// Check for the correct role for this page.
if ($user_role !== 'Event Coordinator' && $user_role !== 'Admin') {
    header("Location: home.php");
    exit();
}

// Check that an event ID was provided in the URL.
if (!isset($_GET['id'])) {
    header("Location: event.php");
    exit();
}

$event_id = (int)$_GET['id'];

// --- DATA FETCHING ---
// Get the event's name for the page header.
$stmt_event = $conn->prepare("SELECT Event_Name FROM Events WHERE Event_ID = ?");
$stmt_event->bind_param("i", $event_id);
$stmt_event->execute();
$event = $stmt_event->get_result()->fetch_assoc();
$stmt_event->close();

if (!$event) {
    header("Location: event.php");
    exit();
}

// Get the list of all attendees for this event.
$attendees = [];
$sql = "SELECT u.F_Name, u.L_Name, u.Email
        FROM Users u
        JOIN Event_Attendee_Data ead ON u.User_ID = ead.User_ID
        WHERE ead.Event_ID = ?
        ORDER BY u.L_Name, u.F_Name";
$stmt_attendees = $conn->prepare($sql);
$stmt_attendees->bind_param("i", $event_id);
$stmt_attendees->execute();
$result = $stmt_attendees->get_result();
if ($result) {
    $attendees = $result->fetch_all(MYSQLI_ASSOC);
}
$stmt_attendees->close();
?>

<div class="welcome-header">
    <div class="greeting">
        <h1>Attendee List</h1>
        <p>For event: <strong><?php echo htmlspecialchars($event['Event_Name']); ?></strong></p>
    </div>
</div>

<div class="management-table">
    <h2 class="section-title">Total RSVPs: <?php echo count($attendees); ?></h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($attendees)): ?>
                <tr><td colspan="4">No one has RSVP'd to this event yet.</td></tr>
            <?php else: ?>
                <?php foreach ($attendees as $index => $attendee): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($attendee['F_Name']); ?></td>
                        <td><?php echo htmlspecialchars($attendee['L_Name']); ?></td>
                        <td><?php echo htmlspecialchars($attendee['Email']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <div class="page-actions" style="margin-top: 20px;">
        <a href="event.php" class="btn btn--secondary" style="text-decoration:none;">Back to Events</a>
    </div>
</div>

<?php 
// This now includes the closing tags for the page and closes the DB connection.
require_once 'templates/footer.php'; 
?>