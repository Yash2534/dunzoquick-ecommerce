<?php
session_start();
include '../config.php';

// --- Action Handling (Block/Unblock/Delete) ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $user_id = (int)$_GET['id'];
    $admin_id = $_SESSION['user_id'] ?? 0; // Assuming admin ID is in session

    // Prevent admin from performing actions on their own account
    if ($user_id !== $admin_id) {
        if ($action === 'block') {
            $stmt = $conn->prepare("UPDATE users SET status = 'blocked' WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $_SESSION['message'] = "User blocked successfully.";
        } elseif ($action === 'unblock') {
            $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $_SESSION['message'] = "User unblocked successfully.";
        } elseif ($action === 'delete') {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $_SESSION['message'] = "User deleted successfully.";
        }
    } else {
        $_SESSION['message_type'] = 'error';
        $_SESSION['message'] = "You cannot perform this action on your own account.";
    }
    
    // Redirect to clean the URL and show message
    header("Location: User.php");
    exit();
}

// --- Search, Filter, and Pagination Logic ---
$search = $_GET['search'] ?? '';
$filter_role = $_GET['role'] ?? '';
$filter_status = $_GET['status'] ?? '';

$limit = 10; // Users per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$where_clauses = [];
$params = [];
$param_types = '';

if (!empty($search)) {
    $where_clauses[] = "(full_name LIKE ? OR email LIKE ? OR mobile LIKE ?)";
    $search_param = "%{$search}%";
    array_push($params, $search_param, $search_param, $search_param);
    $param_types .= 'sss';
}
if (!empty($filter_role)) {
    $where_clauses[] = "role = ?";
    $params[] = $filter_role;
    $param_types .= 's';
}
if (!empty($filter_status)) {
    $where_clauses[] = "status = ?";
    $params[] = $filter_status;
    $param_types .= 's';
}

$where_sql = "";
if (!empty($where_clauses)) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM users $where_sql";
$stmt_count = $conn->prepare($count_sql);
if (!empty($params)) {
    $stmt_count->bind_param($param_types, ...$params);
}
$stmt_count->execute();
$total_users = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_users / $limit);

// Fetch users for the current page
$sql = "SELECT id, full_name, email, mobile, created_at, profile_photo, role, status 
        FROM users 
        $where_sql
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$param_types .= 'ii';

$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);

$current_admin_id = $_SESSION['user_id'] ?? 0; // Get current admin ID

