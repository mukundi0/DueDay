<?php
// Use the core initializer
require_once "../core/init.php";

// If a user is already logged in, redirect them to the home page
if (isset($_SESSION['user_id'])) {
    header('Location: ../home.php');
    exit();
}

$message = "";
$message_type = "error"; // 'error' or 'success' for styling

// Check for a success message from registration
if (isset($_GET['registered']) && $_GET['registered'] === 'success') {
    $message = "Registration successful! Please log in.";
    $message_type = "success";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $message = "Email and password are required.";
    } else {
        $stmt = $conn->prepare("SELECT Users.*, Role.Role_Name FROM Users JOIN Role ON Users.Role_ID = Role.Role_ID WHERE Users.Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['Password'])) {
                if ($user['status'] !== 'active') {
                    $message = "Your account has been deactivated.";
                } else {
                    $_SESSION['user_id'] = $user['User_ID'];
                    $_SESSION['user_fname'] = $user['F_Name'];
                    $_SESSION['role_name'] = $user['Role_Name'];
                    
                    // Award achievement for first login if not already awarded
                    award_achievement($conn, $user['User_ID'], 1); // Assuming '1' is the ID for "First Login"

                    if ($user['Role_Name'] === 'Admin') {
                        header("Location: ../admin/admin_dashboard.php");
                    } else {
                        header("Location: ../home.php");
                    }
                    exit();
                }
            } else {
                $message = "Invalid email or password.";
            }
        } else {
            $message = "Invalid email or password.";
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DueDay</title>
    <link rel="stylesheet" href="auth.css">
    <link rel="icon" href="../assets/icons/dueday.png" type="image/x-icon">
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <h1>Never miss what's due. Ever.</h1>
        </div>
        <div class="right-panel">
            <img src="../assets/icons/dueday.png" alt="DueDay Logo" class="Logo">
            <h1>Welcome Back</h1>
            <form method="post" id="loginForm" action="login.php" novalidate>
                 <?php if ($message): ?>
                    <div class="message message--<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                <div class="form-group full">
                    <label for="email">Email Address:</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div class="form-group full">
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" required>
                    <div id="strengthMessage"></div>
                </div>
                <button type="submit">Sign In</button>
            </form>
            <div class="redirect-link">
                Don’t have an account? <a href="register.php">Sign up here</a>
            </div>
        </div>
    </div>
    <script src="auth.js"></script>
</body>
</html>