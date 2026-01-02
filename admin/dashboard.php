<?php
include 'auth_check.php';
include 'db_connect.php';

$admin_id = $_SESSION['admin_id'] ?? 0;
$current_admin = null;
if ($admin_id > 0) {
    $stmt_admin = $conn->prepare("SELECT full_name, profile_photo FROM users WHERE id = ?");
    $stmt_admin->bind_param("i", $admin_id);
    $stmt_admin->execute();
    $current_admin = $stmt_admin->get_result()->fetch_assoc();
    $stmt_admin->close();
}
// Also update session username to be sure it's fresh, in case it was changed elsewhere
if ($current_admin) {
    $_SESSION['admin_username'] = $current_admin['full_name'];
}

// --- DATA FETCHING ---

// 1. Fetch Summary Statistics 
$stats = [
    'total_revenue' => 0,
    'revenue_this_month' => 0,
    'revenue_today' => 0,
    'new_orders' => 0,
    'total_users' => 0,
    'total_products' => 0
];
$sql_stats = "
    SELECT
        (SELECT SUM(total_amount) FROM orders WHERE status = 'delivered') as total_revenue,
        (SELECT SUM(total_amount) FROM orders WHERE status = 'delivered' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())) as revenue_this_month,
        (SELECT SUM(total_amount) FROM orders WHERE status = 'delivered' AND DATE(created_at) = CURDATE()) as revenue_today,
        (SELECT COUNT(*) FROM orders WHERE status = 'pending') as new_orders,
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM products) as total_products
";
$result_stats = $conn->query($sql_stats);
if ($result_stats) {
    $result = $result_stats->fetch_assoc();
    if ($result) {
        // If a value from the database is NULL (e.g., no revenue yet), this makes it 0.
        $stats['total_revenue'] = $result['total_revenue'] ? (float)$result['total_revenue'] : 0;
        $stats['revenue_this_month'] = $result['revenue_this_month'] ? (float)$result['revenue_this_month'] : 0;
        $stats['revenue_today'] = $result['revenue_today'] ? (float)$result['revenue_today'] : 0;
        $stats['new_orders'] = $result['new_orders'] ? (int)$result['new_orders'] : 0;
        $stats['total_users'] = $result['total_users'] ? (int)$result['total_users'] : 0;
        $stats['total_products'] = $result['total_products'] ? (int)$result['total_products'] : 0;
    }
}

// 2. Fetch Recent Orders (Last 5)
$recent_orders = [];
$sql_recent_orders = "
    SELECT o.order_number, o.status, o.total_amount, o.created_at, u.full_name, o.delivery_priority
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 5
";
$result_recent_orders = $conn->query($sql_recent_orders);
if ($result_recent_orders) {
    $recent_orders = $result_recent_orders->fetch_all(MYSQLI_ASSOC);
}

// 3. Fetch Top Customers
$top_customers = [];
$sql_top_customers = "
    SELECT u.full_name, u.profile_photo, SUM(o.total_amount) as total_spent
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.status = 'delivered'
    GROUP BY o.user_id
    ORDER BY total_spent DESC
    LIMIT 5
";
$result_top_customers = $conn->query($sql_top_customers);
if ($result_top_customers) {
    $top_customers = $result_top_customers->fetch_all(MYSQLI_ASSOC);
}

