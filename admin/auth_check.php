<?php
/**
 * This file checks if an admin is logged in.
 * It should be included at the very top of all protected admin pages.
 */

// Start the session if it's not already active.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If the admin_loggedin session variable is not set or not true, redirect to the login page.
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header('Location: index.php');
    exit;
}