// Helper function to get a safe image path
function get_image_path($db_path, $full_name) {
    if (empty(trim((string)$db_path))) {
        // Generate a default avatar with initials if no photo exists
        return 'https://ui-avatars.com/api/?name=' . urlencode($full_name) . '&background=e5e7eb&color=6b7280&bold=true';
    }
    // Path in DB is like 'uploads/profile_photos/...'
    // We are in /admin, so we need to go up one level to the root.
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - DUNZO Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        :root {
            --bs-primary-rgb: 79, 70, 229;
            --bs-secondary-rgb: 107, 114, 128;
            --bs-light-rgb: 249, 250, 251;
            --bs-dark-rgb: 17, 24, 39;
            --bs-font-sans-serif: 'Inter', sans-serif;
            --bs-body-bg: #f9fafb;
            --bs-body-color: #374151;
            --bs-border-color: #e5e7eb;
            --bs-card-border-radius: 0.75rem;
            --bs-card-box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --bs-card-cap-bg: #fff;
        }
        .navbar { box-shadow: var(--bs-card-box-shadow); }
        .navbar-brand .logo { font-size: 24px; font-weight: 700; letter-spacing: -0.5px; }
        .navbar-brand .logo .yellow { color: #febd69; }
        .navbar-brand .logo .green { color: #00a651; }
        .nav-link.active { color: var(--bs-primary) !important; font-weight: 600; }
        .page-header { margin-bottom: 1.5rem; }
        .page-header h1 { font-weight: 700; color: var(--bs-dark); }
        .card { border: none; }
        .card-header { padding: 1rem 1.5rem; }
        .card-header h4 { font-weight: 600; color: var(--bs-dark); font-size: 1.125rem; }
        .table th { color: #6b7280; font-weight: 500; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; border-bottom-width: 1px; }
        .table td { vertical-align: middle; }
        .table tbody tr:last-child td { border-bottom: none; }
        .profile-avatar { width: 40px; height: 40px; object-fit: cover; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 0 0 1px var(--bs-border-color); }
        .action-btn { color: #6b7280; text-decoration: none; transition: color 0.2s ease; font-size: 1rem; padding: 0.25rem 0.5rem; }
        .action-btn:hover { color: var(--bs-primary); }
        .action-btn.delete:hover { color: var(--bs-danger); }
        .pagination .page-link { border-radius: 0.375rem !important; margin: 0 2px; border-color: var(--bs-border-color); color: #6b7280; }
        .pagination .page-item.active .page-link { background-color: var(--bs-primary); border-color: var(--bs-primary); }
        .form-control, .form-select { font-size: 0.9rem; border-radius: 0.5rem; }
        .btn-primary { --bs-btn-bg: var(--bs-primary); --bs-btn-border-color: var(--bs-primary); --bs-btn-hover-bg: #4338ca; --bs-btn-hover-border-color: #4338ca; }
        .btn-outline-secondary { --bs-btn-color: #4b5563; --bs-btn-border-color: #d1d5db; --bs-btn-hover-bg: #f9fafb; }
        .btn-group .btn.active { background-color: var(--bs-primary); color: #fff; border-color: var(--bs-primary); }
        #filter-form .input-group {
            border-radius: 50px;
            border: 1px solid var(--bs-border-color);
            overflow: hidden;
        }
        #filter-form .input-group .form-control,
        #filter-form .input-group .input-group-text {
            border: none;
        }
        #filter-form .input-group .form-control:focus {
            box-shadow: none;
        }

        /* Grid View Styles */
        .user-grid {
            display: none; /* Hidden by default */
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .user-card {
            border-radius: var(--bs-card-border-radius);
            box-shadow: var(--bs-card-box-shadow);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: none;
        }
        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }
        .user-card .profile-avatar-lg { width: 80px; height: 80px; margin-bottom: 1rem; border-radius: 50%; object-fit: cover; border: 3px solid #fff; box-shadow: 0 0 0 1px var(--bs-border-color); }
        .user-card .dropdown-menu { min-width: auto; }
        .user-card .dropdown-item { font-size: 0.875rem; }
        .user-card .card-footer { background-color: #f9fafb; }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg bg-white">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">
            <div class="logo"><span class="yellow">dun</span><span class="green">zo</span> <span class="text-secondary fw-normal">Admin</span></div>
        </a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
                <li class="nav-item"><a class="nav-link" href="orders.php">Orders</a></li>
                <li class="nav-item"><a class="nav-link active" href="User.php">Users</a></li>
            </ul>
            <a href="../index.php" target="_blank" class="btn btn-sm btn-outline-secondary me-2"><i class="fas fa-globe me-1"></i> View Site</a>
        </div>
    </div>
</nav>

<div class="container-fluid p-4">
    <div class="page-header d-flex justify-content-between align-items-center">
        <h1>Manage Users</h1>
        <a href="add_user.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i> Add New User</a>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?php echo $_SESSION['message_type'] ?? 'success'; ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php 
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    endif; 
    ?>

    <div class="card">
        <div class="card-header">
            <!-- Search and Filter Form -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <form method="GET" action="User.php" id="filter-form" class="d-flex flex-wrap gap-2">
                    <div class="input-group" style="max-width: 300px;">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search users..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <select name="role" class="form-select" style="width: 150px;">
                        <option value="">All Roles</option>
                        <option value="user" <?= $filter_role == 'user' ? 'selected' : '' ?>>User</option>
                        <option value="admin" <?= $filter_role == 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                    <select name="status" class="form-select" style="width: 150px;">
                        <option value="">All Statuses</option>
                        <option value="active" <?= $filter_status == 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="blocked" <?= $filter_status == 'blocked' ? 'selected' : '' ?>>Blocked</option>
                    </select>
                    <button type="submit" class="btn btn-outline-secondary">Filter</button>
                    <?php if(!empty($search) || !empty($filter_role) || !empty($filter_status)): ?>
                        <a href="User.php" class="btn btn-link text-secondary text-decoration-none">Clear</a>
                    <?php endif; ?>
                </form>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-secondary" id="list-view-btn" title="List View"><i class="fas fa-list"></i></button>
                    <button type="button" class="btn btn-outline-secondary" id="grid-view-btn" title="Grid View"><i class="fas fa-th-large"></i></button>
                </div>
            </div>
        </div>
        <div class="card-body p-0 p-md-3">
            <div class="table-responsive" id="user-list-view">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <img src="<?= get_image_path($user['profile_photo'], $user['full_name']) ?>" alt="<?= htmlspecialchars($user['full_name']) ?>" class="profile-avatar me-3">
                                            <div>
                                                <div class="fw-bold"><?= htmlspecialchars($user['full_name']) ?></div>
                                                <div class="text-muted small"><?= htmlspecialchars($user['email']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge rounded-pill <?= get_role_badge($user['role']) ?>"><?= ucfirst($user['role']) ?></span></td>
                                    <td><span class="badge rounded-pill <?= get_status_badge($user['status']) ?>"><?= ucfirst($user['status']) ?></span></td>
                                    <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                    <td class="text-end pe-4">
                                        <a href="view_user.php?id=<?= $user['id'] ?>" class="action-btn" title="View"><i class="fas fa-eye"></i></a>
                                        <?php if ($user['status'] == 'active'): ?>
                                            <a href="?action=block&id=<?= $user['id'] ?>" class="action-btn" title="Block" onclick="return confirm('Are you sure you want to block this user?');"><i class="fas fa-user-lock"></i></a>
                                        <?php else: ?>
                                            <a href="?action=unblock&id=<?= $user['id'] ?>" class="action-btn" title="Unblock" onclick="return confirm('Are you sure you want to unblock this user?');"><i class="fas fa-user-check"></i></a>
                                        <?php endif; ?>
                                        <a href="?action=delete&id=<?= $user['id'] ?>" class="action-btn delete" title="Delete" onclick="return confirm('Are you sure you want to permanently delete this user? This action cannot be undone.');"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="user-grid" id="user-grid-view">
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <div class="card user-card">
                            <div class="card-body text-center">
                                <div class="dropdown position-absolute top-0 end-0 mt-2 me-2">
                                    <button class="btn btn-sm btn-light btn-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-h"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="view_user.php?id=<?= $user['id'] ?>"><i class="fas fa-eye fa-fw me-2"></i>View</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <?php if ($user['status'] == 'active'): ?>
                                            <li><a class="dropdown-item" href="?action=block&id=<?= $user['id'] ?>" onclick="return confirm('Are you sure you want to block this user?');"><i class="fas fa-user-lock fa-fw me-2"></i>Block</a></li>
                                        <?php else: ?>
                                            <li><a class="dropdown-item" href="?action=unblock&id=<?= $user['id'] ?>" onclick="return confirm('Are you sure you want to unblock this user?');"><i class="fas fa-user-check fa-fw me-2"></i>Unblock</a></li>
                                        <?php endif; ?>
                                        <li><a class="dropdown-item text-danger" href="?action=delete&id=<?= $user['id'] ?>" onclick="return confirm('Are you sure you want to permanently delete this user? This action cannot be undone.');"><i class="fas fa-trash fa-fw me-2"></i>Delete</a></li>
                                    </ul>
                                </div>
                                <img src="<?= get_image_path($user['profile_photo'], $user['full_name']) ?>" alt="<?= htmlspecialchars($user['full_name']) ?>" class="profile-avatar-lg">
                                <h5 class="fw-bold mt-3 mb-0"><?= htmlspecialchars($user['full_name']) ?></h5>
                                <p class="text-muted small mb-2"><?= htmlspecialchars($user['email']) ?></p>
                                <div>
                                    <span class="badge rounded-pill <?= get_role_badge($user['role']) ?> me-1"><?= ucfirst($user['role']) ?></span>
                                    <span class="badge rounded-pill <?= get_status_badge($user['status']) ?>"><?= ucfirst($user['status']) ?></span>
                                </div>
                            </div>
                            <div class="card-footer text-muted small d-flex justify-content-between">
                                <span>Joined: <?= date('M Y', strtotime($user['created_at'])) ?></span>
                                <span><i class="fas fa-mobile-alt me-1"></i> <?= htmlspecialchars($user['mobile']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if (empty($users)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-user-slash fa-3x text-light mb-3"></i>
                    <h5 class="text-secondary">No users found.</h5>
                    <p class="text-muted small">Try adjusting your search or filters, or <a href="add_user.php">add a new user</a>.</p>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($total_pages > 1): ?>
        <div class="card-footer">
            <nav class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing <?= count($users) ?> of <?= $total_users ?> users
                </div>
                <ul class="pagination mb-0">
                    <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&role=<?= $filter_role ?>&status=<?= $filter_status ?>">Prev</a></li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&role=<?= $filter_role ?>&status=<?= $filter_status ?>"><?= $i ?></a></li>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&role=<?= $filter_role ?>&status=<?= $filter_status ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const listViewBtn = document.getElementById('list-view-btn');
    const gridViewBtn = document.getElementById('grid-view-btn');
    const userListView = document.getElementById('user-list-view');
    const userGridView = document.getElementById('user-grid-view');

    function setView(view) {
        if (view === 'grid') {
            userListView.style.display = 'none';
            userGridView.style.display = 'grid'; // Changed to grid
            listViewBtn.classList.remove('active');
            gridViewBtn.classList.add('active');
            localStorage.setItem('userView', 'grid');
        } else {
            userListView.style.display = 'block';
            userGridView.style.display = 'none'; // Changed to none
            gridViewBtn.classList.remove('active');
            listViewBtn.classList.add('active');
            localStorage.setItem('userView', 'list');
        }
    }

    listViewBtn.addEventListener('click', () => setView('list'));
    gridViewBtn.addEventListener('click', () => setView('grid'));

    // Set initial view from localStorage or default to list
    const savedView = localStorage.getItem('userView') || 'list';
    setView(savedView);
    
    // Auto-submit filter form on change for dropdowns
    document.querySelectorAll('#filter-form select').forEach(select => {
        select.addEventListener('change', () => {
            document.getElementById('filter-form').submit();
        });
    });
});
</script>
</body>
</html>