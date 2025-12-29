<?php
include 'auth_check.php';
include 'db_connect.php';

$message = '';
$message_type = '';

// Handle POST requests for Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add or Update Coupon
    if (isset($_POST['save_coupon'])) {
        $id = $_POST['id'] ? (int)$_POST['id'] : null;
        $code = strtoupper(trim($_POST['code']));
        $discount = (float)$_POST['discount_percentage'];
        $min_spend = (float)$_POST['min_spend'];
        $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
        $user_id = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($code) || $discount <= 0) {
            $message = "Coupon code and a valid discount percentage are required.";
            $message_type = 'danger';
        } else {
            if ($id) { // Update
                $stmt = $conn->prepare("UPDATE coupons SET code=?, discount_percentage=?, min_spend=?, expiry_date=?, user_id=?, is_active=? WHERE id=?");
                $stmt->bind_param("sddsiii", $code, $discount, $min_spend, $expiry_date, $user_id, $is_active, $id);
                $action = 'updated';
            } else { // Insert
                $stmt = $conn->prepare("INSERT INTO coupons (code, discount_percentage, min_spend, expiry_date, user_id, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sddsii", $code, $discount, $min_spend, $expiry_date, $user_id, $is_active);
                $action = 'created';
            }

            if ($stmt->execute()) {
                header("Location: coupons.php?message=Coupon successfully {$action}.&type=success");
                exit();
            } else {
                $message = "Error: " . $stmt->error;
                $message_type = 'danger';
            }
            $stmt->close();
        }
    }

    // Delete Coupon
    if (isset($_POST['delete_coupon'])) {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM coupons WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            header("Location: coupons.php?message=Coupon deleted successfully.&type=success");
            exit();
        } else {
            header("Location: coupons.php?message=Error deleting coupon: " . $stmt->error . "&type=danger");
            exit();
        }
        $stmt->close();
    }
}

// Handle status toggle
if (isset($_GET['toggle_status'])) {
    $id = (int)$_GET['toggle_status'];
    $stmt = $conn->prepare("UPDATE coupons SET is_active = !is_active WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: coupons.php?message=Status updated.&type=success");
    exit();
}

// --- DATA FETCHING ---
$action = $_GET['action'] ?? 'list';
$edit_coupon = null;