// Helper function for avatars
function get_avatar($photo, $name)
{
    if ($photo && file_exists('../' . $photo)) { // Check if the file exists relative to the admin folder
        return '../' . htmlspecialchars($photo);
    }
    return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=random&color=fff&rounded=true';
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - DUNZO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #f4f7fe;
            --sidebar-bg: #ffffff;
            --card-bg: #ffffff;
            --text-color: #1a202c;
            --text-muted: #718096;
            --primary: #4361ee;
            --primary-light: #eef2ff;
            --border-color: #e2e8f0;
            --danger: #e74c3c;
            --success: #1abc9c;
            --warning: #f1c40f;
            --info: #3498db;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.05);
            --border-radius: 12px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            display: flex;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        .sidebar {
            width: 260px;
            background-color: var(--sidebar-bg);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--border-color);
        }

        .sidebar .logo {
            font-size: 28px;
            font-weight: 700;
            padding: 10px;
            margin-bottom: 30px;
            text-align: center;
        }

        .sidebar .logo .yellow {
            color: #febd69;
        }

        .sidebar .logo .green {
            color: #00a651;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            flex-grow: 1;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: var(--text-muted);
            font-weight: 500;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: all 0.2s ease;
        }

        .sidebar-menu li a i {
            font-size: 18px;
            width: 20px;
            margin-right: 15px;
            text-align: center;
        }

        .sidebar-menu li a.active,
        .sidebar-menu li a:hover {
            background-color: var(--primary-light);
            color: var(--primary);
        }

        .main-content {
            margin-left: 260px;
            width: calc(100% - 260px);
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background-color: var(--card-bg);
            padding: 15px 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        
        .user-profile-dropdown {
            position: relative;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            padding: 5px;
            border-radius: 8px;
            transition: background-color 0.2s ease;
        }

        .user-profile:hover {
            background-color: var(--bg-color);
        }

        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .user-profile .user-details h4 {
            font-size: 15px;
            font-weight: 600;
            margin: 0;
            line-height: 1.2;
        }

        .user-profile .user-details p {
            font-size: 13px;
            color: var(--text-muted);
            margin: 0;
        }

        .user-profile .fa-chevron-down {
            font-size: 12px;
            color: var(--text-muted);
            transition: transform 0.2s ease;
        }

        .user-profile-dropdown.open .user-profile .fa-chevron-down {
            transform: rotate(180deg);
        }

        .dropdown-menu {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            width: 220px;
            z-index: 1000;
            border: 1px solid var(--border-color);
            overflow: hidden;
            display: none;
            /* Hidden by default */
            animation: fadeInDropdown 0.2s ease-out;
        }

        .user-profile-dropdown.open .dropdown-menu {
            display: block;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            font-size: 14px;
            color: var(--text-color);
            transition: background-color 0.2s ease;
        }

        .dropdown-item i {
            width: 16px;
            text-align: center;
            color: var(--text-muted);
        }

        .dropdown-item:hover {
            background-color: var(--bg-color);
        }

        .dropdown-item.logout {
            color: var(--danger);
        }

        .dropdown-item.logout:hover {
            background-color: rgba(229, 62, 62, 0.1);
        }

        .dropdown-item.logout i {
            color: var(--danger);
        }

        @keyframes fadeInDropdown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .summary-cards {
            display: grid;
            grid-template-columns: 1fr;
            /* 1 column on mobile */
            gap: 25px;
            margin-bottom: 30px;
        }

        @media (min-width: 768px) {
            .summary-cards {
                grid-template-columns: repeat(2, 1fr);
            }

            /* 2 columns on tablet */
        }

        @media (min-width: 1200px) {
            .summary-cards {
                grid-template-columns: repeat(3, 1fr);
            }

  
        }

        .summary-box {
            background-color: var(--card-bg);
            padding: 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .summary-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.07);
        }

        .summary-box .icon {
            font-size: 24px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .summary-box .icon.revenue {
            background-color: var(--primary-light);
            color: var(--primary);
        }

        .summary-box .icon.new-orders {
            background-color: #fff8e1;
            color: var(--warning);
        }

        .summary-box .icon.total-users {
            background-color: #e3f2fd;
            color: var(--info);
        }

        .summary-box .icon.total-products {
            background-color: #f3e5f5;
            color: #8e24aa;
        }

        .summary-box .icon.revenue-month {
            background-color: #e0f7fa;
            color: #0097a7;
        }

        .summary-box .icon.revenue-today {
            background-color: #e8f5e9;
            color: #388e3c;
        }

        .summary-box h3 {
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 500;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }

        .summary-box p {
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }

        .data-panels {
            display: grid;
            grid-template-columns: 1fr;
            gap: 25px;
        }

        @media (min-width: 992px) {

            .data-panels {
                grid-template-columns: 2fr 1fr;
            }
        }

        .panel {
            background-color: var(--card-bg);
            padding: 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .panel-header h3 {
            font-size: 18px;
            font-weight: 600;
        }

        .panel-header a {
            color: var(--primary);
            font-weight: 500;
            font-size: 14px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .data-table th {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .data-table tbody tr:hover {
            background-color: #f9fafb;
        }

        .status {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-pending {
            background-color: rgba(245, 124, 0, 0.15);
            color: var(--warning);
        }

        .status-delivered {
            background-color: rgba(56, 161, 105, 0.15);
            color: var(--success);
        }

        .status-confirmed,
        .status-out-for-delivery,
        .status-preparing {
            background-color: rgba(43, 108, 176, 0.15);
            color: var(--info);
        }

        .status-cancelled {
            background-color: rgba(113, 128, 150, 0.15);
            color: var(--text-muted);
        }

        .data-list {
            list-style: none;
        }

        .data-list li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .data-list li:last-child {
            border-bottom: none;
        }

        .data-list .item-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .data-list .item-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .data-list .item-info .name {
            font-weight: 500;
        }

        .data-list .value {
            font-weight: 600;
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <div class="header-left">
                <h2 class="mb-0">Dashboard</h2>
            </div>
            <div class="header-right">
                <div class="user-profile-dropdown">
                    <div class="user-profile" onclick="this.parentElement.classList.toggle('open')">
                        <img src="<?= $current_admin ? get_avatar($current_admin['profile_photo'], $current_admin['full_name']) : 'https://ui-avatars.com/api/?name=A&background=00b34b&color=fff&rounded=true' ?>" alt="Admin User">
                        <div class="user-details">
                            <h4><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></h4>
                            <p>Administrator</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="summary-cards">
            <a href="orders.php">
                <div class="summary-box">
                    <div class="icon revenue"><i class="fas fa-dollar-sign"></i></div>
                    <div class="data">
                        <h3>Total Revenue (All-time)</h3>
                        <p>₹<?= number_format($stats['total_revenue'], 2) ?></p>
                    </div>
                </div>
            </a>
            <a href="orders.php">
                <div class="summary-box">
                    <div class="icon revenue-month"><i class="fas fa-calendar-alt"></i></div>
                    <div class="data">
                        <h3>Revenue This Month</h3>
                        <p>₹<?= number_format($stats['revenue_this_month'], 2) ?></p>
                    </div>
                </div>
            </a>
            <a href="orders.php">
                <div class="summary-box">
                    <div class="icon revenue-today"><i class="fas fa-calendar-day"></i></div>
                    <div class="data">
                        <h3>Today's Revenue</h3>
                        <p>₹<?= number_format($stats['revenue_today'], 2) ?></p>
                    </div>
                </div>
            </a>
            <a href="orders.php?status=pending">
                <div class="summary-box">
                    <div class="icon new-orders"><i class="fas fa-shopping-bag"></i></div>
                    <div class="data">
                        <h3>New Orders</h3>
                        <p><?= number_format($stats['new_orders']) ?></p>
                    </div>
                </div>
            </a>
            <a href="User.php">
                <div class="summary-box">
                    <div class="icon total-users"><i class="fas fa-users"></i></div>
                    <div class="data">
                        <h3>Total Customers</h3>
                        <p><?= number_format($stats['total_users']) ?></p>
                    </div>
                </div>
            </a>
            <a href="products.php">
                <div class="summary-box">
                    <div class="icon total-products"><i class="fas fa-box-open"></i></div>
                    <div class="data">
                        <h3>Total Products</h3>
                        <p><?= number_format($stats['total_products']) ?></p>
                    </div>
                </div>
            </a>
        </div>

        <div class="data-panels">
            <div class="panel">
                <div class="panel-header">
                    <h3>Recent Orders</h3>
                    <a href="orders.php">View All</a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_orders)): ?>
                            <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($order['order_number']) ?>
                                    </td>
                                    <td><?= htmlspecialchars($order['full_name']) ?></td>
                                    <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                                    <td><span class="status status-<?= str_replace('_', '-', htmlspecialchars($order['status'])) ?>"><?= ucfirst(str_replace('_', ' ', htmlspecialchars($order['status']))) ?></span></td>
                                    <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 20px;">No recent orders found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <h3>Top Customers</h3>
                    <a href="User.php">View All</a>
                </div>
                <ul class="data-list">
                    <?php if (!empty($top_customers)): ?>
                        <?php foreach ($top_customers as $customer): ?>
                            <li>
                                <div class="item-info">
                                    <img src="<?= get_avatar($customer['profile_photo'], $customer['full_name']) ?>" alt="<?= htmlspecialchars($customer['full_name']) ?>">
                                    <span class="name"><?= htmlspecialchars($customer['full_name']) ?></span>
                                </div>
                                <span class="value">₹<?= number_format($customer['total_spent'], 2) ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li style="justify-content: center; padding: 20px;">No customer data available.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <script>
        // Close dropdown when clicking outside
        window.addEventListener('click', function(e) {
            const dropdown = document.querySelector('.user-profile-dropdown');
            // Check if the dropdown is open and the click is outside of it
            if (dropdown && dropdown.classList.contains('open') && !dropdown.contains(e.target)) {
                dropdown.classList.remove('open');
            }
        });
    </script>
</body>

</html>
