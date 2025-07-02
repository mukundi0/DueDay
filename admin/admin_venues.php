<?php
require_once 'templates/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add_venue' && !empty($_POST['venue_name'])) {
        $stmt = $conn->prepare("INSERT INTO Venues (Venue_Name) VALUES (?)");
        $stmt->bind_param("s", $_POST['venue_name']);
        $stmt->execute();
    }
    if ($action === 'delete_venue') {
        $stmt = $conn->prepare("DELETE FROM Venues WHERE Venue_ID = ?");
        $stmt->bind_param("i", $_POST['venue_id']);
        $stmt->execute();
    }
    header("Location: admin_venues.php");
    exit();
}

$venues = $conn->query("SELECT * FROM Venues ORDER BY Venue_Name ASC")->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<head>
    <title>Manage Venues - Admin</title>
</head>

<h1 class="page-title">Manage Venues</h1>
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