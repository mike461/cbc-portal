<?php
// 1. Initialize the session
session_start();

// 2. Unset all of the session variables to clear the user's data
$_SESSION = array();

// 3. If it's desired to kill the session, also delete the session cookie.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Finally, destroy the session completely on the server
session_destroy();

// 5. REDIRECT: Changed from index.html to index.php
header("Location: index.php");
exit();
?>