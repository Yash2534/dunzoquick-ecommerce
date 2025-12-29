<?php
session_start();
include '../config.php';

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($user_id === 0) {
    header("Location: User.php");
    exit();
}

// Helper function to get a safe image path
function get_image_path($db_path) {
    $default_image = '../assets/img/default-avatar.png';
    if (empty(trim((string)$db_path))) {
        return $default_image;
    }
    return '../' . htmlspecialchars($db_path);
}

// Helper functions for status and role badges
function get_status_badge($status) {
    $map = [
        'active' => 'bg-success-subtle text-success-emphasis',
        'blocked' => 'bg-danger-subtle text-danger-emphasis',
    ];
    return $map[$status] ?? 'bg-secondary-subtle';
}

function get_role_badge($role) {
    $map = [
        'admin' => 'bg-primary-subtle text-primary-emphasis',
        'user' => 'bg-secondary-subtle text-secondary-emphasis',
        'delivery' => 'bg-info-subtle text-info-emphasis',
    ];
    return $map[$role] ?? 'bg-light';
}

// Fetch user data
$stmt = $conn->prepare("
    SELECT u.*, (SELECT COUNT(*) FROM orders WHERE user_id = u.id) AS total_orders
    FROM users u 
    WHERE u.id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['message'] = "User not found.";
    $_SESSION['message_type'] = 'error';
    header("Location: User.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View User - DUNZO Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4f46e5;
            --light-gray: #f3f4f6;
            --border-color: #e5e7eb;
            --text-dark: #111827;
            --text-light: #6b7280;
            --card-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --card-radius: 0.75rem;
        }
        body { background-color: var(--light-gray); font-family: 'Inter', sans-serif; color: var(--text-dark); }
        .navbar { background-color: white !important; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border-bottom: 1px solid var(--border-color); }
        .navbar-brand strong { color: var(--text-dark); }
        .navbar .nav-link { color: var(--text-light); font-weight: 500; }
        .navbar .nav-link.active, .navbar .nav-link:hover { color: var(--primary-color); }
        .btn-outline-light { border-color: #d1d5db; color: #374151; }
        .btn-outline-light:hover { background-color: #f9fafb; color: #374151; }
        .card { border: none; border-radius: var(--card-radius); box-shadow: var(--card-shadow); }
        .card-header { background-color: #fff; border-bottom: 1px solid var(--border-color); border-top-left-radius: var(--card-radius); border-top-right-radius: var(--card-radius); }
        .card-header h4 { font-weight: 600; color: var(--text-dark); }
        .btn-primary { background-color: var(--primary-color); border-color: var(--primary-color); font-weight: 500; }
        .btn-primary:hover { background-color: #4338ca; border-color: #4338ca; }
        .btn-secondary { background-color: #fff; border-color: #d1d5db; color: #374151; font-weight: 500; }
        .btn-secondary:hover { background-color: #f9fafb; }
        .profile-avatar-lg { width: 128px; height: 128px; object-fit: cover; border-radius: 50%; border: 4px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .badge { font-weight: 500; padding: 0.4em 0.7em; font-size: 0.8rem; border-radius: 0.375rem; }
        .details-list dt { font-weight: 500; color: var(--text-light); }
        .details-list dd { font-weight: 500; color: var(--text-dark); }
    </style>
    <style>
        /* Enhanced Styles */
        .stat-card {
            background-color: #f9fafb;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 1rem;
            text-align: center;
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-shadow);
        }
        .stat-card .stat-value { font-size: 1.75rem; font-weight: 700; color: var(--primary-color); line-height: 1.2; }
        .stat-card .stat-label { font-size: 0.875rem; color: var(--text-light); font-weight: 500; }
        .copy-icon { cursor: pointer; color: var(--text-light); transition: color 0.2s ease-in-out; }
        .copy-icon:hover { color: var(--primary-color); }
        .toast-container {
            position: fixed;
            bottom: 1rem;
            right: 1rem;
            z-index: 1056;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white">
    <div class="container-fluid">
        <a class="navbar-brand" href="#"><strong>DUNZO Admin</strong></a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
                <li class="nav-item"><a class="nav-link" href="orders.php">Orders</a></li>
                <li class="nav-item"><a class="nav-link active" href="User.php">Users</a></li>
            </ul>
            <a href="../index.php" target="_blank" class="btn btn-outline-light me-2">View Site</a>
        </div>
    </div>
</nav>

<div class="container my-4">
    <div class="card">
        <div class="card-header bg-white py-3">
            <h4 class="mb-0"><i class="fas fa-user-circle me-2"></i>User Profile</h4>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-4 col-lg-3 text-center">
                    <img src="<?= get_image_path($user['profile_photo']) ?>" alt="<?= htmlspecialchars($user['full_name']) ?>" class="profile-avatar-lg mb-3">
                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($user['full_name']) ?></h5>
                    <div class="text-muted mb-3"><?= htmlspecialchars($user['email']) ?></div>
                    <div>
                        <span class="badge <?= get_role_badge($user['role'] ?? 'user') ?> me-2"><?= ucfirst($user['role'] ?? 'user') ?></span>
                        <span class="badge <?= get_status_badge($user['status'] ?? 'active') ?>"><?= ucfirst($user['status'] ?? 'active') ?></span>
                    </div>
                </div>
                <div class="col-md-8 col-lg-9">
                    <h5 class="mb-3 border-bottom pb-2">Account Details</h5>
                    <dl class="row details-list">
                        <dt class="col-sm-3">User ID</dt>
                        <dd class="col-sm-9">#<?= $user['id'] ?></dd>

                        <dt class="col-sm-3">Full Name</dt>
                        <dd class="col-sm-9"><?= htmlspecialchars($user['full_name']) ?></dd>

                        <dt class="col-sm-3">Email Address</dt>
                        <dd class="col-sm-9">
                            <span id="user-email"><?= htmlspecialchars($user['email']) ?></span>
                            <i class="fas fa-copy ms-2 copy-icon" title="Copy email" onclick="copyToClipboard('#user-email')"></i>
                        </dd>

                        <dt class="col-sm-3">Mobile Number</dt>
                        <dd class="col-sm-9"><?= htmlspecialchars($user['mobile']) ?></dd>

                        <dt class="col-sm-3">Joined On</dt>
                        <dd class="col-sm-9"><?= date('F j, Y, g:i a', strtotime($user['created_at'])) ?></dd>

                        <dt class="col-sm-3">Last Updated</dt>
                        <dd class="col-sm-9"><?= date('F j, Y, g:i a', strtotime($user['updated_at'])) ?></dd>
                    </dl>

                    <h5 class="mt-4 mb-3 border-bottom pb-2">Activity</h5>
                    <div class="row">
                        <div class="col-sm-6 col-md-4 mb-3">
                            <div class="stat-card">
                                <div class="stat-value"><?= $user['total_orders'] ?></div>
                                <div class="stat-label">Total Orders</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-8 mb-3">
                             <div class="stat-card h-100 d-flex flex-column justify-content-center">
                                <div class="stat-label">Last Login</div>
                                <div class="stat-value" style="font-size: 1.2rem; color: var(--text-dark);">
                                    <?php if (!empty($user['last_login'])): ?>
                                        <?= date('F j, Y, g:i a', strtotime($user['last_login'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size: 1.2rem;">Never</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white text-end p-3">
            <a href="User.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Users</a>
            <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-primary"><i class="fas fa-edit me-2"></i>Edit User</a>
        </div>
    </div>
</div>

<div id="toast-container" class="toast-container"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function copyToClipboard(elementSelector) {
    const textToCopy = document.querySelector(elementSelector).innerText;
    navigator.clipboard.writeText(textToCopy).then(() => {
        showToast('Copied to clipboard!');
    }, (err) => {
        console.error('Could not copy text: ', err);
        showToast('Failed to copy', 'error');
    });
}

function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toast-container');
    const toastId = 'toast-' + Date.now();
    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-bg-${type === 'success' ? 'primary' : 'danger'} border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    const toastElement = document.getElementById(toastId);
    const bsToast = new bootstrap.Toast(toastElement, { delay: 3000 });
    bsToast.show();
    toastElement.addEventListener('hidden.bs.toast', () => toastElement.remove());
}
</script>
</body>
</html>