<?php
require_once 'templates/header.php';

$message = '';
$message_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add_venue' && !empty($_POST['venue_name'])) {
        $stmt = $conn->prepare("INSERT INTO Venues (Venue_Name) VALUES (?)");
        $stmt->bind_param("s", $_POST['venue_name']);
        if ($stmt->execute()) {
            $message = "Venue added successfully!";
        } else {
            $message = "Error: Could not add venue.";
            $message_type = 'error';
        }
        $stmt->close();
    }
    if ($action === 'delete_venue') {
        try {
            $stmt = $conn->prepare("DELETE FROM Venues WHERE Venue_ID = ?");
            $stmt->bind_param("i", $_POST['venue_id']);
            $stmt->execute();
            $message = "Venue deleted successfully!";
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1451)/*means foreign constraint key has failed*/{
                $message = "Cannot delete venue. It is currently assigned to an event or a scheduled class.";
                $message_type = 'error';
            } else {
                $message = "An unexpected database error occurred.";
                $message_type = 'error';
            }
        }
    }
    // Redirect to show the message
    header("Location: admin_venues.php?message=" . urlencode($message) . "&type=" . $message_type);
    exit();
}

// Check for a message from the redirect
if(isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type'] ?? 'success');
}


$venues = $conn->query("SELECT * FROM Venues ORDER BY Venue_Name ASC")->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<head>
    <title>Manage Venues - Admin</title>
</head>

<h1 class="page-title">Manage Venues</h1>

<?php if ($message): ?>
    <div class="message-banner" style="padding: 15px; margin-bottom: 20px; border-radius: 5px; background-color: <?php echo $message_type === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $message_type === 'success' ? '#155724' : '#721c24'; ?>;">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="widget">
    <h3>Add New Venue</h3>
    <form method="POST" action="admin_venues.php" class="widget-form">
        <input type="hidden" name="action" value="add_venue">
        <input type="text" name="venue_name" class="form-input" placeholder="New venue name..." required>
        <button type="submit" class="action-button">Add Venue</button>
    </form>
</div>
<div class="management-section">
    <h2>Existing Venues</h2>
    <ul class="widget-list">
        <?php foreach ($venues as $venue): ?>
            <li class="widget-list-item">
                <span><?php echo htmlspecialchars($venue['Venue_Name']); ?></span>
                <form method="POST" action="admin_venues.php" data-confirm="Are you sure? Deleting a venue may affect existing events.">
                    <input type="hidden" name="action" value="delete_venue">
                    <input type="hidden" name="venue_id" value="<?php echo $venue['Venue_ID']; ?>">
                    <button type="submit" class="table-action-btn delete">Delete</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<?php require_once 'templates/footer.php'; ?>