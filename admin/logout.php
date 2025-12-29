<?php
/**
 * This script handles the admin logout process.
 */

// Start the session to access it.
session_start();

// Unset all of the session variables.
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get(option: "session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(name: session_name(), value: '', expires_or_options: time() - 42000,
        path: $params["path"], domain: $params["domain"],
        secure: $params["secure"], httponly: $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

// Redirect to the main site's homepage after logout.
header("Location: ../index.php");
exit;