<?php
// Use the core initializer
require_once "../core/init.php";

// --- NEW: DEFINE YOUR SECRET INVITATION CODES HERE ---
// You can change these codes to anything you want. Keep them secret.
define('MODULE_LEADER_CODE', 'ML-SECRET');
define('EVENT_COORDINATOR_CODE', 'EC-SECRET');

// If a user is already logged in, redirect them to the home page
if (isset($_SESSION['user_id'])) {
    header('Location: ../home.php');
    exit();
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // --- Data Validation ---
    $fname = trim($_POST['first_name'] ?? '');
    $lname = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role_id = $_POST['role'] ?? '';
    $invitation_code = trim($_POST['invitation_code'] ?? ''); // New field

    if (empty($fname)) { $errors[] = "First name is required."; }
    if (empty($lname)) { $errors[] = "Last name is required."; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "A valid email is required."; }
    if (empty($password)) { $errors[] = "Password is required."; }
    if (strlen($password) < 8) { $errors[] = "Password must be at least 8 characters long."; }
    if ($password !== $confirm_password) { $errors[] = "Passwords do not match."; }
    if (empty($role_id)) { $errors[] = "Please select a role."; }

    // --- NEW: INVITATION CODE VALIDATION ---
    // Role ID 1 = Module Leader, Role ID 3 = Event Coordinator (check your DB if different)
    if ($role_id == 1 && $invitation_code !== MODULE_LEADER_CODE) {
        $errors[] = "Invalid invitation code for Module Leader role.";
    }
    if ($role_id == 3 && $invitation_code !== EVENT_COORDINATOR_CODE) {
        $errors[] = "Invalid invitation code for Event Coordinator role.";
    }
    // No code is needed for Role ID 2 (Student)

    // --- Database Interaction ---
    if (empty($errors)) {
        $stmt_check = $conn->prepare("SELECT User_ID FROM Users WHERE Email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $errors[] = "This email address is already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt_insert = $conn->prepare("INSERT INTO Users (F_Name, L_Name, Email, Password, Role_ID) VALUES (?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("ssssi", $fname, $lname, $email, $hashedPassword, $role_id);

            if ($stmt_insert->execute()) {
                header("Location: login.php?registered=success");
                exit();
            } else {
                $errors[] = "An unexpected error occurred. Please try again.";
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - DueDay</title>
    <link rel="stylesheet" href="auth.css">
    <link rel="icon" href="../assets/icons/dueday.png" type="image/x-icon">
</head>
<body>
<div class="container">
    <div class="left-panel">
        <h1>Join the Community, Stay on Track.</h1>
    </div>
    <div class="right-panel">
        <img src="../assets/icons/dueday.png" alt="DueDay Logo" class="Logo">
        <h1>Create Account</h1>
        <form method="post" id="registerForm" action="register.php" novalidate>
            <?php if (!empty($errors)): ?>
                <div class="message message--error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                </div>
            </div>
            <div class="form-group full">
                <label for="email">Email Address:</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" required>
                    <div id="strengthMessage"></div>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                </div>
            </div>
            <div class="form-group full">
                <label for="role">Select Your Role:</label>
                <select name="role" id="role" required>
                    <option value="" disabled selected>-- Choose a role --</option>
                    <option value="2">Student</option> 
                    <option value="1">Module Leader</option> 
                    <option value="3">Event Coordinator</option>
                </select>
            </div>
            
            <div class="form-group full" id="invitationCodeGroup" style="display: none;">
                <label for="invitation_code">Invitation Code:</label>
                <input type="text" name="invitation_code" id="invitation_code" placeholder="Required for non-student roles">
            </div>

            <button type="submit">Register</button>
        </form>
        <div class="redirect-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</div>
<script src="auth.js"></script>
</body>
</html>