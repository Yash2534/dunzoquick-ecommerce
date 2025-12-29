<?php
// This file should be included at the top of all main admin pages.
// It handles session, auth, db connection, and the top header bar.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'auth_check.php';
include_once 'db_connect.php';

// Fetch current admin details for header
$admin_id = $_SESSION['admin_id'] ?? 0;
$current_admin = null;
if ($admin_id > 0) {
    $stmt_admin = $conn->prepare("SELECT full_name, profile_photo FROM users WHERE id = ? AND role = 'admin'");
    if ($stmt_admin) {
        $stmt_admin->bind_param("i", $admin_id);
        $stmt_admin->execute();
        $result_admin = $stmt_admin->get_result();
        $current_admin = $result_admin->fetch_assoc();
        $stmt_admin->close();
    }
}

// Update session username to be sure it's fresh
if ($current_admin) {
    $_SESSION['admin_username'] = $current_admin['full_name'];
}

// Helper function for avatars
function get_admin_avatar($photo, $name) {
    if ($photo && file_exists('../' . $photo)) {
        return '../' . htmlspecialchars($photo) . '?v=' . time();
    }
    return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=4361ee&color=fff&rounded=true';
}
?>
<div class="header">
    <div class="header-left">
        <h2 class="mb-0 page-title"><?= ucwords(str_replace(['_', '-'], ' ', basename($_SERVER['PHP_SELF'], '.php'))) ?></h2>
    </div>
    <div class="header-right">
        <div class="user-profile-dropdown">
            <div class="user-profile" onclick="this.parentElement.classList.toggle('open')">
                <img src="<?= $current_admin ? get_admin_avatar($current_admin['profile_photo'], $current_admin['full_name']) : get_admin_avatar(null, 'Admin') ?>" alt="Admin User">
                <div class="user-details">
                    <h4><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></h4>
                    <p>Administrator</p>
                </div>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="dropdown-menu" id="userDropdown">
                <a href="logout.php" class="dropdown-item logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>
</div>