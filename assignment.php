<?php
// This init.php is required first for session, db connection, and AJAX handling.
require_once __DIR__ . '/core/init.php';

// --- POST REQUEST HANDLING (MOVED TO TOP) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_SESSION['user_id'];
    // --- FIX: Permissions returned to Module Leader ---
    $is_module_leader = ($_SESSION['role_name'] === 'Module Leader');

    // -- AJAX ACTION: ADD COMMENT --
    if ($action === 'add_comment') {
        header('Content-Type: application/json');
        $assignment_id = (int)$_POST['assignment_id'];
        $comment_text = trim($_POST['comment_text']);

        if (empty($assignment_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid Assignment ID.']);
            exit();
        }
        if (empty($comment_text)) {
            echo json_encode(['status' => 'error', 'message' => 'Comment cannot be empty.']);
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO Assignment_Comments (Assignment_ID, User_ID, Comment_Text) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $assignment_id, $user_id, $comment_text);
        
        if ($stmt->execute()) {
            $stmt_user = $conn->prepare("SELECT F_Name FROM Users WHERE User_ID = ?");
            $stmt_user->bind_param("i", $user_id);
            $stmt_user->execute();
            $user_result = $stmt_user->get_result()->fetch_assoc();

            echo json_encode([
                'status' => 'success',
                'comment' => [
                    'Comment_Text' => htmlspecialchars($comment_text),
                    'F_Name' => htmlspecialchars($user_result['F_Name']),
                    'Comment_Date' => date('Y-m-d H:i:s')
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
        }
        $stmt->close();
        $conn->close();
        exit();
    }
    
    // -- REGULAR FORM ACTION: CREATE ASSIGNMENT --
    // --- FIX: Check if user is a Module Leader ---
    if ($action === 'create_assignment' && $is_module_leader) {
        $stmt = $conn->prepare("INSERT INTO Assignments (Assignment_Creator_ID, Class_ID, Assignment_Title, Assignment_Description, Assignment_DueDate, Assignment_Marks, Assignment_Instructions) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssis", $user_id, $_POST['class_id'], $_POST['assignment_title'], $_POST['assignment_description'], $_POST['assignment_due_date'], $_POST['assignment_marks'], $_POST['assignment_instructions']);
        $stmt->execute();
        header("Location: assignment.php?created=success");
        exit();
    }

    // -- REGULAR FORM ACTION: SUBMIT ASSIGNMENT --
    if ($action === 'submit_assignment') {
        $assignment_id = $_POST['assignment_id'];
        if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] == UPLOAD_ERR_OK) {
            $target_dir = "uploads/submissions/";
            if (!is_dir($target_dir)) { mkdir($target_dir, 0755, true); }
            $original_filename = basename($_FILES["submission_file"]["name"]);
            $safe_filename = preg_replace("/[^a-zA-Z0-9\._-]/", "", $original_filename);
            $target_file = $target_dir . "assign" . $assignment_id . "_user" . $user_id . "_" . time() . "_" . $safe_filename;
            if (move_uploaded_file($_FILES["submission_file"]["tmp_name"], $target_file)) {
                $stmt_delete = $conn->prepare("DELETE FROM Assignment_Submission_Data WHERE Assignment_ID = ? AND User_ID = ?"); $stmt_delete->bind_param("ii", $assignment_id, $user_id); $stmt_delete->execute();
                $stmt_insert = $conn->prepare("INSERT INTO Assignment_Submission_Data (Assignment_ID, User_ID, Submission_Date, File_Path, Notes) VALUES (?, ?, NOW(), ?, ?)"); $stmt_insert->bind_param("iiss", $assignment_id, $user_id, $target_file, $_POST['submission_notes']); $stmt_insert->execute();
            }
        }
        header("Location: assignment.php?submission=success");
        exit();
    }
}

// --- NORMAL PAGE LOAD STARTS HERE ---
require_once 'templates/header.php';
// --- FIX: Permissions returned to Module Leader ---
$is_module_leader = ($user_role === 'Module Leader');

// --- DATA FETCHING for page display ---
$all_classes = $conn->query("SELECT * FROM Classes ORDER BY Class_Name")->fetch_all(MYSQLI_ASSOC);
$assignments = $conn->query("
    SELECT a.*, c.Class_Name, 
    (SELECT COUNT(*) FROM Assignment_Submission_Data WHERE Assignment_ID = a.Assignment_ID) as submission_count,
    (SELECT COUNT(*) FROM Assignment_Comments WHERE Assignment_ID = a.Assignment_ID) as comment_count
    FROM Assignments a 
    LEFT JOIN Classes c ON a.Class_ID = c.Class_ID 
    GROUP BY a.Assignment_ID 
    ORDER BY a.Assignment_DueDate DESC
")->fetch_all(MYSQLI_ASSOC);
$user_submissions = [];
$stmt_subs = $conn->prepare("SELECT Assignment_ID, File_Path FROM Assignment_Submission_Data WHERE User_ID = ?");
$stmt_subs->bind_param("i", $user_id);
$stmt_subs->execute();
$result_subs = $stmt_subs->get_result();
while ($row = $result_subs->fetch_assoc()) { $user_submissions[$row['Assignment_ID']] = $row['File_Path']; }
?>

<?php if ($is_module_leader): ?>
<div class="page-actions">
    <button class="btn" id="viewBtn">View Assignments</button>
    <button class="btn" id="createBtn">Create New</button>
</div>
<?php endif; ?>

<div id="createSection">
    <?php if ($is_module_leader): ?>
    <div class="form-container">
        <h2 class="section-title">New Assignment Details</h2>
        <form method="POST" action="assignment.php">
            <input type="hidden" name="action" value="create_assignment">
            <div class="form-group"><label for="class_id">Class:</label><select id="class_id" name="class_id" class="form-input" required><option value="">-- Select a Class --</option><?php foreach($all_classes as $class): ?><option value="<?php echo $class['Class_ID']; ?>"><?php echo htmlspecialchars($class['Class_Name']); ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label for="assignment_title">Title:</label><input type="text" id="assignment_title" name="assignment_title" class="form-input" required></div>
            <div class="form-group"><label for="assignment_due_date">Due Date:</label><input type="datetime-local" id="assignment_due_date" name="assignment_due_date" class="form-input" required></div>
            <div class="form-group"><label for="assignment_marks">Total Marks:</label><input type="number" id="assignment_marks" name="assignment_marks" class="form-input"></div>
            <div class="form-group"><label for="assignment_description">Description:</label><textarea id="assignment_description" name="assignment_description" class="form-input" rows="3"></textarea></div>
            <div class="form-group"><label for="assignment_instructions">Instructions:</label><textarea id="assignment_instructions" name="assignment_instructions" class="form-input" rows="5"></textarea></div>
            <button type="submit" class="btn btn--primary">Publish Assignment</button>
        </form>
    </div>
    <?php endif; ?>
</div>

<div id="viewSection" class="assignment-view">
    <h2 class="section-title">Current Assignments</h2>
    <?php if (empty($assignments)): ?><p>No assignments have been posted yet.</p><?php else: foreach ($assignments as $assignment): ?>
        <div class="card">
            <div class="card__header"><h3 class="card__title"><?php echo htmlspecialchars($assignment['Assignment_Title']); ?></h3><span class="card__meta-tag"><?php echo htmlspecialchars($assignment['Class_Name'] ?? 'General'); ?></span></div>
            <div class="card__body"><p><?php echo nl2br(htmlspecialchars($assignment['Assignment_Description'])); ?></p><div class="due-date"><strong>Due:</strong> <?php echo date('M d, Y @ g:ia', strtotime($assignment['Assignment_DueDate'])); ?></div></div>
            <div class="card__footer">
                <?php if ($is_module_leader): ?>
                    <a href="view_submissions.php?id=<?php echo $assignment['Assignment_ID']; ?>" class="btn">View Submissions (<?php echo $assignment['submission_count']; ?>)</a>
                <?php else: ?>
                    <?php if (array_key_exists($assignment['Assignment_ID'], $user_submissions)): ?>
                        <a href="<?php echo htmlspecialchars($user_submissions[$assignment['Assignment_ID']]); ?>" class="btn btn--success" target="_blank">View Submission</a>
                        <button class="btn submit-work-btn" data-modal-target="submissionModal" data-assignment-id="<?php echo $assignment['Assignment_ID']; ?>">Resubmit</button>
                    <?php else: ?>
                        <button class="btn btn--primary submit-work-btn" data-modal-target="submissionModal" data-assignment-id="<?php echo $assignment['Assignment_ID']; ?>">Submit Work</button>
                    <?php endif; ?>
                <?php endif; ?>
                <button class="btn btn--secondary comments-btn" data-modal-target="commentsModal" data-assignment-id="<?php echo $assignment['Assignment_ID']; ?>">
                    Comments (<?php echo $assignment['comment_count']; ?>)
                </button>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>

<div class="modal" id="submissionModal">
    <div class="modal-content"><span class="close-modal">&times;</span><h2>Submit Assignment</h2><form method="POST" action="assignment.php" enctype="multipart/form-data"><input type="hidden" name="action" value="submit_assignment"><input type="hidden" name="assignment_id" id="modal_assignment_id" value=""><div class="form-group"><label for="submission_file">Upload File:</label><input type="file" id="submission_file" name="submission_file" class="form-input" required></div><div class="form-group"><label for="submission_notes">Notes (Optional):</label><textarea id="submission_notes" name="submission_notes" class="form-input" rows="3"></textarea></div><button type="submit" class="btn btn--primary">Submit</button></form></div>
</div>

<div class="modal" id="commentsModal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Assignment Comments</h2>
        <div id="comments-container" class="comments-container">
            <p>Loading comments...</p>
        </div>
        <div class="comment-form-container">
            <form id="commentForm" method="POST" action="assignment.php">
                <input type="hidden" name="action" value="add_comment">
                <input type="hidden" name="assignment_id" id="modal_comment_assignment_id" value="">
                <div class="form-group">
                    <label for="comment_text">Add a Comment</label>
                    <textarea id="comment_text" name="comment_text" class="form-input" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn--primary">Post Comment</button>
            </form>
        </div>
    </div>
</div>

<?php
require_once 'templates/footer.php';
$conn->close();
?>