<?php
// filepath: c:\xampp\htdocs\DUNZO\cart.php
session_start();
include 'config.php';


$tax_rate = 0.09;
// Helper function to generate a clean, root-relative image path
function get_image_path($db_path) {
    $default_image = '/DUNZO/Image/no-image.png';
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
    
    return '/DUNZO/' . htmlspecialchars($path);
}

// Ensure user logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Fetch user details to check for Prime membership
$stmt_user = $conn->prepare("SELECT membership_expiry_date FROM users WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();
$is_prime_member = ($user && $user['membership_expiry_date'] && strtotime($user['membership_expiry_date']) >= time());
$stmt_user->close();

// Prepare main cart query, to be used for both AJAX updates and page load
$stmt_cart_query = $conn->prepare("
    SELECT c.*, p.name as product_name, p.price, p.image
    FROM cart c 
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?");
$stmt_cart_query->bind_param("i", $user_id);

// Update quantity (This action is handled via AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = max(1, (int)$_POST['quantity']);
    $stmt_update = $conn->prepare("UPDATE cart SET quantity=? WHERE user_id=? AND product_id=?");
    $stmt_update->bind_param("iii", $quantity, $user_id, $product_id);
    $stmt_update->execute();
    // Recalculate everything for the JSON response
    $stmt_cart_query->execute();
    $result = $stmt_cart_query->get_result();
    $cart_items_ajax = $result->fetch_all(MYSQLI_ASSOC);

    $subtotal_ajax = 0;
    $item_count_ajax = 0;
    $item_price_ajax = 0;
    foreach ($cart_items_ajax as $item) {
        $subtotal_ajax += $item['price'] * $item['quantity'];
        $item_count_ajax += $item['quantity'];
        if ($item['product_id'] == $product_id) {
            $item_price_ajax = $item['price'];
        }
    }
    $item_total_price_ajax = $item_price_ajax * $quantity;

    $applied_coupon_ajax = $_SESSION['applied_coupon'] ?? null;
    $discount_ajax = $applied_coupon_ajax ? ($subtotal_ajax * ($applied_coupon_ajax['discount_percentage'] / 100)) : 0;
    $order_value_ajax = $subtotal_ajax - $discount_ajax;
    $shipping_ajax = calculate_shipping_charge($order_value_ajax); // Pass correct prime status
    $tax_ajax = ($subtotal_ajax - $discount_ajax) * $tax_rate;
    $total_ajax = $subtotal_ajax - $discount_ajax + $shipping_ajax + $tax_ajax;

    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success', 'item_count' => $item_count_ajax,
        'item_total_price' => '₹' . number_format($item_total_price_ajax, 2),
        'subtotal' => '₹' . number_format($subtotal_ajax, 2), 'discount' => '-₹' . number_format($discount_ajax, 2),
        'shipping' => $shipping_ajax == 0 ? 'FREE' : '₹' . number_format($shipping_ajax, 2),
        'tax' => '₹' . number_format($tax_ajax, 2), 'total' => '₹' . number_format($total_ajax, 2),
        'discount_applied' => $discount_ajax > 0,
        'coupon_code' => $applied_coupon_ajax['code'] ?? ''
    ]);
    exit();
}

// Remove item (This action is handled via AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove'])) {
    $product_id = (int)$_POST['remove'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id=? AND product_id=?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();

    header('Content-Type: application/json');
    echo json_encode(['status' => 'success']);
    exit();
}

// Empty cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['empty'])) {
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    // Also remove any applied coupon from session
    unset($_SESSION['applied_coupon']);
    header("Location: cart.php"); exit();
}

// Fetch cart
$stmt_cart_query->execute();
$result = $stmt_cart_query->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);

// Calculate initial subtotal and item count. Price is now fetched securely from the products table.
$subtotal = 0;
$item_count = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $item_count += $item['quantity'];
}