// Fetch a single coupon for editing
if ($action === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM coupons WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_coupon = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Fetch all coupons for the list view
$coupons = [];
$result = $conn->query("SELECT c.*, u.full_name FROM coupons c LEFT JOIN users u ON c.user_id = u.id ORDER BY c.created_at DESC");
if ($result) $coupons = $result->fetch_all(MYSQLI_ASSOC);

// Fetch all users for the dropdown
$users = [];
$result = $conn->query("SELECT id, full_name, email FROM users ORDER BY full_name ASC");
if ($result) $users = $result->fetch_all(MYSQLI_ASSOC);

if(isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type'] ?? 'info');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coupon Management - DUNZO Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css"> <!-- Assuming you have a shared style.css -->
    <style>
        /* Basic styles from dashboard.php for consistency */
        :root { --bg-color: #f4f7fe; --sidebar-bg: #ffffff; --card-bg: #ffffff; --text-color: #1a202c; --text-muted: #718096; --primary: #4361ee; --primary-light: #eef2ff; --border-color: #e2e8f0; --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.05); --border-radius: 12px; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-color); color: var(--text-color); display: flex; }
        .sidebar { width: 260px; background-color: var(--sidebar-bg); height: 100vh; position: fixed; left: 0; top: 0; padding: 20px; display: flex; flex-direction: column; border-right: 1px solid var(--border-color); }
        .sidebar .logo { font-size: 28px; font-weight: 700; padding: 10px; margin-bottom: 30px; }
        .sidebar .logo .yellow { color: #febd69; } .sidebar .logo .green { color: #00a651; }
        .sidebar-menu { list-style: none; padding: 0; margin: 0; flex-grow: 1; }
        .sidebar-menu li a { display: flex; align-items: center; padding: 12px 15px; color: var(--text-muted); font-weight: 500; border-radius: 8px; margin-bottom: 5px; transition: all 0.2s ease; text-decoration: none; }
        .sidebar-menu li a i { font-size: 18px; width: 20px; margin-right: 15px; text-align: center; }
        .sidebar-menu li a.active, .sidebar-menu li a:hover { background-color: var(--primary-light); color: var(--primary); }
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 30px; }
        .panel { background-color: var(--card-bg); padding: 25px; border-radius: var(--border-radius); box-shadow: var(--shadow); }
        .panel-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid var(--border-color); }
        .panel-header h3 { font-size: 18px; font-weight: 600; }
        .form-label { font-weight: 500; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Coupon Management</h2>
            <a href="coupons.php?action=add" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add New Coupon</a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                <?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($action === 'add' || $action === 'edit'): ?>
        <div class="panel">
            <div class="panel-header">
                <h3><?= $action === 'edit' ? 'Edit' : 'Add' ?> Coupon</h3>
                <a href="coupons.php" class="btn btn-secondary btn-sm">Back to List</a>
            </div>
            <form method="POST" action="coupons.php">
                <input type="hidden" name="id" value="<?= $edit_coupon['id'] ?? '' ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="code" class="form-label">Coupon Code</label>
                        <input type="text" class="form-control" id="code" name="code" value="<?= htmlspecialchars($edit_coupon['code'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="discount_percentage" class="form-label">Discount (%)</label>
                        <input type="number" step="0.01" class="form-control" id="discount_percentage" name="discount_percentage" value="<?= htmlspecialchars($edit_coupon['discount_percentage'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="min_spend" class="form-label">Minimum Spend (₹)</label>
                        <input type="number" step="0.01" class="form-control" id="min_spend" name="min_spend" value="<?= htmlspecialchars($edit_coupon['min_spend'] ?? '0') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="expiry_date" class="form-label">Expiry Date</label>
                        <input type="date" class="form-control" id="expiry_date" name="expiry_date" value="<?= htmlspecialchars($edit_coupon['expiry_date'] ?? '') ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="user_id" class="form-label">Assign to Specific User (Optional)</label>
                    <select class="form-select" id="user_id" name="user_id">
                        <option value="">-- Global Coupon --</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>" <?= (isset($edit_coupon['user_id']) && $edit_coupon['user_id'] == $user['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['full_name']) ?> (<?= htmlspecialchars($user['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" <?= (isset($edit_coupon) && !$edit_coupon['is_active']) ? '' : 'checked' ?>>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
                <button type="submit" name="save_coupon" class="btn btn-primary">Save Coupon</button>
            </form>
        </div>
        <?php else: ?>
        <div class="panel">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Discount</th>
                            <th>Min. Spend</th>
                            <th>Expires</th>
                            <th>Assigned To</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($coupons)): ?>
                            <tr><td colspan="7" class="text-center py-4">No coupons found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($coupons as $coupon): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($coupon['code']) ?></strong></td>
                                <td><?= htmlspecialchars($coupon['discount_percentage']) ?>%</td>
                                <td>₹<?= number_format($coupon['min_spend'], 2) ?></td>
                                <td><?= $coupon['expiry_date'] ? date('M d, Y', strtotime($coupon['expiry_date'])) : 'N/A' ?></td>
                                <td><?= htmlspecialchars($coupon['full_name'] ?? 'Global') ?></td>
                                <td>
                                    <?php if ($coupon['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="coupons.php?toggle_status=<?= $coupon['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Toggle Status">
                                        <i class="fas fa-power-off"></i>
                                    </a>
                                    <a href="coupons.php?action=edit&id=<?= $coupon['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="coupons.php" onsubmit="return confirm('Are you sure you want to delete this coupon?');" class="d-inline">
                                        <input type="hidden" name="id" value="<?= $coupon['id'] ?>">
                                        <button type="submit" name="delete_coupon" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>