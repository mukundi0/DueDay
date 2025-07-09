<?php
// --- LOGIC ---
require_once 'templates/header.php'; // Includes connection, session, security, and sidebar

$message = '';
$message_type = 'success';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_schedule') {
        $class_id = $_POST['class_id'];
        $venue_id = $_POST['venue_id'];
        $day_of_week = $_POST['day_of_week'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];

        // --- NEW: CONFLICT CHECKING LOGIC ---
        $conflict_stmt = $conn->prepare(
            "SELECT Entry_ID FROM class_schedule 
             WHERE Venue_ID = ? 
             AND Day_Of_Week = ? 
             AND ((Start_Time < ? AND End_Time > ?) OR (Start_Time >= ? AND Start_Time < ?))"
        );
        // Parameters: venue, day, new_end_time, new_start_time, new_start_time, new_end_time
        $conflict_stmt->bind_param("iissss", $venue_id, $day_of_week, $end_time, $start_time, $start_time, $end_time);
        $conflict_stmt->execute();
        $conflict_result = $conflict_stmt->get_result();

        if ($conflict_result->num_rows > 0) {
            $message = "Error: A class is already scheduled in this venue at the specified time.";
            $message_type = 'error';
        } else {
             // No conflict, proceed with insertion
            $stmt = $conn->prepare("INSERT INTO class_schedule (Class_ID, Venue_ID, Day_Of_Week, Start_Time, End_Time) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iiiss", $class_id, $venue_id, $day_of_week, $start_time, $end_time);
            if ($stmt->execute()) {
                $message = "Class scheduled successfully!";
            } else {
                $message = "An error occurred while scheduling the class.";
                $message_type = 'error';
            }
            $stmt->close();
        }
        $conflict_stmt->close();
    }

    if ($action === 'delete_schedule') {
        $stmt = $conn->prepare("DELETE FROM class_schedule WHERE Entry_ID = ?");
        $stmt->bind_param("i", $_POST['entry_id']);
        $stmt->execute();
        $stmt->close();
        $message = "Schedule entry deleted successfully!";
    }

    // Use GET parameters to show message after redirect
    header("Location: admin_schedule.php?message=" . urlencode($message) . "&type=" . $message_type);
    exit();
}


// Fetch any message from the redirect
if (isset($_GET['message'])) {
    $message = $_GET['message'];
    $message_type = $_GET['type'] ?? 'success';
}


// Fetch data for the page
$classes = $conn->query("SELECT * FROM Classes ORDER BY Class_Name ASC")->fetch_all(MYSQLI_ASSOC);
$venues = $conn->query("SELECT * FROM Venues ORDER BY Venue_Name ASC")->fetch_all(MYSQLI_ASSOC);
$schedule = $conn->query("
    SELECT cs.*, c.Class_Name, v.Venue_Name
    FROM class_schedule cs
    JOIN Classes c ON cs.Class_ID = c.Class_ID
    JOIN Venues v ON cs.Venue_ID = v.Venue_ID
    ORDER BY cs.Day_Of_Week, cs.Start_Time
")->fetch_all(MYSQLI_ASSOC);

$days_of_week = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];

$conn->close();

// --- PRESENTATION ---
?>
<head>
    <title>Manage Schedule - Admin</title>
</head>

<h1 class="page-title">Manage Class Schedule</h1>

<?php if ($message): ?>
    <div class="message-banner <?php echo $message_type; ?>" style="padding: 15px; margin-bottom: 20px; border-radius: 5px; background-color: <?php echo $message_type === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $message_type === 'success' ? '#155724' : '#721c24'; ?>;">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="widget">
    <h3>Add New Schedule Entry</h3>
    <form method="POST" action="admin_schedule.php">
        <input type="hidden" name="action" value="add_schedule">
        <div class="form-group">
            <label for="class_id">Class:</label>
            <select name="class_id" id="class_id" class="form-input" required>
                <?php foreach ($classes as $class): ?>
                    <option value="<?php echo $class['Class_ID']; ?>"><?php echo htmlspecialchars($class['Class_Name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="venue_id">Venue:</label>
            <select name="venue_id" id="venue_id" class="form-input" required>
                <?php foreach ($venues as $venue): ?>
                    <option value="<?php echo $venue['Venue_ID']; ?>"><?php echo htmlspecialchars($venue['Venue_Name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="day_of_week">Day of Week:</label>
            <select name="day_of_week" id="day_of_week" class="form-input" required>
                <?php foreach ($days_of_week as $day_num => $day_name): ?>
                    <option value="<?php echo $day_num; ?>"><?php echo $day_name; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="start_time">Start Time:</label>
            <input type="time" name="start_time" id="start_time" class="form-input" required>
        </div>
        <div class="form-group">
            <label for="end_time">End Time:</label>
            <input type="time" name="end_time" id="end_time" class="form-input" required>
        </div>
        <button type="submit" class="action-button">Add to Schedule</button>
    </form>
</div>

<div class="management-section">
    <h2>Current Schedule</h2>
    <table>
        <thead>
            <tr>
                <th>Day</th>
                <th>Time</th>
                <th>Class</th>
                <th>Venue</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($schedule)): ?>
                <tr><td colspan="5">No classes scheduled yet.</td></tr>
            <?php else: ?>
                <?php foreach ($schedule as $entry): ?>
                    <tr>
                        <td><?php echo $days_of_week[$entry['Day_Of_Week']]; ?></td>
                        <td><?php echo date('h:i A', strtotime($entry['Start_Time'])) . ' - ' . date('h:i A', strtotime($entry['End_Time'])); ?></td>
                        <td><?php echo htmlspecialchars($entry['Class_Name']); ?></td>
                        <td><?php echo htmlspecialchars($entry['Venue_Name']); ?></td>
                        <td class="action-cell">
                            <form method="POST" action="admin_schedule.php" data-confirm="Are you sure you want to delete this schedule entry?">
                                <input type="hidden" name="action" value="delete_schedule">
                                <input type="hidden" name="entry_id" value="<?php echo $entry['Entry_ID']; ?>">
                                <button type="submit" class="table-action-btn delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'templates/footer.php'; ?>