// --- Fetch Available Coupons for the user ---
$available_coupons = [];
if ($subtotal > 0) {
    $stmt_avail_coupons = $conn->prepare("
        SELECT * FROM coupons 
        WHERE is_active = 1 
        AND (expiry_date IS NULL OR expiry_date >= CURDATE())
        AND (user_id = ? OR user_id IS NULL)
        ORDER BY min_spend ASC
    ");
    $stmt_avail_coupons->bind_param("i", $user_id);
    $stmt_avail_coupons->execute();
    $result_avail_coupons = $stmt_avail_coupons->get_result();
    $available_coupons = $result_avail_coupons->fetch_all(MYSQLI_ASSOC);
    $stmt_avail_coupons->close();
}

// --- Coupon Management ---

// Handle removing a coupon
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_coupon'])) {
    $message = "Coupon removed.";
    if (isset($_SESSION['applied_coupon']['code'])) {
        $message = "Coupon '" . htmlspecialchars($_SESSION['applied_coupon']['code']) . "' removed.";
    }
    unset($_SESSION['applied_coupon']);
    $_SESSION['coupon_message'] = $message;
    $_SESSION['coupon_message_type'] = 'info';
    header("Location: cart.php");
    exit();
}

// Handle applying a coupon
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_coupon'])) {
    $code = strtoupper(trim($_POST['coupon_code']));
    if (empty($code)) {
        unset($_SESSION['applied_coupon']);
    } else {
        // Query for a global coupon OR a user-specific coupon
        $stmt_coupon = $conn->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND (user_id IS NULL OR user_id = ?)");
        $stmt_coupon->bind_param("si", $code, $user_id);
        $stmt_coupon->execute();
        $coupon = $stmt_coupon->get_result()->fetch_assoc();

        if ($coupon) {
            if ($coupon['expiry_date'] && strtotime($coupon['expiry_date']) < strtotime('today')) {
                $_SESSION['coupon_message'] = "Coupon '{$code}' has expired.";
                $_SESSION['coupon_message_type'] = 'error';
                unset($_SESSION['applied_coupon']);
            } elseif ($subtotal < $coupon['min_spend']) {
                $_SESSION['coupon_message'] = "You need to spend at least ₹" . number_format($coupon['min_spend'], 2) . " to use this coupon.";
                $_SESSION['coupon_message_type'] = 'error';
                unset($_SESSION['applied_coupon']);
            } else {
                $_SESSION['applied_coupon'] = $coupon;
                $_SESSION['coupon_message'] = "Coupon '{$code}' applied successfully!";
                $_SESSION['coupon_message_type'] = 'success';
            }
        } else {
            $_SESSION['coupon_message'] = "Invalid or unavailable coupon code.";
            $_SESSION['coupon_message_type'] = 'error';
            unset($_SESSION['applied_coupon']);
        }
    }
    header("Location: cart.php"); // Redirect to show message and prevent resubmission
    exit();
}

// --- Final Totals Calculation ---
$applied_coupon = $_SESSION['applied_coupon'] ?? null;
$discount = $applied_coupon ? ($subtotal * ($applied_coupon['discount_percentage'] / 100)) : 0;
$order_value = $subtotal - $discount;
$shipping = calculate_shipping_charge($order_value); // Pass correct prime status
$tax = ($subtotal - $discount) * $tax_rate;
$total = $subtotal - $discount + $shipping + $tax;

// --- Proceed to Checkout ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proceed_to_checkout'])) {
    if (empty($cart_items)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Your cart is empty.']);
        exit();
    }
    // The order should only be created AFTER successful payment verification.
    // This block should simply redirect to the checkout page.
    // The checkout page will handle the totals and payment initiation.
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'redirect' => 'checkout.php']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Shopping Cart - DUNZO</title>
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
  --danger: #dc3545;
  --gray-100: #f8f9fa;
  --gray-200: #e9ecef;
  --gray-300: #ced4da;
  --gray-600: #6c757d;
  --gray-800: #343a40;
  --border-radius: 8px;
  --box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  --transition: all 0.3s ease;
}

