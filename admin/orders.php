<?php
include 'auth_check.php';
include 'db_connect.php';

// --- Define possible statuses and their colors ---
$statuses = ['pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered'];
$status_colors = [
    'pending' => 'warning', 'confirmed' => 'info', 'preparing' => 'info',
    'out_for_delivery' => 'primary', 'delivered' => 'success', 'cancelled' => 'danger'
];

// --- Handle Status Updates ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Build the redirect URL to preserve filters from the current page's query string
    $redirect_query = [];
    if (!empty($_GET['status'])) { $redirect_query['status'] = $_GET['status']; }
    if (!empty($_GET['search'])) { $redirect_query['search'] = $_GET['search']; }
    $redirect_url = 'orders.php' . (empty($redirect_query) ? '' : '?' . http_build_query($redirect_query));

    // Single status update via dropdown
    if (isset($_POST['update_status'])) {
        $order_id = (int)$_POST['order_id'];
        $new_status = $_POST['status'];

        if (in_array($new_status, $statuses)) {
            $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $new_status, $order_id);
            $stmt->execute();
            // Redirect to avoid form resubmission and reflect changes
            header("Location: " . $redirect_url);
            exit();
        }
    }

    // Bulk status update
    if (isset($_POST['apply_bulk_action']) && !empty($_POST['order_ids']) && !empty($_POST['bulk_status'])) {
        $bulk_status = $_POST['bulk_status'];
        $order_ids = array_map('intval', $_POST['order_ids']); // Sanitize input
        
        if (in_array($bulk_status, $statuses) && !empty($order_ids)) {
            $placeholders = implode(',', array_fill(0, count($order_ids), '?'));
            $types = str_repeat('i', count($order_ids));
            
            $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id IN ($placeholders)");
            $stmt->bind_param("s" . $types, $bulk_status, ...$order_ids);
            $stmt->execute();
            header("Location: " . $redirect_url);
            exit();
        }
    }
}

// --- Fetch Orders with Filtering and Searching ---
$filter_status = $_GET['status'] ?? 'all';
$search_query = $_GET['search'] ?? '';

$sql = "SELECT o.id, o.order_number, o.total_amount, o.status, o.created_at, u.full_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.id";

$where_clauses = [];
$params = [];
$types = '';

if ($filter_status !== 'all' && in_array($filter_status, $statuses)) {
    $where_clauses[] = "o.status = ?";
    $params[] = $filter_status;
    $types .= 's';
}

if (!empty($search_query)) {
    $where_clauses[] = "(o.order_number LIKE ? OR u.full_name LIKE ?)";
    $search_term = "%{$search_query}%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'ss';
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}

$sql .= " ORDER BY o.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

