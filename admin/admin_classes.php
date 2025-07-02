<?php
// --- LOGIC ---
require_once 'templates/header.php'; // Includes connection, session, security, and sidebar

// Handle form submissions for adding or deleting classes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Action to add a new class
    if ($action === 'add_class' && !empty($_POST['class_name'])) {
        $stmt = $conn->prepare("INSERT INTO Classes (Class_Name) VALUES (?)");
        $stmt->bind_param("s", $_POST['class_name']);
        $stmt->execute();
        $stmt->close();
    }

    // Action to delete an existing class
    if ($action === 'delete_class' && !empty($_POST['class_id'])) {
        $stmt = $conn->prepare("DELETE FROM Classes WHERE Class_ID = ?");
        $stmt->bind_param("i", $_POST['class_id']);
        $stmt->execute();
        $stmt->close();
    }

    // Redirect to the same page to prevent form resubmission
    header("Location: admin_classes.php");
    exit();
}

// Fetch all existing classes to display in the list
$classes = $conn->query("SELECT * FROM Classes ORDER BY Class_Name ASC")->fetch_all(MYSQLI_ASSOC);
$conn->close();

// --- PRESENTATION ---
?>
<head>
    <title>Manage Classes - Admin</title>
</head>

<h1 class="page-title">Manage Classes</h1>

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
                    <form method="POST" action="admin_classes.php" data-confirm="Are you sure you want to delete this class? This could affect scheduled classes.">
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