body {
  background-color: #f7f8fa; /* Lighter, cleaner background */
  font-family: 'Poppins', sans-serif;
  color: var(--gray-800);
}

.navbar {
    background-color: #ffffff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
}

.logo {
    font-size: 32px;
    font-weight: 700;
}
.logo .yellow { color: #febd69; }
.logo .green { color: #00a651; }

.navbar-nav .nav-link {
    color: var(--gray-600) !important;
    font-weight: 500;
    position: relative;
    padding: 0.5rem 1rem;
}
.navbar-nav .nav-link:hover, .navbar-nav .nav-link.active {
    color: var(--primary) !important; /* Kept for header consistency */
}
.navbar-nav .nav-link .cart-badge {
    background-color: var(--secondary);
    color: white;
    font-size: 0.75rem;
    position: absolute;
    top: 0;
    right: 0;
    transform: translate(50%, -50%);
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.cart-container {
  max-width: 1200px;
  margin: 40px auto;
  padding: 0 15px;
}

.cart-header {
  text-align: center;
  margin-bottom: 30px;
}
.cart-header h1 {
  font-weight: 700;
  color: var(--dark);
}

.cart-grid {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 30px;
  align-items: flex-start;
}

.cart-items-card {
  background: white;
  border-radius: var(--border-radius);
  box-shadow: var(--box-shadow);
  padding: 1rem;
}

.cart-table {
  margin-bottom: 0;
}
.cart-table th {
  font-weight: 600;
  color: var(--gray-600);
  text-transform: uppercase;
  font-size: 0.8rem;
  letter-spacing: 0.5px;
  border-bottom: 2px solid var(--gray-200);
  padding: 1rem;
}
.cart-table td {
  padding: 1.5rem 1rem;
  vertical-align: middle;
}
.cart-table tbody tr {
  border-bottom: 1px solid var(--gray-200);
}
.cart-table tbody tr:last-child {
  border-bottom: none;
}

.product-info {
  display: flex;
  align-items: center;
  gap: 15px;
}
.product-image {
  width: 80px;
  height: 80px;
  object-fit: contain;
  border-radius: 8px;
  background: var(--gray-100);
  flex-shrink: 0;
}
.product-details .product-name {
  font-weight: 600;
  color: var(--dark);
  margin-bottom: 4px;
}
.product-details .product-price {
  color: var(--gray-600);
  font-size: 0.9rem;
}

.quantity-control {
  display: flex;
  align-items: center;
  border: 1px solid var(--primary);
  border-radius: var(--border-radius);
  overflow: hidden;
  max-width: 120px;
  margin: auto;
}
.quantity-control {
  display: flex;
  align-items: center;
  border: 1px solid var(--primary);
  border-radius: var(--border-radius);
  overflow: hidden;
}
.quantity-btn {
  background: none;
  border: none;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 1.2rem;
  color: var(--primary);
  transition: var(--transition);
}
.quantity-btn:hover {
  background-color: rgba(14, 168, 111, 0.1);
}
.quantity-input {
  width: 40px;
  border: none;
  text-align: center;
  font-weight: 500;
  background: transparent;
}
.quantity-input:focus {
  outline: none;
}
.quantity-input::-webkit-outer-spin-button,
.quantity-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.remove-btn {
  background: none;
  border: none;
  color: var(--gray-500);
  cursor: pointer;
  font-size: 1.1rem;
  transition: var(--transition);
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}
.remove-btn:hover {
  color: var(--danger);
  background-color: rgba(220, 53, 69, 0.1);
}

.cart-actions-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}
.action-btn {
  background: transparent;
  border: 1px solid var(--gray-300);
  color: var(--gray-700);
  padding: 0.6rem 1.2rem;
  border-radius: 50px;
  text-decoration: none;
  font-weight: 500;
  transition: var(--transition);
}
.action-btn:hover {
  border-color: var(--dark);
  background: var(--dark);
  color: white;
}
.action-btn.empty:hover {
  border-color: var(--danger);
  background: var(--danger);
  color: white;
}

.cart-summary {
  background: white;
  border-radius: var(--border-radius);
  box-shadow: var(--box-shadow);
  padding: 25px;
  position: sticky;
  top: 30px;
}
.item-total-price {
  font-weight: 600;
  font-size: 1.1rem;
}

.summary-title {
  font-weight: 700;
  font-size: 1.4rem;
  margin-bottom: 20px;
  color: var(--dark);
  padding-bottom: 15px;
  border-bottom: 1px solid var(--gray-200);
}

.summary-details .summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 15px;
  font-size: 0.95rem;
}
.summary-details .summary-row span:first-child {
  color: var(--gray-600);
}
.summary-details .summary-row span:last-child {
  font-weight: 600;
  color: var(--dark);
}
.summary-row.total {
  font-size: 1.3rem;
  font-weight: 700;
  color: var(--dark);
  padding-top: 15px;
  border-top: 1px solid var(--gray-200);
  margin-top: 10px;
}
.summary-row.savings {
  color: var(--success) !important;
  font-weight: 600;
}
.summary-row.savings span {
  color: inherit !important;
}
.summary-row.total {
  font-size: 1.4rem;
  font-weight: 700;
  color: var(--dark);
  margin-top: 15px;
  padding-top: 15px;
  border-top: 1px solid var(--gray-200);
}
.checkout-btn {
  background: var(--primary);
  color: white;
  border: none;
  border-radius: var(--border-radius);
  padding: 15px;
  font-weight: 600;
  font-size: 1.1rem;
  width: 100%;
  transition: var(--transition);
  box-shadow: 0 4px 15px rgba(14, 168, 111, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.checkout-btn:hover {
  transform: translateY(-3px);
  background: var(--secondary);
  box-shadow: 0 6px 18px rgba(14, 168, 111, 0.3);
}

.cart-actions {
  display: flex;
  gap: 15px;
  margin-top: 30px;
  flex-wrap: wrap;
}

.action-btn {
  padding: 12px 20px;
  border-radius: 50px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: var(--transition);
}

.action-btn.continue {
  background: white;
  color: var(--primary);
  border: 1px solid var(--primary);
}

.action-btn.continue:hover {
  background: var(--primary);
  color: white;
}

.action-btn.empty {
  background: white;
  color: var(--secondary);
  border: 1px solid var(--secondary);
}

.action-btn.empty:hover {
  background: var(--secondary);
  color: white;
}

.empty-cart-modern {
  text-align: center;
  padding: 80px 20px;
  background: #fff;
  border-radius: 16px;
  box-shadow: var(--box-shadow);
  animation: fadeUp 0.6s ease-in-out;
}

.empty-cart-icon {
  background: rgba(14, 168, 111, 0.08);
  border-radius: 50%;
  width: 120px;
  height: 120px;
  margin: 0 auto 20px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.empty-cart-icon i {
  font-size: 3.5rem;
  color: var(--primary);
}

.empty-title {
  font-weight: 700;
  font-size: 1.8rem;
  color: var(--gray-800);
  margin-bottom: 10px;
}

.empty-text {
  color: var(--gray-600);
  font-size: 1rem;
  margin-bottom: 30px;
}

.explore-btn {
  background: var(--primary);
  color: white;
  border: none;
  border-radius: 50px;
  padding: 14px 32px;
  font-weight: 600;
  font-size: 1rem;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(14, 168, 111, 0.2);
}

.explore-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 18px rgba(14, 168, 111, 0.3);
  color: white;
}

/* Animation */
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}


.shopping-btn {
  background: linear-gradient(90deg, var(--primary), var(--secondary));
  color: white;
  border: none;
  border-radius: 50px;
  padding: 12px 30px;
  font-weight: 500;
  font-size: 1.1rem;
  transition: var(--transition);
  display: inline-flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 4px 15px rgba(108, 99, 255, 0.3);
}

.shopping-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(108, 99, 255, 0.4);
  color: white;
}

.alert-success {
  background-color: rgba(40, 167, 69, 0.1);
  color: var(--success);
  border: 1px solid rgba(40, 167, 69, 0.2);
  border-radius: 50px;
  padding: 8px 15px;
  font-size: 0.9rem;
  margin-bottom: 15px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.coupon-alert.alert-success {
  background-color: #d1e7dd;
  color: #0f5132;
  border-color: #badbcc;
}
.coupon-alert.alert-danger {
  background-color: #f8d7da;
  color: #842029;
  border-color: #f5c2c7;
}
.coupon-alert.alert-info {
  background-color: #cff4fc;
  color: #055160;
  border-color: #b6effb;
}

.applied-coupon-info {
  background-color: #e9f5ff;
  border: 1px solid #b3d7ff;
  color: #004085;
  padding: 10px 15px;
  border-radius: 8px;
  margin-bottom: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.applied-coupon-info .coupon-code {
  font-family: monospace;
  font-weight: bold;
  background: rgba(255,255,255,0.5);
  padding: 2px 6px;
  border-radius: 4px;
}
.applied-coupon-info .remove-coupon-btn {
  background: none;
  border: none;
  color: #004085;
  opacity: 0.7;
  cursor: pointer;
}
.applied-coupon-info .remove-coupon-btn:hover {
  opacity: 1;
}

.toast {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 1056;
}

/* Loading spinner */
.spinner {
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: white;
  animation: spin 1s ease-in-out infinite;
  display: none;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}
.loading .spinner {
  display: inline-block;
}
.loading .btn-text {
  display: none;
}
.btn-outline-primary {
    --bs-btn-color: var(--primary);
    --bs-btn-border-color: var(--primary);
    --bs-btn-hover-color: #fff;
    --bs-btn-hover-bg: var(--primary);
    --bs-btn-hover-border-color: var(--primary);
}

@media (max-width: 991.98px) {
  .cart-grid {
    grid-template-columns: 1fr;
  }
}
@media (max-width: 576px) {
  .cart-items-card {
    padding: 0;
  }
  .cart-table {
    display: block;
    width: 100%;
  }
  .cart-table thead, .cart-table tbody, .cart-table tr {
    display: block;
  }
  .cart-table thead {
    display: none;
  }
  .cart-table tr {
    padding: 1rem;
    border-bottom: 1px solid var(--gray-200);
  }
  .cart-table td {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border: none;
  }
  .cart-table td::before {
    content: attr(data-label);
    font-weight: 600;
    color: var(--gray-600);
  }
  .cart-table td.product-cell {
    display: block;
  }
  .cart-table td.product-cell::before {
    display: none;
  }
  .cart-table td.action-cell {
    justify-content: flex-end;
  }
  .cart-table td.action-cell::before {
    display: none;
  }
}
.back-btn {
  display: inline-block;
  margin: 18px 0 0 18px;
  background: #eee;
  color: #333;
  padding: 6px 14px;
  border-radius: 5px;
  text-decoration: none;
  font-weight: 500;
  transition: background 0.2s;
}

.back-btn:hover {
  background: #ddd;
}

</style>
</head>
<body>
   <?php include 'includes/header.php'; ?>
 <a href="/DUNZO/index.php" class="back-btn">&larr; Back to Home</a>

<div class="cart-container">
  <div class="cart-header">
    <h1><i class="fas fa-shopping-cart me-2"></i>Ready to Checkout?</h1>
<p>Your basket is waiting! Confirm quantities, apply coupons, and place your order.</p>

  </div>

  <?php if(!empty($cart_items)): ?>
  <div class="cart-grid">
    <div class="cart-items-card">
        <div class="table-responsive">
            <table class="table cart-table align-middle">
                <thead>
                    <tr>
                        <th scope="col" style="width: 50%;">Product</th>
                        <th scope="col" class="text-center">Quantity</th>
                        <th scope="col" class="text-end">Total</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($cart_items as $item): ?>
                    <tr>
                        <td data-label="Product" class="product-cell">
                            <div class="product-info">
                                <img src="<?= get_image_path($item['image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" class="product-image">
                                <div class="product-details">
                                    <div class="product-name"><?= htmlspecialchars($item['product_name']) ?></div>
                                    <div class="product-price">₹<?= number_format($item['price'], 2) ?></div>
                                </div>
                            </div>
                        </td>
                        <td data-label="Quantity">
                            <div class="quantity-control">
                                <button type="button" class="quantity-btn" onclick="updateQty(this, -1, <?= $item['product_id'] ?>)">-</button>
                                <input type="number" class="quantity-input" id="qty-<?= $item['product_id'] ?>" value="<?= $item['quantity'] ?>" min="1" onchange="updateQty(this, 0, <?= $item['product_id'] ?>)">
                                <button type="button" class="quantity-btn" onclick="updateQty(this, 1, <?= $item['product_id'] ?>)">+</button>
                            </div>
                        </td>
                        <td data-label="Total" class="text-end">
                            <div class="item-total-price">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
                        </td>
                        <td class="action-cell">
                            <button type="button" class="remove-btn" title="Remove item" onclick="removeItem(this, <?= $item['product_id'] ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="cart-actions-footer">
            <a href="product.php" class="action-btn continue">
              <i class="fas fa-arrow-left me-1"></i> Continue Shopping
            </a>
            <form method="POST" action="cart.php" onsubmit="return confirm('Are you sure you want to empty your cart?');">
                <input type="hidden" name="empty" value="1">
                <button type="submit" class="action-btn empty"><i class="fas fa-trash me-1"></i> Empty Cart</button>
            </form>
        </div>
    </div>

    <div class="cart-summary">
      <h2 class="summary-title">Order Summary</h2>
      
      <?php
        $coupon_message = $_SESSION['coupon_message'] ?? '';
        $coupon_message_type = $_SESSION['coupon_message_type'] ?? '';
        unset($_SESSION['coupon_message'], $_SESSION['coupon_message_type']);
        if ($coupon_message):
      ?>
      <div class="coupon-alert alert-<?= htmlspecialchars($coupon_message_type) ?>">
        <i class="fas <?= $coupon_message_type === 'success' ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
        <span><?= htmlspecialchars($coupon_message) ?></span>
      </div>
      <?php endif; ?>
      
      <?php if ($applied_coupon): ?>
        <div class="applied-coupon-info">
            <div>Applied: <span class="coupon-code"><?= htmlspecialchars($applied_coupon['code']) ?></span></div>
            <button class="remove-coupon-btn" onclick="removeCoupon()" title="Remove coupon"><i class="fas fa-times"></i></button>
        </div>
      <?php else: ?>
        <?php if (!empty($available_coupons)): ?>
        <button type="button" class="btn btn-outline-primary w-100 mb-3" data-bs-toggle="modal" data-bs-target="#couponsModal">
          <i class="fas fa-tags me-2"></i> View Available Coupons
        </button>
        <?php endif; ?>
      <?php endif; ?>
      
      <div class="summary-details">
        <div class="summary-row">
          <span>Subtotal (<?= $item_count ?> items)</span>
          <span id="summary-subtotal">₹<?= number_format($subtotal, 2) ?></span>
        </div>
        
        <div class="summary-row savings" id="summary-discount-row" style="<?= $discount > 0 ? 'display: flex;' : 'display: none;' ?>">
          <span>Discount</span>
          <span id="summary-discount-value">-₹<?= number_format($discount, 2) ?></span>
        </div>
        
        <div class="summary-row">
          <span>Shipping</span>
          <span id="summary-shipping"><?= $shipping == 0 ? 'FREE' : '₹' . number_format($shipping, 2) ?></span>
        </div>
        
        <div class="summary-row">
          <span>Tax (<?= ($tax_rate * 100) ?>%)</span>
          <span id="summary-tax">₹<?= number_format($tax, 2) ?></span>
        </div>
        
        <div class="summary-row total">
          <span>Total</span>
          <span id="summary-total">₹<?= number_format($total, 2) ?></span>
        </div>
      </div>
      
      <button type="button" class="checkout-btn" onclick="proceedToCheckout()">
        <span class="btn-text">Proceed to Checkout</span>
        <span class="spinner"></span>
      </button>
    </div>
  </div>
  <?php else: ?>
    <div class="empty-cart-modern">
      <div class="empty-cart-icon"><i class="fas fa-box-open"></i></div>
      <h2>Your Cart is Empty</h2>
<p>Nothing to checkout yet. Discover fresh deals and add your favorites to the cart.</p>
<a href="product.php" class="explore-btn">
   <i class="fas fa-shopping-basket me-2"></i> Start Shopping
</a>

    </div>
  <?php endif; ?>
  </div>
</div>

<!-- Coupons Modal -->
<div class="modal fade" id="couponsModal" tabindex="-1" aria-labelledby="couponsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content" style="border-radius: var(--border-radius);">
      <div class="modal-header">
        <h5 class="modal-title" id="couponsModalLabel"><i class="fas fa-tags me-2 text-primary"></i>Available Coupons</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Hidden form for applying coupon -->
        <form method="post" id="coupon-form" style="display: none;">
          <input type="text" name="coupon_code" id="coupon-input">
          <button type="submit" name="apply_coupon"></button>
        </form>
        <?php if (!empty($available_coupons)): ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($available_coupons as $coupon): ?>
              <?php $is_eligible = $subtotal >= $coupon['min_spend']; ?>
              <li class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center <?= !$is_eligible ? 'opacity-50' : '' ?>">
                <div>
                  <h6 class="mb-1 font-monospace <?= $is_eligible ? 'text-success' : '' ?>"><?= htmlspecialchars($coupon['code']) ?></h6>
                  <p class="mb-1 small">
                    <strong><?= rtrim(rtrim(number_format($coupon['discount_percentage'], 2), '0'), '.') ?>% off</strong>
                    <?php if ($coupon['min_spend'] > 0): ?>
                      on orders above ₹<?= number_format($coupon['min_spend'], 2) ?>.
                    <?php endif; ?>
                  </p>
                  <?php if ($coupon['expiry_date']): ?>
                    <small class="text-muted">Expires: <?= date('M d, Y', strtotime($coupon['expiry_date'])) ?></small>
                  <?php endif; ?>
                </div>
                <?php if ($is_eligible): ?>
                  <button class="btn btn-sm btn-outline-primary flex-shrink-0" onclick="applyCoupon('<?= htmlspecialchars($coupon['code']) ?>')">Apply</button>
                <?php else: ?>
                  <span class="badge bg-light text-dark border flex-shrink-0">Spend ₹<?= number_format($coupon['min_spend'] - $subtotal, 2) ?> more</span>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="text-center my-4">No coupons available for you at the moment.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>



<script>
function applyCoupon(code) {
  document.getElementById('coupon-input').value = code;
  document.querySelector('#coupon-form button[name="apply_coupon"]').click();
}

function removeCoupon() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'cart.php';
    form.style.display = 'none';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'remove_coupon';
    input.value = '1';
    form.appendChild(input);

    document.body.appendChild(form);
    form.submit();
}

function updateQty(element, change, productId) {
  let input;
  if (element.tagName === 'INPUT') {
    input = element;
  } else {
    input = document.getElementById('qty-' + productId);
    if (change !== 0) {
      let newValue = parseInt(input.value) + change;
      if (newValue < 1) newValue = 1;
      input.value = newValue;
    }
  }
  
  const quantity = parseInt(input.value);
  
  const cartItem = input.closest('tr');
  cartItem.style.opacity = '0.7';
  
  const formData = new FormData();
  formData.append('update_qty', '1');
  formData.append('product_id', productId);
  formData.append('quantity', quantity);
  formData.append('ajax', '1');
  
  fetch('cart.php', {
    method: 'POST',
    body: formData,
  })
  .then(response => response.json())
  .then(data => {
    if (data.status === "success") {
      // Update item total price
      const itemTotalPriceEl = cartItem.querySelector('.item-total-price');
      if (itemTotalPriceEl) itemTotalPriceEl.textContent = data.item_total_price;

      // Update cart badge in navbar
      const cartBadge = document.querySelector('.navbar .cart-badge');
      if (cartBadge) {
        if (data.item_count > 0) {
          cartBadge.textContent = data.item_count;
          cartBadge.style.display = 'flex';
        } else {
          location.reload();
          return;
        }
      }

      // Update summary
      document.querySelector('.summary-row span:first-child').textContent = `Subtotal (${data.item_count} items)`;
      document.getElementById('summary-subtotal').textContent = data.subtotal;
      document.getElementById('summary-shipping').textContent = data.shipping;
      document.getElementById('summary-tax').textContent = data.tax;
      document.getElementById('summary-total').textContent = data.total;

      // Update discount row
      const discountRow = document.getElementById('summary-discount-row');
      if (data.discount_applied) {
        document.getElementById('summary-discount-value').textContent = data.discount;
        discountRow.style.display = 'flex';
      } else {
        discountRow.style.display = 'none';
      }
    } else {
      showToast('Error', data.message || 'Error updating quantity', 'danger');
    }
  })
  .catch(error => {
    showToast('Network Error', 'Could not connect to the server. Please try again.', 'danger');
  })
  .finally(() => {
    cartItem.style.opacity = '1';
  });
}

function removeItem(buttonElement, productId) {
  if (!confirm('Are you sure you want to remove this item from your cart?')) {
    return;
  }
  
  const cartItem = buttonElement.closest('tr');
  if (cartItem) {
      cartItem.style.opacity = '0.5';
  }

  const formData = new FormData();
  formData.append('remove', productId);
  formData.append('ajax', '1');

  fetch('cart.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.status === 'success') {
      location.reload();
    } else {
      showToast('Error', data.message || 'Error removing item.', 'danger');
      if (cartItem) { cartItem.style.opacity = '1'; }
    }
  })
  .catch(error => {
    showToast('Network Error', 'Could not connect to the server. Please try again.', 'danger');
    if (cartItem) { cartItem.style.opacity = '1'; }
  });
}

function proceedToCheckout() {
  const checkoutBtn = document.querySelector('.checkout-btn');
  checkoutBtn.classList.add('loading');
  checkoutBtn.disabled = true;
  
  const formData = new FormData();
  formData.append('proceed_to_checkout', '1');

  fetch('cart.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.status === 'success') {
      window.location.href = data.redirect;
    } else {
      showToast(data.message || 'An error occurred. Please try again.', 'error');
      checkoutBtn.classList.remove('loading');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showToast('Network error. Please try again.', 'error');
    checkoutBtn.classList.remove('loading');
  });
}

function showToast(message, type = 'success') {
  // Remove existing toasts
  const existingToasts = document.querySelectorAll('.toast');
  existingToasts.forEach(toast => toast.remove());
  
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `
    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
    <span>${message}</span>
  `;
  
  document.body.appendChild(toast);
  
  // Remove toast after 3 seconds
  setTimeout(() => {
    toast.remove();
  }, 3000);
}
</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>