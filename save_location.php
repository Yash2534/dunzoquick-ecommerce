<?php
session_start();

/**
 * This script handles saving the selected location to the session
 * and redirecting the user back to their original page.
 */

// Ensure the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and store location data in the session
    // Using htmlspecialchars as a replacement for the deprecated FILTER_SANITIZE_STRING
    $_SESSION['location'] = isset($_POST['full_address']) ? htmlspecialchars($_POST['full_address'], ENT_QUOTES, 'UTF-8') : '';
    
    $quick_location = isset($_POST['quick_location']) ? htmlspecialchars($_POST['quick_location'], ENT_QUOTES, 'UTF-8') : '';
    $area = isset($_POST['area']) ? htmlspecialchars($_POST['area'], ENT_QUOTES, 'UTF-8') : '';
    $_SESSION['short_location'] = !empty($quick_location) ? $quick_location : $area;

    $_SESSION['latitude'] = filter_input(INPUT_POST, 'latitude', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $_SESSION['longitude'] = filter_input(INPUT_POST, 'longitude', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $_SESSION['pincode'] = isset($_POST['pincode']) ? htmlspecialchars($_POST['pincode'], ENT_QUOTES, 'UTF-8') : '';
    $_SESSION['city'] = isset($_POST['city']) ? htmlspecialchars($_POST['city'], ENT_QUOTES, 'UTF-8') : '';

    // Determine the redirect URL (fallback to index.php)
    $redirect_url = $_SESSION['redirect_url'] ?? 'index.php';
    unset($_SESSION['redirect_url']); // Clean up session variable

    // Redirect back to the original page with a success indicator
    header("Location: " . $redirect_url . "?location=updated");
    exit();
}

// If not a POST request, redirect to home
header("Location: index.php");
exit();