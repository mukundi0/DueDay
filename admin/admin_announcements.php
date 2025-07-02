<?php
require_once 'templates/header.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'send_global_announcement') {
    $title = $_POST['announcement_title'];
    $description = $_POST['announcement_description'];
    $priority_id = $_POST['announcement_priority'];

    $conn->begin_transaction();
    try {
        $stmt_announce = $conn->prepare("INSERT INTO Announcements (Announcement_Title, Announcement_Description, Announcement_Priority, Creator_User_ID) VALUES (?, ?, ?, ?)");
        $stmt_announce->bind_param("ssii", $title, $description, $priority_id, $_SESSION['user_id']);
        $stmt_announce->execute();
        $conn->commit();
        $message = "Announcement sent successfully!";
    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        $message = "Error: Could not send announcement.";
    }
}

$priorities = $conn->query("SELECT * FROM Priority ORDER BY Priority_ID ASC")->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<head>
    <title>Global Announcements - Admin</title>
</head>

<h1 class="page-title">Global Announcements</h1>

<div class="management-section">
    <?php if ($message): ?>
        <p class="message-banner"><?php echo $message; ?></p>
    <?php endif; ?>
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

<?php require_once 'templates/footer.php'; ?>