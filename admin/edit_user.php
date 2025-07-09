<?php
require_once 'templates/header.php';

$user_id_to_edit = $_GET['id'] ?? null;
if (!$user_id_to_edit) { header("Location: admin_users.php"); exit(); }

$message = '';
$message_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Action to update the user's role
    if ($action === 'update_role') {
        $new_role_id = $_POST['new_role_id'];
        $role_check = $conn->query("SELECT Role_ID FROM role WHERE Role_ID = $new_role_id")->num_rows;

        if ($role_check > 0) {
            $stmt = $conn->prepare("UPDATE Users SET Role_ID = ? WHERE User_ID = ?");
            $stmt->bind_param("ii", $new_role_id, $user_id_to_edit);
            if ($stmt->execute()) {
                $message = "User role updated successfully!";
            } else {
                $message = "Error: Could not update role.";
                $message_type = 'error';
            }
            $stmt->close();
        } else {
            $message = "Error: The selected role does not exist.";
            $message_type = 'error';
        }
    }

    // Action for an admin to override a user's password
    if ($action === 'admin_change_password') {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 8) {
                $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE Users SET Password = ? WHERE User_ID = ?");
                $update_stmt->bind_param("si", $hashed_new_password, $user_id_to_edit);
                if ($update_stmt->execute()) {
                    $message = "User password has been updated successfully!";
                } else {
                    $message = "An error occurred while updating the password.";
                    $message_type = 'error';
                }
                $update_stmt->close();
            } else {
                $message = "Password must be at least 8 characters long.";
                $message_type = 'error';
            }
        } else {
            $message = "Passwords do not match.";
            $message_type = 'error';
        }
    }
}

$stmt_user = $conn->prepare("SELECT User_ID, F_Name, L_Name, Email, Role_ID FROM Users WHERE User_ID = ?");
$stmt_user->bind_param("i", $user_id_to_edit);
$stmt_user->execute();
$user_to_edit = $stmt_user->get_result()->fetch_assoc();
$stmt_user->close();

if (!$user_to_edit) { header("Location: admin_users.php"); exit(); }

$roles = $conn->query("SELECT * FROM Role")->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<head>
    <title>Edit User</title>
</head>

<h1 class="page-title">Edit User: <?php echo htmlspecialchars($user_to_edit['F_Name'] . ' ' . $user_to_edit['L_Name']); ?></h1>

<div class="management-section">
    <?php if ($message): ?>
        <p class="message-banner" style="padding: 15px; margin-bottom: 20px; border-radius: 5px; background-color: <?php echo $message_type === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $message_type === 'success' ? '#155724' : '#721c24'; ?>;">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="edit_user.php?id=<?php echo $user_to_edit['User_ID']; ?>">
        <input type="hidden" name="action" value="update_role">
        <h3>User Details</h3>
        <div class="form-group">
            <label>User Email:</label>
            <p><?php echo htmlspecialchars($user_to_edit['Email']); ?></p>
        </div>
        <div class="form-group">
            <label for="new_role_id">Change Role To:</label>
            <select name="new_role_id" id="new_role_id" class="form-input">
                <?php foreach ($roles as $role): ?>
                    <option value="<?php echo $role['Role_ID']; ?>" <?php if ($role['Role_ID'] == $user_to_edit['Role_ID']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($role['Role_Name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="page-actions">
            <button type="submit" class="action-button">Save Changes</button>
            <?php if ($user_to_edit['User_ID'] != $_SESSION['user_id']): // Admin can't impersonate self ?>
                <a href="impersonate.php?id=<?php echo $user_to_edit['User_ID']; ?>" class="action-button" style="background-color: #4f46e5; text-decoration:none;">Impersonate User</a>
            <?php endif; ?>
        </div>
    </form>
    
    <hr style="margin: 30px 0;">

    <form method="POST" action="edit_user.php?id=<?php echo $user_to_edit['User_ID']; ?>">
        <input type="hidden" name="action" value="admin_change_password">
        <h3>Override Password</h3>
        <p style="font-size: 0.9em; color: var(--subtle-text); margin-bottom: 15px;">Force a password change for this user. The user's current password is not required.</p>
        <div class="form-group">
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" class="form-input" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
        </div>
        <button type="submit" class="action-button">Update Password</button>
    </form>

    <hr style="margin: 30px 0;">

    <div class="page-actions">
        <a href="admin_users.php" class="action-button secondary" style="text-decoration:none;">Back to User List</a>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>