<?php
include 'auth_check.php';
include 'db_connect.php';

// --- DATA FETCHING ---

// 1. Stat Cards Data
$stats_query = "
    SELECT
        (SELECT SUM(total_amount) FROM orders WHERE status = 'delivered') as total_revenue,
        (SELECT COUNT(*) FROM orders) as total_orders,
        (SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as new_users_30_days,
        (SELECT COUNT(*) FROM users WHERE membership_expiry_date >= CURDATE()) as prime_members
";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

$total_revenue = $stats['total_revenue'] ?? 0;
$total_orders = $stats['total_orders'] ?? 0;
$new_users_30_days = $stats['new_users_30_days'] ?? 0;
$prime_members = $stats['prime_members'] ?? 0;
$avg_order_value = ($total_orders > 0) ? ($total_revenue / $total_orders) : 0;

// 2. Sales Chart Data (Last 7 days)
$sales_chart_data = [
    'labels' => [],
    'data' => []
];
// Initialize all 7 days with 0 revenue
for ($i = 6; $i >= 0; $i--) {
    $date = date("Y-m-d", strtotime("-$i days"));
    $sales_chart_data['labels'][] = date("M d", strtotime($date));
    $sales_chart_data['data'][$date] = 0;
}
// Query for sales in the last 7 days
$sales_query = "
    SELECT DATE(created_at) as order_date, SUM(total_amount) as daily_revenue 
    FROM orders 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND status = 'delivered'
    GROUP BY order_date 
    ORDER BY order_date ASC
";
$sales_result = $conn->query($sales_query);
if ($sales_result) {
    while ($row = $sales_result->fetch_assoc()) {
        $sales_chart_data['data'][$row['order_date']] = (float)$row['daily_revenue'];
    }
}
// Convert the associative array to a simple indexed array for the chart
$sales_chart_data['data'] = array_values($sales_chart_data['data']);

// 3. Recent Orders
$recent_orders = [];
$orders_query = "
    SELECT o.order_number, u.full_name, o.total_amount, o.status, o.created_at 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
";
$orders_result = $conn->query($orders_query);
if ($orders_result) {
    $recent_orders = $orders_result->fetch_all(MYSQLI_ASSOC);
}

// 4. Top Selling Products
// NOTE: This query assumes an 'order_items' table exists. If not, this section will be empty.
$top_products = [];
// Check if order_items table exists to avoid errors
$table_check = $conn->query("SHOW TABLES LIKE 'order_items'");
if ($table_check && $table_check->num_rows > 0) {
    $top_products_query = "
        SELECT p.name as product_name, SUM(oi.quantity) as total_sold 
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.status = 'delivered'
        GROUP BY oi.product_id 
        ORDER BY total_sold DESC 
        LIMIT 5
    ";
    $top_products_result = $conn->query($top_products_query);
    if ($top_products_result) {
        $top_products = $top_products_result->fetch_all(MYSQLI_ASSOC);
    }
}

// 5. Order Status Distribution
$order_status_distribution = [
    'labels' => [],
    'data' => []
];
$status_query = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
$status_result = $conn->query($status_query);
if ($status_result) {
    while ($row = $status_result->fetch_assoc()) {
        $order_status_distribution['labels'][] = ucfirst(str_replace('_', ' ', $row['status']));
        $order_status_distribution['data'][] = (int)$row['count'];
    }
}

// 6. Top Customers
$top_customers = [];
$customers_query = "
    SELECT u.full_name, SUM(o.total_amount) as total_spent, COUNT(o.id) as order_count 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.status = 'delivered' 
    GROUP BY o.user_id 
    ORDER BY total_spent DESC 
    LIMIT 5
";
$customers_result = $conn->query($customers_query);
if ($customers_result) {
    $top_customers = $customers_result->fetch_all(MYSQLI_ASSOC);
}

// Helper for status badges
$status_classes = [
    'pending' => 'bg-warning text-dark',
    'confirmed' => 'bg-info text-dark',
    'preparing' => 'bg-primary',
    'out_for_delivery' => 'bg-primary',
    'delivered' => 'bg-success',
    'cancelled' => 'bg-danger',
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard - DUNZO Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fc; font-family: 'Nunito', sans-serif; }
        .page-title { margin: 2rem 0; color: #5a5c69; }
        .card { margin-bottom: 1.5rem; border: 1px solid #e3e6f0; }
        .border-left-primary { border-left: .25rem solid #4e73df!important; }
        .border-left-success { border-left: .25rem solid #1cc88a!important; }
        .border-left-info { border-left: .25rem solid #36b9cc!important; }
        .border-left-warning { border-left: .25rem solid #f6c23e!important; }
        .border-left-danger { border-left: .25rem solid #e74a3b!important; }
        .text-xs { font-size: .7rem; }
        .text-gray-300 { color: #dddfeb!important; }
        .text-gray-800 { color: #5a5c69!important; }
        .no-gutters { margin-right: 0; margin-left: 0; }
        .no-gutters > .col, .no-gutters > [class*="col-"] { padding-right: 0; padding-left: 0; }
        .badge.bg-primary { background-color: #4e73df !important; }
        .badge.bg-success { background-color: #1cc88a !important; }
        .badge.bg-info { background-color: #36b9cc !important; }
        .badge.bg-warning { background-color: #f6c23e !important; }
        .badge.bg-danger { background-color: #e74a3b !important; }
        .badge.bg-secondary { background-color: #858796 !important; }
        .progress { height: 1rem; }
        .small { font-size: 80%; }
    </style>
</head>
<body>
<div class="container-fluid px-4">
    <h1 class="page-title fw-bold">Analytics Dashboard</h1>

    <!-- Stat Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Revenue</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">₹<?= number_format($total_revenue, 2) ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-dollar-sign fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">Total Orders</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?= number_format($total_orders) ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-shopping-cart fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">Avg. Order Value</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">₹<?= number_format($avg_order_value, 2) ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-clipboard-list fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">New Users (30 Days)</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?= number_format($new_users_30_days) ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-danger text-uppercase mb-1">Active Prime Members</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?= number_format($prime_members) ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-crown fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Lists -->
    <div class="row">
        <div class="col-lg-7 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Sales Overview (Last 7 Days)</h6>
                </div>
                <div class="card-body">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-5 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Top Selling Products</h6></div>
                <div class="card-body">
                    <?php if (!empty($top_products)): ?>
                        <?php foreach ($top_products as $product): ?>
                            <h4 class="small fw-bold"><?= htmlspecialchars($product['product_name']) ?><span
                                    class="float-end"><?= $product['total_sold'] ?> Sold</span></h4>
                            <div class="progress mb-4"><div class="progress-bar bg-success" role="progressbar" style="width: <?= ($top_products[0]['total_sold'] > 0) ? (($product['total_sold'] / $top_products[0]['total_sold']) * 100) : 0 ?>%"></div></div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center text-muted mt-3">No product sales data available.</p>
                        <p class="text-center text-muted small">This requires an 'order_items' table with sales records.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- More Analytics -->
    <div class="row">
        <!-- Order Status Distribution -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Order Status Distribution</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div style="position: relative; height:300px; width:300px">
                        <canvas id="orderStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Customers -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Top Customers by Spending</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Customer Name</th>
                                    <th>Total Orders</th>
                                    <th>Total Spent</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($top_customers)): ?>
                                    <?php foreach($top_customers as $customer): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($customer['full_name']) ?></td>
                                        <td><?= $customer['order_count'] ?></td>
                                        <td class="fw-bold">₹<?= number_format($customer['total_spent'], 2) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center">No customer data available.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Recent Orders</h6></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead><tr><th>Order ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                        <?php if (!empty($recent_orders)): ?>
                            <?php foreach($recent_orders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['order_number']) ?></td>
                                <td><?= htmlspecialchars($order['full_name']) ?></td>
                                <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                                <td><span class="badge <?= $status_classes[$order['status']] ?? 'bg-secondary' ?>"><?= ucfirst(str_replace('_', ' ', $order['status'])) ?></span></td>
                                <td><?= date("d M, Y", strtotime($order['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center">No recent orders found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var ctx = document.getElementById('salesChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($sales_chart_data['labels'] ?? []) ?>,
            datasets: [{
                label: 'Revenue',
                data: <?= json_encode($sales_chart_data['data'] ?? []) ?>,
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                borderColor: 'rgba(78, 115, 223, 1)',
                borderWidth: 2,
                pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: { scales: { y: { beginAtZero: true } } }
    });

    // Doughnut Chart for Order Status
    var statusCtx = document.getElementById('orderStatusChart').getContext('2d');
    var orderStatusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($order_status_distribution['labels'] ?? []) ?>,
            datasets: [{
                data: <?= json_encode($order_status_distribution['data'] ?? []) ?>,
                backgroundColor: ['#1cc88a', '#e74a3b', '#f6c23e', '#4e73df', '#36b9cc'],
                hoverOffset: 4
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                }
            },
        },
    });
});
</script>

</body>
</html>