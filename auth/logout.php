<?php
// It's good practice to start the session to ensure we can destroy it.
session_start();

// This line immediately clears all the data associated with the current session. For example, user_id, user_fname, and role_name are all removed.         
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

// Redirect the user back to the login page.
header("Location: login.php");
exit();
?>