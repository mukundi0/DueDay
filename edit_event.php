<?php
// INITIALIZATION - The header file now handles init.php, session, and security.
require_once 'templates/header.php';

// --- PAGE-SPECIFIC SECURITY & SETUP ---
// Check if the user has the correct role for this page.
if ($user_role !== 'Event Coordinator' && $user_role !== 'Admin') {
    header("Location: home.php");
    exit();
}

// Check if an Event ID was provided in the URL.
if (!isset($_GET['id'])) {
    header("Location: event.php");
    exit();
}

$event_id = (int)$_GET['id'];
$message = '';
$message_type = '';


// --- FORM SUBMISSION (POST REQUEST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_name = $_POST['event_name'];
    $event_description = $_POST['event_description'];
    $event_date = $_POST['event_date'];
    $venue_id = $_POST['venue_id'];

    $stmt = $conn->prepare("UPDATE Events SET Event_Name = ?, Event_Description = ?, Event_Date = ?, Venue_ID = ? WHERE Event_ID = ?");
    $stmt->bind_param("sssis", $event_name, $event_description, $event_date, $venue_id, $event_id);

    if ($stmt->execute()) {
        $message = "Event updated successfully!";
        $message_type = "success";
    } else {
        $message = "Error updating event: " . $conn->error;
        $message_type = "error";
    }
    $stmt->close();
}


// --- DATA FETCHING FOR FORM ---
// Fetch the current details of the event to populate the form.
$stmt_event = $conn->prepare("SELECT * FROM Events WHERE Event_ID = ?");
$stmt_event->bind_param("i", $event_id);
$stmt_event->execute();
$event = $stmt_event->get_result()->fetch_assoc();
$stmt_event->close();

if (!$event) {
    // If no event with that ID exists, redirect away.
    header("Location: event.php");
    exit();
}

// Fetch all available venues to populate the dropdown list.
$venues = $conn->query("SELECT * FROM Venues ORDER BY Venue_Name")->fetch_all(MYSQLI_ASSOC);

?>

<div class="form-container">
    <h1 class="page-title">Edit Event</h1>

    <?php if ($message): ?>
        <p class="message-banner <?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST" action="edit_event.php?id=<?php echo $event_id; ?>">
        <div class="form-group">
            <label for="event_name">Event Name:</label>
            <input type="text" id="event_name" name="event_name" class="form-input" value="<?php echo htmlspecialchars($event['Event_Name']); ?>" required>
        </div>

        <div class="form-group">
            <label for="event_date">Event Date & Time:</label>
            <input type="datetime-local" id="event_date" name="event_date" class="form-input" value="<?php echo date('Y-m-d\TH:i', strtotime($event['Event_Date'])); ?>" required>
        </div>

        <div class="form-group">
            <label for="venue_id">Venue:</label>
            <select id="venue_id" name="venue_id" class="form-input" required>
                <option value="">-- Select a Venue --</option>
                <?php foreach ($venues as $venue): ?>
                    <option value="<?php echo $venue['Venue_ID']; ?>" <?php if ($venue['Venue_ID'] == $event['Venue_ID']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($venue['Venue_Name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="event_description">Event Description:</label>
            <textarea id="event_description" name="event_description" class="form-input" rows="4"><?php echo htmlspecialchars($event['Event_Description']); ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn--primary">Save Changes</button>
            <a href="event.php" class="btn btn--secondary">Cancel</a>
        </div>
    </form>
</div>

<?php 
// This now includes the closing tags for the page and closes the DB connection.
require_once 'templates/footer.php'; 
?>