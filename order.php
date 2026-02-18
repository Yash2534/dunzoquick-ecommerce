<?php
session_start();
include 'config.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Handle flash messages from cancel_order.php
$message = $_SESSION['order_message'] ?? null;
$message_type = $_SESSION['order_message_type'] ?? 'info';
unset($_SESSION['order_message'], $_SESSION['order_message_type']);

// Fetch all orders for the user, most recent first
$stmt_orders = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt_orders->bind_param("i", $user_id);
$stmt_orders->execute();
$orders_result = $stmt_orders->get_result();
$orders = $orders_result->fetch_all(MYSQLI_ASSOC);

$order_items = [];
if (!empty($orders)) {
    // Get all order IDs to fetch items efficiently
    $order_ids = array_column($orders, 'id');
    
    // Create placeholders for the IN clause (e.g., ?,?,?)
    $placeholders = implode(',', array_fill(0, count($order_ids), '?'));
    $types = str_repeat('i', count($order_ids));
    
    // Fetch all items for these orders in a single query to avoid N+1 problem
    $stmt_items = $conn->prepare("
        SELECT oi.*, p.image 
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id IN ($placeholders)
    ");
    $stmt_items->bind_param($types, ...$order_ids);
    $stmt_items->execute();
    $items_result = $stmt_items->get_result();
    
    // Group items by their order_id for easy access
    while ($item = $items_result->fetch_assoc()) {
        $order_items[$item['order_id']][] = $item;
    }
}

// Helper function to generate a clean, root-relative image path
function get_image_path($db_path) {
    $default_image = '/DunzoQuick/Image/no-image.png';
    if (empty(trim((string)$db_path))) {
        return $default_image;
    }
    // 1. Clean up known incorrect prefixes
    $path = preg_replace('#^(\.\./|/DUNZO/|Product/)#', '', (string)$db_path);
    $path = ltrim($path, '/');

    // 2. Based on your file structure `C:\xampp\htdocs\DUNZO\Image`, all images
    // should be inside the 'Image' directory. This code ensures that.
    // It prepends 'Image/' if it's missing.
    // We also check for 'PICTURE/' for compatibility with older data.
    if (strpos($path, 'Image/') !== 0 && strpos($path, 'PICTURE/') !== 0) {
        $path = 'Image/' . $path;
    }

    return '/DunzoQuick/' . htmlspecialchars($path);
}

// Helper function to determine the CSS class for an order status badge
function get_status_badge($status) {
    switch (strtolower($status)) {
        case 'delivered':
            return 'badge-success';

        case 'cancelled':
            return 'badge-danger';
        case 'out_for_delivery':
            return 'badge-info';
        case 'confirmed':
        case 'preparing':
            return 'badge-warning';
        case 'pending':

        default:
            return 'badge-secondary';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">

<title>My Orders - DUNZO</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
  --primary: #0ea86f; /* Blinkit green */

  --secondary: #20c997; /* Lighter green for gradients/hovers */
  --light: #f8f9fa;
  --dark: #212529;
  --success: #28a745;
  --info: #17a2b8;
  --warning: #ffc107;
  --danger: #dc3545;
  --gray-100: #f8f9fa;
  --gray-200: #e9ecef;
  --gray-300: #dee2e6;
  --gray-600: #6c757d;
  --gray-800: #343a40;
  --border-radius: 16px; /* Slightly larger radius */
  --box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.alert-container {
    margin-bottom: 20px;
}
.alert {
    padding: 1rem;
    border-radius: 8px;
    border: 1px solid transparent;
    font-weight: 500;
}
.alert-success {
    color: #0f5132; background-color: #d1e7dd; border-color: #badbcc;
}
.alert-danger {
    color: #842029; background-color: #f8d7da; border-color: #f5c2c7;
}

body {
  background-color: #f7f8fa; /* Lighter, cleaner background */

  font-family: 'Poppins', sans-serif;
  color: var(--gray-800);
}

.orders-container {
  max-width: 900px;
  margin: 30px auto;
  padding: 0 15px;
}

.orders-header {
  text-align: center;
  margin-bottom: 30px;
}

.orders-header h1 {
  font-weight: 700;
  color: var(--dark); /* Dark text is more modern than colored */
  font-size: 2.2rem;
}

.order-card {
  background: white;
  border-radius: var(--border-radius);
  box-shadow: var(--box-shadow);
  margin-bottom: 25px;
  border: 1px solid var(--gray-200); /* Subtle border */
  overflow: hidden;
  transition: all 0.3s ease;
}

.order-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.08);
}

.order-card-header {
  background: white; /* Cleaner than gray */
  padding: 1rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
  border-bottom: 1px solid var(--gray-200);
}

.order-info {

    display: flex;
    flex-direction: column;
    text-align: left;
}

.order-info span {
  font-size: 0.8rem;
  color: var(--gray-600);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.order-info strong {
  color: var(--dark);
  font-weight: 600;
  font-size: 0.95rem;
}

.order-status .badge {
  font-size: 0.8rem;
  padding: 0.4em 0.8em;
  border-radius: 50px;
  font-weight: 600;
  text-transform: capitalize;
}

.badge-success { background-color: #d1fae5; color: #065f46; }
.badge-danger { background-color: #fee2e2; color: #991b1b; }
.badge-info { background-color: #e0f2fe; color: #0c4a6e; }
.badge-warning { background-color: #fef3c7; color: #92400e; }
.badge-secondary { background-color: #f3f4f6; color: #374151; }

.order-card-body {

  padding: 1rem 1.5rem;
}

.order-item-image {
  width: 50px;
  height: 50px;
  border-radius: 8px;
  object-fit: contain;
  background-color: var(--gray-100);
  flex-shrink: 0;
}

.order-item {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 1rem 0;
  border-bottom: 1px solid var(--gray-200);
}

.order-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.item-details { flex-grow: 1; }
.item-details .item-name { font-weight: 600; }
.item-details .item-qty { color: var(--gray-600); font-size: 0.9rem; }
.item-price { font-weight: 600; }

.empty-orders {
  text-align: center;
  padding: 80px 20px;
  background: white;
  border-radius: 16px;
  box-shadow: var(--box-shadow);
}

.empty-orders-icon i { font-size: 4rem; color: var(--primary); margin-bottom: 20px; }
.empty-orders h2 { font-weight: 700; font-size: 1.8rem; margin-bottom: 10px; }
.empty-orders p { color: var(--gray-600); margin-bottom: 30px; }

.explore-btn {
  background: var(--primary);
  color: white;
  border: none;
  border-radius: 50px;
  padding: 14px 32px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s ease;
}

.explore-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(14, 168, 111, 0.3);
  color: white;
  background: #0c8056;
}

.order-card-footer {
    padding: 1rem 1.5rem;
    background: var(--gray-100);
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    align-items: center;
}

.btn-track, .btn-invoice, .btn-cancel {
    border-radius: 50px;
    padding: 8px 20px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    border: 1px solid transparent;
}

.btn-track {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}
.btn-track:hover { background: #0c8056; border-color: #0c8056; color: white; }

.btn-invoice {
    background: transparent;
    color: var(--primary);
    border-color: var(--primary);
}
.btn-invoice:hover { background: var(--primary); color: white; }

.btn-cancel {
    background: transparent;
    color: var(--danger);
    border-color: var(--danger);
    cursor: pointer;
    font-family: 'Poppins', sans-serif; /* Ensure font consistency */
}
.btn-cancel:hover { background: var(--danger); color: white; }
</style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="orders-container">
  <div class="orders-header">
    <h1><i class="fas fa-receipt me-2"></i>My Orders</h1>
  </div>

  <?php if ($message): ?>
    <div class="alert-container">
        <div class="alert alert-<?= htmlspecialchars($message_type) ?>"><i class="fas <?= $message_type === 'success' ? 'fa-check-circle' : 'fa-times-circle' ?> me-2"></i><?= htmlspecialchars($message) ?></div>
    </div>
  <?php endif; ?>

  <?php if (empty($orders)): ?>
    <div class="empty-orders">
      <div class="empty-orders-icon"><i class="fas fa-box-open"></i></div>
      <h2>No Orders Yet!</h2>
      <p>You haven't placed any orders. Let's change that!</p>
      <a href="product.php" class="explore-btn"><i class="fas fa-shopping-basket me-2"></i> Start Shopping</a>
    </div>
  <?php else: ?>
    <?php foreach ($orders as $order): ?>
    <div class="order-card">
      <div class="order-card-header">
        <div class="order-info">
            <span>Order #</span>
            <strong><?= htmlspecialchars($order['order_number']) ?></strong>
        </div>
        <div class="order-info">
            <span>Date</span><strong><?= date('M d, Y', strtotime($order['created_at'])) ?></strong></div>
        <div class="order-info">
            <span>Total</span><strong>₹<?= number_format($order['total_amount'], 2) ?></strong></div>
        <div class="order-status"><span class="badge <?= get_status_badge($order['status']) ?>"><?= htmlspecialchars($order['status']) ?></span></div>
      </div>
      <div class="order-card-body">
          <?php if (isset($order_items[$order['id']])): ?>

            <?php foreach ($order_items[$order['id']] as $item): ?>
            <div class="order-item">
                <img src="<?= get_image_path($item['image'] ?? null) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" class="order-item-image">
              <div class="item-details">
                <div class="item-name"><?= htmlspecialchars($item['product_name']) ?></div>
                <div class="item-qty">Quantity: <?= $item['quantity'] ?></div>
              </div>
              <div class="item-price">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
            </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="text-center text-muted">No items found for this order.</p>
          <?php endif; ?>
      </div>
      <div class="order-card-footer">
        <a href="invoice.php?order_id=<?= $order['id'] ?>" class="btn-invoice"><i class="fas fa-file-invoice me-1"></i> View Invoice</a>

        <a href="track_order.php?order_number=<?= htmlspecialchars($order['order_number']) ?>" class="btn-track"><i class="fas fa-truck me-1"></i> Track Order</a>
      </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>