// Fetch status counts for filter tabs
$status_counts = array_fill_keys($statuses, 0);
$status_counts['all'] = 0;
$count_result = $conn->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
if ($count_result) {
    while ($row = $count_result->fetch_assoc()) {
        if (isset($status_counts[$row['status']])) {
            $status_counts[$row['status']] = $row['count'];
            $status_counts['all'] += $row['count'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - DUNZO Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #f4f7fe; --sidebar-bg: #ffffff; --card-bg: #ffffff;
            --text-color: #1a202c; --text-muted: #718096; --primary: #4361ee;
            --primary-light: #eef2ff; --success: #1abc9c; --warning: #f1c40f;
            --danger: #e74c3c; --info: #3498db; --border-color: #e2e8f0;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.05);
            --border-radius: 12px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-color); color: var(--text-color); display: flex; }
        a { text-decoration: none; color: inherit; }

        .sidebar {
            width: 260px; background-color: var(--sidebar-bg); height: 100vh;
            position: fixed; left: 0; top: 0; padding: 20px;
            display: flex; flex-direction: column; border-right: 1px solid var(--border-color);
        }
        .sidebar .logo { font-size: 28px; font-weight: 700; padding: 10px; margin-bottom: 30px; }
        .sidebar .logo .yellow { color: #febd69; }
        .sidebar .logo .green { color: #00a651; }
        .sidebar-menu { list-style: none; padding: 0; margin: 0; flex-grow: 1; }
        .sidebar-menu li a {
            display: flex; align-items: center; padding: 12px 15px;
            color: var(--text-muted); font-weight: 500; border-radius: 8px;
            margin-bottom: 5px; transition: all 0.2s ease;
        }
        .sidebar-menu li a i { font-size: 18px; width: 20px; margin-right: 15px; text-align: center; }
        .sidebar-menu li a.active, .sidebar-menu li a:hover { background-color: var(--primary-light); color: var(--primary); }

        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 30px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header h2 { font-size: 24px; font-weight: 600; }

        .orders-panel { background-color: var(--card-bg); border-radius: var(--border-radius); box-shadow: var(--shadow); overflow: hidden; }
        .panel-toolbar { padding: 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); flex-wrap: wrap; gap: 15px; }
        
        .filter-tabs { display: flex; gap: 5px; background-color: var(--bg-color); padding: 5px; border-radius: 8px; }
        .filter-tabs a { padding: 8px 15px; border-radius: 6px; color: var(--text-muted); font-weight: 500; font-size: 14px; transition: all 0.2s ease; }
        .filter-tabs a.active, .filter-tabs a:hover { background-color: var(--card-bg); color: var(--primary); box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .filter-tabs .count { background-color: var(--border-color); color: var(--text-muted); font-size: 11px; padding: 2px 6px; border-radius: 10px; margin-left: 8px; }
        .filter-tabs a.active .count { background-color: var(--primary); color: white; }

        .search-form { display: flex; align-items: center; }
        .search-form { 
            border: 1px solid var(--border-color);
            border-radius: 50px;
            overflow: hidden;
        }
        .search-form input { border: none; padding: 10px 15px; font-size: 14px; flex-grow: 1; background: transparent; }
        .search-form input:focus { outline: none; }
        .search-form button { background-color: var(--primary); color: white; border: none; padding: 10px 15px; cursor: pointer; }

        .bulk-actions { padding: 20px; border-bottom: 1px solid var(--border-color); display: flex; gap: 10px; align-items: center; }
        .bulk-actions select { padding: 8px; border-radius: 6px; border: 1px solid var(--border-color); }
        .bulk-actions button { padding: 8px 15px; border-radius: 6px; border: none; background-color: var(--primary); color: white; cursor: pointer; font-weight: 500; }

        .table-container { padding: 20px; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 15px; text-align: left; border-bottom: 1px solid var(--border-color); }
        .data-table th { font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; }
        .data-table tbody tr:last-child td { border-bottom: none; }
        .data-table tbody tr:hover { background-color: #f9fafb; }

        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: capitalize; color: white; }
        .status-badge.pending { background-color: var(--warning); }
        .status-badge.confirmed, .status-badge.preparing { background-color: var(--info); }
        .status-badge.out_for_delivery { background-color: var(--primary); }
        .status-badge.delivered { background-color: var(--success); }
        .status-badge.cancelled { background-color: var(--danger); }

        .status-select { padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 14px; }
        .no-orders { text-align: center; padding: 50px; color: var(--text-muted); }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; // Using a reusable sidebar ?>

    <div class="main-content">
        <div class="header">
            <h2>Order Management</h2>
        </div>

        <div class="orders-panel">
            <div class="panel-toolbar">
                <div class="filter-tabs">
                    <a href="orders.php" class="<?= $filter_status === 'all' ? 'active' : '' ?>">All <span class="count"><?= $status_counts['all'] ?></span></a>
                    <?php foreach ($statuses as $status): ?>
                        <a href="?status=<?= $status ?>" class="<?= $filter_status === $status ? 'active' : '' ?>">
                            <?= ucfirst(str_replace('_', ' ', $status)) ?>
                            <span class="count"><?= $status_counts[$status] ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
                <form action="orders.php" method="GET" class="search-form">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($filter_status) ?>">
                    <input type="text" name="search" placeholder="Search Order # or Name..." value="<?= htmlspecialchars($search_query) ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <form action="orders.php" method="POST" id="bulk-action-form">
               

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($orders)): ?>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><input type="checkbox" name="order_ids[]" value="<?= $order['id'] ?>" class="order-checkbox"></td>
                                    <td><strong><?= htmlspecialchars($order['order_number']) ?></strong></td>
                                    <td><?= htmlspecialchars($order['full_name']) ?></td>
                                    <td><?= date('M d, Y, g:i A', strtotime($order['created_at'])) ?></td>
                                    <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                                    <td>
                                        <span class="status-badge <?= htmlspecialchars($order['status']) ?>">
                                            <?= ucfirst(str_replace('_', ' ', htmlspecialchars($order['status']))) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form action="orders.php" method="POST" class="status-update-form">
                                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                            <input type="hidden" name="update_status" value="1">
                                            <select name="status" class="status-select" data-original-status="<?= htmlspecialchars($order['status']) ?>">
                                                <?php foreach ($statuses as $status): ?>
                                                    <option value="<?= $status ?>" <?= $order['status'] === $status ? 'selected' : '' ?>>
                                                        <?= ucfirst(str_replace('_', ' ', $status)) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="no-orders">
                                        <i class="fas fa-box-open fa-3x" style="margin-bottom: 15px;"></i><br>
                                        No orders found for this filter.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle "Select All" checkbox
            const selectAllCheckbox = document.getElementById('select-all');
            const orderCheckboxes = document.querySelectorAll('.order-checkbox');

            selectAllCheckbox.addEventListener('change', function() {
                orderCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });

            // Handle individual status update confirmation
            const statusSelects = document.querySelectorAll('.status-select');
            statusSelects.forEach(select => {
                // The form is submitted on change, so we intercept that.
                select.addEventListener('change', function(e) {
                    const form = this.closest('form');
                    if (!confirm('Are you sure you want to update the status for this order?')) {
                        // Revert to the original value if the user cancels
                        this.value = this.dataset.originalStatus;
                    } else {
                        form.submit();
                    }
                });
            });

            // Handle bulk action confirmation
            const bulkActionForm = document.getElementById('bulk-action-form');
            bulkActionForm.addEventListener('submit', function(e) {
                const selectedCount = document.querySelectorAll('.order-checkbox:checked').length;
                if (selectedCount === 0) {
                    alert('Please select at least one order.');
                    e.preventDefault();
                    return;
                }
                if (!confirm(`Are you sure you want to apply this action to ${selectedCount} selected order(s)?`)) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
