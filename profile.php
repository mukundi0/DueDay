<?php
require_once 'templates/header.php'; // Includes session, DB connection, and user details

// --- MESSAGE HANDLING ---
$message = '';
$message_type = 'error';

// --- POST REQUEST HANDLING ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- NEW: LOGIC FOR PROFILE PICTURE UPLOAD ---
    if ($action === 'upload_pfp') {
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = mime_content_type($_FILES['profile_picture']['tmp_name']);

            if (in_array($file_type, $allowed_types)) {
                $target_dir = "uploads/pfp/";
                if (!is_dir($target_dir)) { mkdir($target_dir, 0755, true); }
                
                $file_extension = pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION);
                $safe_filename = "user_" . $user_id . "_" . time() . "." . $file_extension;
                $target_file = $target_dir . $safe_filename;

                if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                    // Update database with the new file path
                    $stmt_update_pfp = $conn->prepare("UPDATE Users SET Profile_Picture_Path = ? WHERE User_ID = ?");
                    $stmt_update_pfp->bind_param("si", $target_file, $user_id);
                    if ($stmt_update_pfp->execute()) {
                        $message = "Profile picture updated successfully!";
                        $message_type = "success";
                    } else {
                        $message = "Error updating database record.";
                    }
                    $stmt_update_pfp->close();
                } else {
                    $message = "Sorry, there was an error uploading your file.";
                }
            } else {
                $message = "Invalid file type. Please upload a JPG, PNG, or GIF.";
            }
        } else {
            $message = "No file was uploaded or an error occurred.";
        }
    }

    // --- LOGIC FOR PASSWORD CHANGE ---
    if ($action === 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        $stmt_pass = $conn->prepare("SELECT Password FROM Users WHERE User_ID = ?");
        $stmt_pass->bind_param("i", $user_id);
        $stmt_pass->execute();
        $result = $stmt_pass->get_result()->fetch_assoc();
        $stmt_pass->close();

        if ($result && password_verify($current_password, $result['Password'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 8) {
                    $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_stmt = $conn->prepare("UPDATE Users SET Password = ? WHERE User_ID = ?");
                    $update_stmt->bind_param("si", $hashed_new_password, $user_id);
                    if ($update_stmt->execute()) {
                        $message = "Password updated successfully!";
                        $message_type = "success";
                    } else {
                        $message = "An error occurred while updating the password.";
                    }
                    $update_stmt->close();
                } else {
                    $message = "New password must be at least 8 characters long.";
                }
            } else {
                $message = "New passwords do not match.";
            }
        } else {
            $message = "Incorrect current password.";
        }
    }
}

// --- DATA FETCHING FOR PAGE DISPLAY ---
// Fetch full user details, including the new profile picture path
$stmt_user_details = $conn->prepare("SELECT L_Name, Email, Profile_Picture_Path FROM Users WHERE User_ID = ?");
$stmt_user_details->bind_param("i", $user_id);
$stmt_user_details->execute();
$user_details = $stmt_user_details->get_result()->fetch_assoc();
$stmt_user_details->close();

$graded_assignments = $conn->query(
    "SELECT a.Assignment_Title, asd.Grade, asd.Feedback
     FROM assignment_submission_data asd
     JOIN assignments a ON asd.Assignment_ID = a.Assignment_ID
     WHERE asd.User_ID = $user_id AND asd.Grade IS NOT NULL AND asd.Grade != ''
     ORDER BY a.Assignment_Title ASC"
)->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<div class="form-container" style="max-width: 800px; margin: auto;">
    <h2 class="section-title">My Profile</h2>

    <?php if ($message): ?>
        <p class="message-banner <?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <p><strong>Name:</strong> <?php echo htmlspecialchars($user_fname . ' ' . ($user_details['L_Name'] ?? '')); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($user_details['Email'] ?? ''); ?></p>
    <p><strong>Role:</strong> <?php echo htmlspecialchars($user_role); ?></p>
    <hr style="margin: 30px 0;">

    <h3 class="section-title" style="font-size: 1.5rem;">Change Profile Picture</h3>
    <form method="POST" action="profile.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload_pfp">
        <div class="form-group">
            <label for="profile_picture">Upload New Image:</label>
            <input type="file" id="profile_picture" name="profile_picture" class="form-input" required>
        </div>
        <button type="submit" class="btn btn--primary">Upload Picture</button>
    </form>
    <hr style="margin: 30px 0;">

    <h3 class="section-title" style="font-size: 1.5rem;">Change Password</h3>
    <form method="POST" action="profile.php">
        <input type="hidden" name="action" value="change_password">
        <div class="form-group">
            <label for="current_password">Current Password:</label>
            <input type="password" id="current_password" name="current_password" class="form-input" required>
        </div>
        <div class="form-group">
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" class="form-input" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
        </div>
        <button type="submit" class="btn btn--primary">Update Password</button>
    </form>
    <hr style="margin: 30px 0;">

    <h3 class="section-title" style="font-size: 1.5rem;">My Grades & Feedback</h3>
    <?php if (empty($graded_assignments)): ?>
        <p>You have no graded assignments yet.</p>
    <?php else: ?>
        <div class="management-table">
            <table>
                <thead>
                    <tr>
                        <th>Assignment</th>
                        <th>Grade</th>
                        <th>Feedback</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($graded_assignments as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['Assignment_Title']); ?></td>
                            <td><strong><?php echo htmlspecialchars($item['Grade']); ?></strong></td>
                            <td><?php echo nl2br(htmlspecialchars($item['Feedback'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    
    <div style="margin-top: 30px;">
         <a href="/dueday/auth/logout.php" class="btn btn--danger">Logout</a>
    </div>
</div>

<style>
    /* These styles are just for the messages on this page */
    .message-banner { padding: 15px; margin-bottom: 20px; border-radius: 5px; }
    .message-banner.success { background-color: #d4edda; color: #155724; }
    .message-banner.error { background-color: #f8d7da; color: #721c24; }
</style>

<?php require_once 'templates/footer.php'; ?>