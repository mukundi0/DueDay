<?php
// LOGIC
require_once 'templates/header.php'; // Includes connection, session, security, and sidebar

$message = '';
$message_type = 'success';

// Handle form submissions for adding or deleting classes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Action to add a new class
    if ($action === 'add_class' && !empty($_POST['class_name'])) {
        $stmt = $conn->prepare("INSERT INTO Classes (Class_Name) VALUES (?)");
        $stmt->bind_param("s", $_POST['class_name']);
        if ($stmt->execute()) {
            $message = "Class added successfully!";
        } else {
            $message = "Error adding class.";
            $message_type = 'error';
        }
        $stmt->close();
    }

    // Action to delete an existing class
    if ($action === 'delete_class' && !empty($_POST['class_id'])) {
        $class_id_to_delete = $_POST['class_id'];
        
        //Comprehensive check for all dependencies
        $check_assignments = $conn->query("SELECT COUNT(*) as count FROM assignments WHERE Class_ID = $class_id_to_delete")->fetch_assoc()['count'];
        $check_schedule = $conn->query("SELECT COUNT(*) as count FROM class_schedule WHERE Class_ID = $class_id_to_delete")->fetch_assoc()['count'];
        $check_polls = $conn->query("SELECT COUNT(*) as count FROM polls WHERE Class_ID = $class_id_to_delete")->fetch_assoc()['count'];
        $check_users = $conn->query("SELECT COUNT(*) as count FROM user_classes WHERE Class_ID = $class_id_to_delete")->fetch_assoc()['count'];

        if ($check_assignments > 0) {
            $message = "Cannot delete this class because it has active assignments. Please re-assign or delete them first.";
            $message_type = 'error';
        } elseif ($check_schedule > 0) {
            $message = "Cannot delete this class because it is on the weekly schedule. Please remove it from the schedule first.";
            $message_type = 'error';
        } elseif ($check_polls > 0) {
            $message = "Cannot delete this class because it has active polls. Please remove them first.";
            $message_type = 'error';
        } elseif ($check_users > 0) {
            $message = "Cannot delete this class because students are enrolled. Please unenroll all students first.";
            $message_type = 'error';
        } else {
            // If all checks pass, proceed with deletion
            $stmt = $conn->prepare("DELETE FROM Classes WHERE Class_ID = ?");
            $stmt->bind_param("i", $class_id_to_delete);
            if ($stmt->execute()) {
                $message = "Class deleted successfully!";
            } else {
                $message = "An unexpected error occurred during deletion.";
                $message_type = 'error';
            }
            $stmt->close();
        }
    }

    // Redirect to the same page to prevent form resubmission and show the message
    header("Location: admin_classes.php?message=" . urlencode($message) . "&type=" . $message_type);
    exit();
}

// Fetch any message from the redirect
if (isset($_GET['message'])) {
    $message = $_GET['message'];
    $message_type = $_GET['type'] ?? 'success';
}


// Fetch all existing classes to display in the list
$classes = $conn->query("SELECT * FROM Classes ORDER BY Class_Name ASC")->fetch_all(MYSQLI_ASSOC);
$conn->close();

// PRESENTATION VIEW
?>
<head>
    <title>Manage Classes - Admin</title>
</head>

<h1 class="page-title">Manage Classes</h1>

<?php if ($message): ?>
    <div class="message-banner" style="padding: 15px; margin-bottom: 20px; border-radius: 5px; background-color: <?php echo $message_type === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $message_type === 'success' ? '#155724' : '#721c24'; ?>;">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>


<div class="widget">
    <h3>Add New Class</h3>
    <form method="POST" action="admin_classes.php" class="widget-form">
        <input type="hidden" name="action" value="add_class">
        <input type="text" name="class_name" class="form-input" placeholder="New class name (e.g., CS101)..." required>
        <button type="submit" class="action-button">Add Class</button>
    </form>
</div>

<div class="management-section">
    <h2>Existing Classes</h2>
    <ul class="widget-list">
        <?php if (empty($classes)): ?>
            <li>No classes found. Add one using the form above.</li>
        <?php else: ?>
            <?php foreach ($classes as $class): ?>
                <li class="widget-list-item">
                    <span><?php echo htmlspecialchars($class['Class_Name']); ?></span>
                    <form method="POST" action="admin_classes.php" data-confirm="Are you sure you want to delete this class? This can only be done if no students or assignments are linked to it.">
                        <input type="hidden" name="action" value="delete_class">
                        <input type="hidden" name="class_id" value="<?php echo $class['Class_ID']; ?>">
                        <button type="submit" class="table-action-btn delete">Delete</button>
                    </form>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>

<?php require_once 'templates/footer.php'; ?>