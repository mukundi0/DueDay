<?php
require_once 'templates/header.php';
$is_event_coordinator = ($user_role === 'Event Coordinator');

// POST REQUEST HANDLING
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($is_event_coordinator) {
        if ($action === 'create_event') {
            $stmt = $conn->prepare("INSERT INTO Events (Event_Name, Event_Description, Event_Date, Venue_ID) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $_POST['event_name'], $_POST['event_description'], $_POST['event_date'], $_POST['venue_id']);
            $stmt->execute();
            $stmt->close();
            header("Location: event.php?created=success");
            exit();
        }
        if ($action === 'delete_event') {
            $event_id_to_delete = $_POST['event_id'];
            $conn->begin_transaction();
            try {
                $stmt1 = $conn->prepare("DELETE FROM Event_Attendee_Data WHERE Event_ID = ?"); $stmt1->bind_param("i", $event_id_to_delete); $stmt1->execute(); $stmt1->close();
                $stmt2 = $conn->prepare("DELETE FROM Events WHERE Event_ID = ?"); $stmt2->bind_param("i", $event_id_to_delete); $stmt2->execute(); $stmt2->close();
                $conn->commit();
            } catch (mysqli_sql_exception $e) { $conn->rollback(); }
            header("Location: event.php?deleted=success");
            exit();
        }
    }
    if ($action === 'rsvp') {
        $stmt = $conn->prepare("INSERT INTO Event_Attendee_Data (User_ID, Event_ID) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $_POST['event_id']);
        $stmt->execute(); $stmt->close();
        award_achievement($conn, $user_id, 4);
        header("Location: event.php?rsvp=success");
        exit();
    }
    if ($action === 'cancel_rsvp') {
        $stmt = $conn->prepare("DELETE FROM Event_Attendee_Data WHERE User_ID = ? AND Event_ID = ?");
        $stmt->bind_param("ii", $user_id, $_POST['event_id']);
        $stmt->execute(); $stmt->close();
        header("Location: event.php?rsvp=cancelled");
        exit();
    }
}

// DATA FETCHING
$events = []; $venues = []; $user_rsvps = [];
$stmt_rsvps = $conn->prepare("SELECT Event_ID FROM Event_Attendee_Data WHERE User_ID = ?");
$stmt_rsvps->bind_param("i", $user_id);
$stmt_rsvps->execute();
$result_rsvps = $stmt_rsvps->get_result();
while ($row = $result_rsvps->fetch_assoc()) { $user_rsvps[] = $row['Event_ID']; }
$stmt_rsvps->close();
if ($is_event_coordinator) {
    $result = $conn->query("SELECT e.*, v.Venue_Name, COUNT(ead.Attendee_ID) as rsvp_count FROM Events e JOIN Venues v ON e.Venue_ID = v.Venue_ID LEFT JOIN Event_Attendee_Data ead ON e.Event_ID = ead.Event_ID GROUP BY e.Event_ID ORDER BY e.Event_Date DESC");
    if ($result) $events = $result->fetch_all(MYSQLI_ASSOC);
    $venues_result = $conn->query("SELECT * FROM Venues ORDER BY Venue_Name ASC");
    if ($venues_result) $venues = $venues_result->fetch_all(MYSQLI_ASSOC);
} else {
    $result = $conn->query("SELECT e.*, v.Venue_Name FROM Events e JOIN Venues v ON e.Venue_ID = v.Venue_ID WHERE e.Event_Date >= NOW() ORDER BY e.Event_Date ASC");
    if ($result) $events = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<?php if ($is_event_coordinator): ?>
    <div class="page-actions"><button id="showCreateBtn" class="btn btn--primary">Create New Event</button></div>
    <div class="form-container" id="createForm">
        <h2 class="section-title">New Event Details</h2>
        <form method="POST" action="event.php">
            <input type="hidden" name="action" value="create_event">
            <div class="form-group"><label for="event_name">Event Name:</label><input type="text" id="event_name" name="event_name" class="form-input" required></div>
            <div class="form-group"><label for="event_date">Date and Time:</label><input type="datetime-local" id="event_date" name="event_date" class="form-input" required></div>
            <div class="form-group"><label for="venue_id">Venue:</label><select id="venue_id" name="venue_id" class="form-input" required><option value="">-- Select Venue --</option><?php foreach ($venues as $venue): ?><option value="<?php echo $venue['Venue_ID']; ?>"><?php echo htmlspecialchars($venue['Venue_Name']); ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label for="event_description">Description:</label><textarea id="event_description" name="event_description" class="form-input" rows="4"></textarea></div>
            <button type="submit" class="btn btn--primary">Save Event</button>
        </form>
    </div>
    <div class="management-table">
        <h2 class="section-title">Event Management</h2>
        <table>
            <thead><tr><th>Event Name</th><th>Date</th><th>Venue</th><th>RSVPs</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if (empty($events)): ?><tr><td colspan="5">No events found.</td></tr><?php else: foreach ($events as $event): ?>
                <tr>
                    <td><?php echo htmlspecialchars($event['Event_Name']); ?></td><td><?php echo date('M j, Y - g:ia', strtotime($event['Event_Date'])); ?></td><td><?php echo htmlspecialchars($event['Venue_Name']); ?></td><td><?php echo $event['rsvp_count']; ?></td>
                    <td class="action-cell"><a href="edit_event.php?id=<?php echo $event['Event_ID']; ?>" class="btn btn--secondary">Edit</a><a href="view_attendees.php?id=<?php echo $event['Event_ID']; ?>" class="btn btn--secondary">View List</a><form method="POST" action="event.php" data-confirm="Are you sure you want to delete this event? This cannot be undone."><input type="hidden" name="action" value="delete_event"><input type="hidden" name="event_id" value="<?php echo $event['Event_ID']; ?>"><button type="submit" class="btn btn--danger">Delete</button></form></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="events-list">
        <h2 class="section-title">Upcoming Events</h2>
        <?php if (empty($events)): ?><p>There are no upcoming events scheduled at this time.</p><?php else: foreach ($events as $event): ?>
            <div class="card">
                <h3 class="card__title"><?php echo htmlspecialchars($event['Event_Name']); ?></h3>
                <p><strong>When:</strong> <?php echo date('F jS, Y \a\t g:ia', strtotime($event['Event_Date'])); ?></p><p><strong>Where:</strong> <?php echo htmlspecialchars($event['Venue_Name']); ?></p>
                <p class="description"><?php echo nl2br(htmlspecialchars($event['Event_Description'])); ?></p>
                <div class="card__footer">
                    <?php if (in_array($event['Event_ID'], $user_rsvps)): ?><form method="POST" action="event.php"><input type="hidden" name="action" value="cancel_rsvp"><input type="hidden" name="event_id" value="<?php echo $event['Event_ID']; ?>"><button type="submit" class="btn btn--secondary">Cancel RSVP</button></form>
                    <?php else: ?><form method="POST" action="event.php"><input type="hidden" name="action" value="rsvp"><input type="hidden" name="event_id" value="<?php echo $event['Event_ID']; ?>"><button type="submit" class="btn btn--primary">RSVP Now</button></form><?php endif; ?>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>
<?php endif; ?>

<?php 
require_once 'templates/footer.php';
$conn->close();
?>