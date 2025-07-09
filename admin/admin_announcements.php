<?php
require_once 'templates/header.php';

$message = '';
$message_type = 'success';

// --- ACTION HANDLING ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Action to send a new announcement
    if ($action === 'send_global_announcement') {
        $title = $_POST['announcement_title'];
        $description = $_POST['announcement_description'];
        $priority_id = $_POST['announcement_priority']; 

        $stmt_announce = $conn->prepare("INSERT INTO Announcements (Announcement_Title, Announcement_Description, Announcement_Priority, Creator_User_ID) VALUES (?, ?, ?, ?)");
        $stmt_announce->bind_param("ssii", $title, $description, $priority_id, $_SESSION['user_id']);
        
        if ($stmt_announce->execute()) {
            $message = "Announcement sent successfully!";
        } else {
            $message = "Error: Could not send announcement.";
            $message_type = 'error';
        }
        $stmt_announce->close();
    }

    // Action to archive an announcement
    if ($action === 'archive_announcement') {
        $stmt_archive = $conn->prepare("UPDATE Announcements SET Status = 'Archived' WHERE Announcement_ID = ?");
        $stmt_archive->bind_param("i", $_POST['announcement_id']);
        if ($stmt_archive->execute()) {
             $message = "Announcement archived successfully.";
        } else {
            $message = "Error: Could not archive announcement.";
            $message_type = 'error';
        }
        $stmt_archive->close();
    }
    
    // ** NEW: Action to unarchive an announcement **
    if ($action === 'unarchive_announcement') {
        $stmt_unarchive = $conn->prepare("UPDATE Announcements SET Status = 'Active' WHERE Announcement_ID = ?");
        $stmt_unarchive->bind_param("i", $_POST['announcement_id']);
        if ($stmt_unarchive->execute()) {
             $message = "Announcement has been restored to active.";
        } else {
            $message = "Error: Could not restore announcement.";
            $message_type = 'error';
        }
        $stmt_unarchive->close();
    }
    
    // ** NEW: Action to permanently delete an announcement **
    if ($action === 'delete_announcement') {
        $stmt_delete = $conn->prepare("DELETE FROM Announcements WHERE Announcement_ID = ?");
        $stmt_delete->bind_param("i", $_POST['announcement_id']);
        if ($stmt_delete->execute()) {
             $message = "Announcement permanently deleted.";
        } else {
            $message = "Error: Could not delete announcement.";
            $message_type = 'error';
        }
        $stmt_delete->close();
    }
    
    header("Location: admin_announcements.php?message=" . urlencode($message) . "&type=" . $message_type);
    exit();
}

// --- DATA FETCHING ---

if(isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type'] ?? 'success');
}

$priorities = $conn->query("SELECT * FROM Priority ORDER BY Priority_ID ASC")->fetch_all(MYSQLI_ASSOC);
// Fetch both active and archived announcements
$active_announcements = $conn->query("SELECT * FROM Announcements WHERE Status = 'Active' ORDER BY Announcement_ID DESC")->fetch_all(MYSQLI_ASSOC);
$archived_announcements = $conn->query("SELECT * FROM Announcements WHERE Status = 'Archived' ORDER BY Announcement_ID DESC")->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<head>
    <title>Global Announcements - Admin</title>
</head>

<h1 class="page-title">Global Announcements</h1>

<?php if ($message): ?>
    <div class="message-banner" style="padding: 15px; margin-bottom: 20px; border-radius: 5px; background-color: <?php echo $message_type === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $message_type === 'success' ? '#155724' : '#721c24'; ?>;">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="management-section">
    <h3>Create New Announcement</h3>
    <form method="POST" action="admin_announcements.php">
        <input type="hidden" name="action" value="send_global_announcement">
        <div class="form-group">
            <label for="announcement_title">Title:</label>
            <input type="text" id="announcement_title" name="announcement_title" class="form-input" required>
        </div>
        <div class="form-group">
            <label for="announcement_description">Description:</label>
            <textarea id="announcement_description" name="announcement_description" class="form-input" rows="5" required></textarea>
        </div>
        <div class="form-group">
            <label for="announcement_priority">Priority:</label>
            <select id="announcement_priority" name="announcement_priority" class="form-input" required>
                 <?php foreach ($priorities as $priority): ?>
                    <option value="<?php echo $priority['Priority_ID']; ?>"><?php echo htmlspecialchars($priority['Priority_Type']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="action-button">Send to All Users</button>
    </form>
</div>

<div class="management-section">
    <h2>Active Announcements</h2>
    <ul class="widget-list">
        <?php if (empty($active_announcements)): ?>
            <li class="widget-list-item">No active announcements.</li>
        <?php else: ?>
            <?php foreach ($active_announcements as $announcement): ?>
                <li class="widget-list-item">
                    <span><strong><?php echo htmlspecialchars($announcement['Announcement_Title']); ?></strong>: <?php echo htmlspecialchars($announcement['Announcement_Description']); ?></span>
                    <form method="POST" action="admin_announcements.php" data-confirm="Are you sure you want to archive this announcement? It will no longer be visible to users.">
                        <input type="hidden" name="action" value="archive_announcement">
                        <input type="hidden" name="announcement_id" value="<?php echo $announcement['Announcement_ID']; ?>">
                        <button type="submit" class="table-action-btn edit" style="background-color: #6b7280;">Archive</button>
                    </form>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>

<div class="management-section">
    <h2>Archived Announcements</h2>
    <ul class="widget-list">
        <?php if (empty($archived_announcements)): ?>
            <li class="widget-list-item">No archived announcements.</li>
        <?php else: ?>
            <?php foreach ($archived_announcements as $announcement): ?>
                <li class="widget-list-item">
                    <span><strong><?php echo htmlspecialchars($announcement['Announcement_Title']); ?></strong>: <?php echo htmlspecialchars($announcement['Announcement_Description']); ?></span>
                    <div class="action-cell">
                        <form method="POST" action="admin_announcements.php" style="margin-right: 5px;">
                            <input type="hidden" name="action" value="unarchive_announcement">
                            <input type="hidden" name="announcement_id" value="<?php echo $announcement['Announcement_ID']; ?>">
                            <button type="submit" class="table-action-btn activate">Unarchive</button>
                        </form>
                        <form method="POST" action="admin_announcements.php" data-confirm="Are you sure you want to PERMANENTLY DELETE this announcement? This action cannot be undone.">
                            <input type="hidden" name="action" value="delete_announcement">
                            <input type="hidden" name="announcement_id" value="<?php echo $announcement['Announcement_ID']; ?>">
                            <button type="submit" class="table-action-btn delete">Delete</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>

<?php require_once 'templates/footer.php'; ?>