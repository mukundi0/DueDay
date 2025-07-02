<?php
require_once 'templates/header.php';
$is_module_leader = ($user_role === 'Module Leader');

// --- POST REQUEST HANDLING ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create_poll' && $is_module_leader) {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO Polls (Poll_Title, Poll_Description, Class_ID, Expires_At, Status, Is_Anonymous, Allow_Multiple_Choices) VALUES (?, ?, ?, ?, 'Active', ?, ?)");
            $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;
            $allow_multiple = isset($_POST['allow_multiple']) ? 1 : 0;
            $stmt->bind_param("ssisii", $_POST['poll_title'], $_POST['poll_description'], $_POST['class_id'], $_POST['poll_expiry'], $is_anonymous, $allow_multiple);
            $stmt->execute();
            $poll_id = $conn->insert_id;
            $stmt_options = $conn->prepare("INSERT INTO Poll_Options (Poll_ID, Option_Text) VALUES (?, ?)");
            foreach ($_POST['options'] as $option_text) { if (!empty(trim($option_text))) { $stmt_options->bind_param("is", $poll_id, $option_text); $stmt_options->execute(); } }
            $conn->commit();
        } catch (mysqli_sql_exception $e) { $conn->rollback(); error_log($e->getMessage()); }
        header("Location: poll.php?created=success");
        exit();
    }
    // This logic already supports changing a vote because it deletes the old one first.
    if ($action === 'submit_vote') {
        $poll_id = $_POST['poll_id'];
        $selected_options = $_POST['option'] ?? [];
        if (!is_array($selected_options)) { $selected_options = [$selected_options]; }
        if (!empty($selected_options)) {
            $stmt_delete = $conn->prepare("DELETE FROM Poll_Data WHERE User_ID = ? AND Poll_ID = ?");
            $stmt_delete->bind_param("ii", $user_id, $poll_id);
            $stmt_delete->execute();
            $stmt_insert = $conn->prepare("INSERT INTO Poll_Data (User_ID, Poll_ID, Option_ID) VALUES (?, ?, ?)");
            foreach ($selected_options as $option_id) { $stmt_insert->bind_param("iii", $user_id, $poll_id, $option_id); $stmt_insert->execute(); }
            // Only award achievement on the first vote
            award_achievement($conn, $user_id, 3);
        }
        header("Location: poll.php?voted=true");
        exit();
    }
}

// --- DATA FETCHING ---
$all_classes = $conn->query("SELECT * FROM Classes ORDER BY Class_Name")->fetch_all(MYSQLI_ASSOC);
$user_voted_polls = [];
$stmt_votes = $conn->prepare("SELECT DISTINCT Poll_ID FROM Poll_Data WHERE User_ID = ?");
$stmt_votes->bind_param("i", $user_id);
$stmt_votes->execute();
$result_votes = $stmt_votes->get_result();
while($row = $result_votes->fetch_assoc()) { $user_voted_polls[] = $row['Poll_ID']; }
$polls_data = [];
$sql = "SELECT p.*, po.Option_ID, po.Option_Text, c.Class_Name FROM Polls p JOIN Poll_Options po ON p.Poll_ID = po.Poll_ID LEFT JOIN Classes c ON p.Class_ID = c.Class_ID WHERE p.Expires_At > NOW() OR p.Poll_ID IN (SELECT Poll_ID FROM Poll_Data WHERE User_ID = ?) ORDER BY p.Expires_At DESC, p.Poll_ID, po.Option_ID";
$stmt_polls = $conn->prepare($sql);
$stmt_polls->bind_param("i", $user_id);
$stmt_polls->execute();
$result = $stmt_polls->get_result();
while ($row = $result->fetch_assoc()) {
    $poll_id = $row['Poll_ID'];
    if (!isset($polls_data[$poll_id])) { $polls_data[$poll_id] = [ 'title' => $row['Poll_Title'], 'description' => $row['Poll_Description'], 'class_name' => $row['Class_Name'], 'expires_at' => new DateTime($row['Expires_At']), 'is_active' => (new DateTime() < new DateTime($row['Expires_At'])), 'allow_multiple' => (bool)$row['Allow_Multiple_Choices'], 'options' => [] ]; }
    $polls_data[$poll_id]['options'][] = ['id' => $row['Option_ID'], 'text' => $row['Option_Text']];
}
?>

<?php if ($is_module_leader): ?>
<div class="page-actions">
    <button class="btn" id="viewBtn">View Polls</button>
    <button class="btn" id="createBtn">Create New</button>
</div>
<?php endif; ?>

