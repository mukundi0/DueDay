<?php
// admin/admin_enroll_students.php

require_once 'templates/header.php';

$message = '';
$message_type = 'success';
$class_id_to_manage = $_GET['class_id'] ?? null;

// Handle POST actions for enrolling or unenrolling a single user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $user_id = $_POST['user_id'];
    $class_id_to_manage = $_POST['class_id']; // Continue managing the same class

    if ($_POST['action'] === 'enroll') {
        $stmt = $conn->prepare("INSERT INTO user_classes (User_ID, Class_ID) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $class_id_to_manage);
        if($stmt->execute()){
            $message = "User enrolled successfully!";
        }
        $stmt->close();
    } elseif ($_POST['action'] === 'unenroll') {
        $stmt = $conn->prepare("DELETE FROM user_classes WHERE User_ID = ? AND Class_ID = ?");
        $stmt->bind_param("ii", $user_id, $class_id_to_manage);
        if($stmt->execute()){
            $message = "User unenrolled successfully!";
        }
        $stmt->close();
    }
    // Use GET to show message after redirecting to the same class management page
    header("Location: admin_enroll_students.php?class_id=" . $class_id_to_manage . "&message=" . urlencode($message) . "&type=" . $message_type);
    exit();
}

// Fetch any message from the redirect
if(isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type'] ?? 'success');
}

// Fetch all classes for the dropdown
$all_classes = $conn->query("SELECT * FROM Classes ORDER BY Class_Name ASC")->fetch_all(MYSQLI_ASSOC);

$enrolled_users = [];
$unenrolled_users = [];

if ($class_id_to_manage) {
    // Get all users, regardless of role
    $all_users_result = $conn->query("SELECT User_ID, F_Name, L_Name, Email FROM Users ORDER BY L_Name, F_Name");
    $all_users = $all_users_result->fetch_all(MYSQLI_ASSOC);
    
    // Get IDs of users enrolled in the current class
    $stmt_enrolled = $conn->prepare("SELECT User_ID FROM user_classes WHERE Class_ID = ?");
    $stmt_enrolled->bind_param("i", $class_id_to_manage);
    $stmt_enrolled->execute();
    $result_enrolled = $stmt_enrolled->get_result();
    $enrolled_ids = [];
    while($row = $result_enrolled->fetch_assoc()){
        $enrolled_ids[] = $row['User_ID'];
    }
    $stmt_enrolled->close();

    // Separate users into enrolled and unenrolled lists
    foreach ($all_users as $user) {
        if (in_array($user['User_ID'], $enrolled_ids)) {
            $enrolled_users[] = $user;
        } else {
            $unenrolled_users[] = $user;
        }
    }
}
?>
<style>
    .enrollment-container { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
    .enrollment-list h3 { border-bottom: 2px solid var(--border-color); padding-bottom: 10px; margin-bottom: 15px; }
    .student-item { display: flex; justify-content: space-between; align-items: center; padding: 10px; border-radius: 5px; background-color: #f9fafb; margin-bottom: 10px; }
    .student-item form { margin: 0; }
</style>

<h1 class="page-title">Manage User Enrollment</h1>

<?php if ($message): ?>
    <div class="message-banner" style="padding: 15px; margin-bottom: 20px; border-radius: 5px; background-color: <?php echo $message_type === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $message_type === 'success' ? '#155724' : '#721c24'; ?>;">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="widget">
    <h3>Select a Class to Manage</h3>
    <form method="GET" action="admin_enroll_students.php" class="widget-form">
        <select name="class_id" class="form-input" onchange="this.form.submit()">
            <option value="">-- Choose a Class --</option>
            <?php foreach ($all_classes as $class): ?>
                <option value="<?php echo $class['Class_ID']; ?>" <?php if ($class_id_to_manage == $class['Class_ID']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($class['Class_Name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <a href="admin_classes.php" class="action-button secondary">Back to Classes</a>
    </form>
</div>

<?php if ($class_id_to_manage): ?>
<div class="enrollment-container management-section">
    <div class="enrollment-list">
        <h3>Enrolled Users (<?php echo count($enrolled_users); ?>)</h3>
        <?php if (empty($enrolled_users)): ?>
            <p>No users are currently enrolled in this class.</p>
        <?php else: ?>
            <?php foreach ($enrolled_users as $user): ?>
                <div class="student-item">
                    <span><?php echo htmlspecialchars($user['F_Name'] . ' ' . $user['L_Name']); ?></span>
                    <form method="POST" action="admin_enroll_students.php">
                        <input type="hidden" name="action" value="unenroll">
                        <input type="hidden" name="user_id" value="<?php echo $user['User_ID']; ?>">
                        <input type="hidden" name="class_id" value="<?php echo $class_id_to_manage; ?>">
                        <button type="submit" class="table-action-btn delete">Unenroll</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="enrollment-list">
        <h3>Available Users (<?php echo count($unenrolled_users); ?>)</h3>
        <?php if (empty($unenrolled_users)): ?>
            <p>All available users are enrolled.</p>
        <?php else: ?>
            <?php foreach ($unenrolled_users as $user): ?>
                <div class="student-item">
                    <span><?php echo htmlspecialchars($user['F_Name'] . ' ' . $user['L_Name']); ?></span>
                    <form method="POST" action="admin_enroll_students.php">
                        <input type="hidden" name="action" value="enroll">
                        <input type="hidden" name="user_id" value="<?php echo $user['User_ID']; ?>">
                        <input type="hidden" name="class_id" value="<?php echo $class_id_to_manage; ?>">
                        <button type="submit" class="table-action-btn activate">Enroll</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once 'templates/footer.php'; ?>