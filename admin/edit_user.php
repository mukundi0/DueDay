<?php
require_once 'templates/header.php';

$user_id_to_edit = $_GET['id'] ?? null;
if (!$user_id_to_edit) { header("Location: admin_users.php"); exit(); }
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_role_id'])) {
    $stmt = $conn->prepare("UPDATE Users SET Role_ID = ? WHERE User_ID = ?");
    $stmt->bind_param("ii", $_POST['new_role_id'], $user_id_to_edit);
    if ($stmt->execute()) {
        $message = "User role updated successfully!";
    } else {
        $message = "Error: Could not update role.";
    }
    $stmt->close();
}

$stmt_user = $conn->prepare("SELECT User_ID, F_Name, L_Name, Email, Role_ID FROM Users WHERE User_ID = ?");
$stmt_user->bind_param("i", $user_id_to_edit);
$stmt_user->execute();
$user_to_edit = $stmt_user->get_result()->fetch_assoc();
$stmt_user->close();

$roles = $conn->query("SELECT * FROM Role")->fetch_all(MYSQLI_ASSOC);
$conn->close();

if (!$user_to_edit) { header("Location: admin_users.php"); exit(); }
?>
<head>
    <title>Edit User Role</title>
</head>

<h1 class="page-title">Edit User: <?php echo htmlspecialchars($user_to_edit['F_Name'] . ' ' . $user_to_edit['L_Name']); ?></h1>
<div class="management-section">
    <?php if ($message): ?>
        <p class="message-banner"><?php echo $message; ?></p>
    <?php endif; ?>
    <form method="POST" action="edit_user.php?id=<?php echo $user_to_edit['User_ID']; ?>">
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
        <button type="submit" class="action-button">Save Changes</button>
        <a href="admin_users.php" class="action-button secondary">Back to User List</a>
    </form>
</div>

<?php require_once 'templates/footer.php'; ?>