<div id="createSection">
    <?php if ($is_module_leader): ?>
    <div class="form-container">
        <h2 class="section-title">Create New Poll</h2>
        <form class="poll-form" method="POST" action="poll.php">
            <input type="hidden" name="action" value="create_poll">
            
            <div class="form-group"><label for="pollTitle">Poll Title:</label><input type="text" id="pollTitle" name="poll_title" class="form-input" placeholder="Enter poll title" required></div>
            <div class="form-group"><label for="pollDescription">Description (Optional):</label><textarea id="pollDescription" name="poll_description" class="form-input" placeholder="Enter description" rows="3"></textarea></div>
            <div class="form-group"><label for="class_id">Course/Module:</label><select id="class_id" name="class_id" class="form-input"><option value="">-- Select a course --</option><?php foreach($all_classes as $class): ?><option value="<?php echo $class['Class_ID']; ?>"><?php echo htmlspecialchars($class['Class_Name']); ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label for="pollExpiry">Expiry Date/Time:</label><input type="datetime-local" id="pollExpiry" name="poll_expiry" class="form-input" required></div>
            
            <div class="form-group">
                <label>Poll Options:</label>
                <div id="pollOptionsContainer">
                    <div class="poll-option-item"><input type="text" name="options[]" class="form-input" placeholder="Option 1" required><button type="button" class="btn remove-option-btn">&times;</button></div>
                    <div class="poll-option-item"><input type="text" name="options[]" class="form-input" placeholder="Option 2" required><button type="button" class="btn remove-option-btn">&times;</button></div>
                </div>
                <button type="button" id="addPollOptionBtn" class="btn btn--secondary" style="margin-top: 10px;">+ Add Option</button>
            </div>

            <div class="form-group checkbox-group"><input type="checkbox" id="anonymousPoll" name="is_anonymous" value="1"><label for="anonymousPoll">Anonymous Poll</label></div>
            <div class="form-group checkbox-group"><input type="checkbox" id="multipleChoices" name="allow_multiple" value="1"><label for="multipleChoices">Allow multiple choices</label></div>
            
            <button type="submit" class="btn btn--primary" style="width: 100%; padding: 12px;">Publish Poll</button>
        </form>
    </div>
    <?php endif; ?>
</div>


<div id="viewSection" class="polls-view-section">
    <h2 class="section-title">Current Polls</h2>
    <?php if (empty($polls_data)): ?><p>No polls are currently active.</p><?php else: foreach ($polls_data as $poll_id => $poll): $user_has_voted = in_array($poll_id, $user_voted_polls); ?>
            <div class="card poll-card--<?php echo $poll['is_active'] ? 'active' : 'closed'; ?>">
                <form method="POST" action="poll.php">
                    <input type="hidden" name="action" value="submit_vote"><input type="hidden" name="poll_id" value="<?php echo $poll_id; ?>">
                    <div class="card__header"><h3 class="card__title"><?php echo htmlspecialchars($poll['title']); ?></h3><?php if($poll['class_name']): ?><span class="card__meta-tag"><?php echo htmlspecialchars($poll['class_name']); ?></span><?php endif; ?></div>
                    <div class="poll-body">
                        <div class="poll-options">
                            <?php foreach ($poll['options'] as $option): ?>
                                <div class="poll-option">
                                    <input type="<?php echo $poll['allow_multiple'] ? 'checkbox' : 'radio'; ?>" name="option<?php echo $poll['allow_multiple'] ? '[]' : ''; ?>" value="<?php echo $option['id']; ?>" id="opt_<?php echo $option['id']; ?>" <?php echo !$poll['is_active'] ? 'disabled' : ''; ?>>
                                    <label for="opt_<?php echo $option['id']; ?>"><?php echo htmlspecialchars($option['text']); ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="card__footer">
                        <?php if ($poll['is_active']): ?>
                            <button type="submit" class="btn btn--primary"><?php echo $user_has_voted ? 'Change Vote' : 'Submit Vote'; ?></button>
                        <?php endif; ?>
                        <button type="button" class="btn btn--secondary view-results-btn" data-modal-target="resultsModal" data-poll-id="<?php echo $poll_id; ?>">View Results</button>
                    </div>
                </form>
            </div>
    <?php endforeach; endif; ?>
</div>

<div class="modal" id="resultsModal"><div class="modal-content"><span class="close-modal">&times;</span><h2 id="resultsTitle" class="section-title">Poll Results</h2><div class="results-container" id="resultsContainer"></div></div></div>

<?php 
require_once 'templates/footer.php'; 
$conn->close();
?>