<?php
session_start();
include 'config.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Check if order number is provided
if (!isset($_GET['order_number'])) {
    die("Order number is missing.");
}
$order_number = $_GET['order_number'];

// Fetch order details, ensuring it belongs to the logged-in user
$stmt_order = $conn->prepare("SELECT * FROM orders WHERE order_number = ? AND user_id = ?");
$stmt_order->bind_param("si", $order_number, $user_id);
$stmt_order->execute();
$order_result = $stmt_order->get_result();

if ($order_result->num_rows === 0) {
    die('Order not found or you do not have permission to view this page.');
}
$order = $order_result->fetch_assoc();
$stmt_order->close();

// Fetch order items
$stmt_items = $conn->prepare("
    SELECT oi.*, p.image 
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt_items->bind_param("i", $order['id']);
$stmt_items->execute();
$items_result = $stmt_items->get_result();
$items = $items_result->fetch_all(MYSQLI_ASSOC);
$stmt_items->close();

// Helper function to generate a clean, root-relative image path
function get_image_path($db_path) {
    $default_image = '/DunzoQuick/Image/no-image.png';
    if (empty(trim((string)$db_path))) {
        return $default_image;
    }
    $path = preg_replace('#^(\.\./|/DUNZO/|Product/)#', '', (string)$db_path);
    $path = ltrim($path, '/');
    if (strpos($path, 'Image/') !== 0 && strpos($path, 'PICTURE/') !== 0) {
        $path = 'Image/' . $path;
    }
    return '/DunzoQuick/' . htmlspecialchars($path);
}

// Define the order tracking stages
$all_statuses = [
    'pending' => ['icon' => 'fa-receipt', 'title' => 'Order Placed', 'text' => 'We have received your order and are processing it.'],
    'confirmed' => ['icon' => 'fa-check-double', 'title' => 'Order Confirmed', 'text' => 'Your order has been confirmed by the store.'],
    'preparing' => ['icon' => 'fa-box-open', 'title' => 'Preparing Your Order', 'text' => 'We’re packing your happiness in a box! 🎁'],
    'out_for_delivery' => ['icon' => 'fa-shipping-fast', 'title' => 'Out for Delivery', 'text' => 'Your order is out for a joyride! 🚚💨'],
    'delivered' => ['icon' => 'fa-house-user', 'title' => 'Delivered', 'text' => 'Delivered with love. Enjoy! ❤️'],
];

$cancelled_status = ['icon' => 'fa-times-circle', 'title' => 'Order Cancelled', 'text' => 'This order has been cancelled.'];

$current_status = $order['status'];
$status_keys = array_keys($all_statuses);
$current_status_index = array_search($current_status, $status_keys);
// If status is not in the standard flow (like 'cancelled'), $current_status_index will be false.
if ($current_status_index === false && $current_status !== 'cancelled') {
    // Fallback for any other status, treat as pending to prevent errors
    $current_status_index = 0; 
}

// Placeholder for delivery partner info
$delivery_partner = [
    'name' => 'Delivery Boy',
    'vehicle' => 'KA-01-XY-1234',
    'eta' => '10 mins',
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Track Order #<?= htmlspecialchars($order['order_number']) ?> - DUNZO</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* General */
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }
        .tracking-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 15px;
        }
        .tracking-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .tracking-header h1 {
            font-weight: 700;
            color: #212529;
        }
        .tracking-header p {
            color: #6c757d;
            font-size: 1.1rem;
        }
        .tracking-grid {
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 30px;
        }
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.05);
            background: #fff;
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #f0f0f0;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 1.25rem;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
        }

        /* Order Summary (Left Column) */
        .order-summary .order-item {
            display: flex;
            gap: 15px;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .order-summary .order-item:last-child { border-bottom: none; }
        .order-summary .order-item img {
            width: 50px;
            height: 50px;
            object-fit: contain;
            border-radius: 8px;
            background-color: #f8f9fa;
        }
        .order-summary .item-info .name { font-weight: 500; }
        .order-summary .item-info .qty { font-size: 0.9rem; color: #6c757d; }
        .order-summary .item-price { font-weight: 600; }
        .order-summary .order-total { padding: 1.25rem; }
        .order-summary .grand-total { font-size: 1.2rem; font-weight: 700; }

        /* Tracking Details (Right Column) */
        .tracking-details .card-body { padding: 30px; }

 

        /* Delivery Partner Card */
        .delivery-partner-card {
            display: flex;
            align-items: center;
            gap: 20px;
            background-color: #f0fff4; /* Light green background */
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            border: 1px solid #c6f6d5;
        }
        .delivery-partner-card img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .partner-info h5 {
            margin-bottom: 4px;
            font-weight: 600;
            color: #047857; /* Dark green */
        }
        .partner-info p {
            margin-bottom: 0;
            font-size: 0.95rem;
            color: #333;
        }
        .partner-info .trust-badges {
            margin-top: 8px;
        }
        .partner-info .badge {
            font-size: 0.75rem;
            padding: 4px 8px;
            background-color: #e0f2f1 !important;
            color: #00796b !important;
            border: 1px solid #b2dfdb;
            font-weight: 500;
        }
        .partner-actions .btn {
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-success { background-color: #28a745; border-color: #28a745; }
        .btn-primary { background-color: #0d6efd; border-color: #0d6efd; }

        /* Timeline */
        .timeline {
            position: relative;
            padding-left: 20px;
        }
        .timeline-item {
            position: relative;
            padding-left: 35px;
            padding-bottom: 35px;
        }
        .timeline-item:last-child { padding-bottom: 0; }

        .timeline-item::before { /* The line connecting dots */
            content: '';
            position: absolute;
            left: 8px;
            top: 20px;
            width: 2px;
            height: 100%;
            background: #e9ecef;
        }
        .timeline-item:last-child::before { display: none; }

        .timeline-item.completed::before { background-color: #28a745; }

        .timeline-icon {
            position: absolute;
            left: 0;
            top: 0;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: #fff;
            border: 2px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1;
            transition: all 0.3s ease;
        }
        .timeline-item.completed .timeline-icon {
            border-color: #28a745;
            background-color: #28a745;
        }
        .timeline-item.active .timeline-icon {
            border-color: #ffc107;
            background-color: #ffc107;
            transform: scale(1.2);
        }
        .timeline-item.pending .timeline-icon {
            border-color: #adb5bd;
            background-color: #fff;
        }
        .timeline-item.cancelled .timeline-icon {
            border-color: #dc3545;
            background-color: #dc3545;
        }
        .timeline-icon i {
            color: white;
            font-size: 0.8rem;
        }
        .timeline-item.pending .timeline-icon i { color: #adb5bd; }

        .timeline-content h5 {
            font-weight: 600;
            font-size: 1.05rem;
            margin-bottom: 5px;
        }
        .timeline-content p {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 5px;
        }
        .timeline-content .timeline-time {
            font-size: 0.8rem;
            color: #6c757d;
            font-weight: 500;
        }
        .timeline-item.active .timeline-content h5 {
            color: #ffc107;
        }
        .timeline-item.cancelled .timeline-content h5 {
            color: #dc3545;
        }
        
        /* Rating Section */
        .rating-section {
            background-color: #f0fff4;
            border: 1px solid #c6f6d5;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            margin-top: 30px;
        }
        .rating-section h5 {
            font-weight: 600;
            color: #047857;
            margin-bottom: 15px;
        }
        .star-rating {
            font-size: 2.5rem;
            color: #ffc107;
            cursor: pointer;
        }
        .star-rating .fa-star:hover,
        .star-rating .fa-star.selected {
            color: #ff9800;
        }
        .rating-section .btn-thanks {
            background: linear-gradient(135deg, #ffc107, #ff9800);
            border: none;
            color: white;
            font-weight: 600;
            padding: 10px 25px;
            border-radius: 50px;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        .rating-section .btn-thanks:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(255, 152, 0, 0.4);
        }
        .rating-section p {
            margin-top: 15px;
            color: #6c757d;
            font-size: 0.9rem;
        }

        @media (max-width: 992px) {
            .tracking-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="tracking-container">
        <div class="tracking-header">
            <h1>Track Your Order</h1>
            <p>Order #<?= htmlspecialchars($order['order_number']) ?></p>
        </div>

        <div class="tracking-grid">
            <!-- Left Column: Order Summary -->
            <div class="order-summary">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-shopping-basket me-2"></i>Order Summary
                    </div>
                    <div class="card-body">
                        <?php foreach ($items as $item): ?>
                        <div class="order-item">
                            <img src="<?= get_image_path($item['image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
                            <div class="item-info">
                                <div class="name"><?= htmlspecialchars($item['product_name']) ?></div>
                                <div class="qty">Qty: <?= $item['quantity'] ?></div>
                            </div>
                            <div class="item-price">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="order-total">
                        <div class="row mb-2">
                            <div class="col">Subtotal</div>
                            <div class="col text-end">₹<?= number_format($order['subtotal'], 2) ?></div>
                        </div>
                        <?php if ($order['discount_amount'] > 0): ?>
                        <div class="row mb-2 text-success">
                            <div class="col">Discount</div>
                            <div class="col text-end">-₹<?= number_format($order['discount_amount'], 2) ?></div>
                        </div>
                        <?php endif; ?>
                        <div class="row mb-2">
                            <div class="col">Shipping</div>
                            <div class="col text-end">₹<?= number_format($order['shipping_amount'], 2) ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">Tax</div>
                            <div class="col text-end">₹<?= number_format($order['tax_amount'], 2) ?></div>
                        </div>
                        <hr>
                        <div class="row grand-total">
                            <div class="col">Total</div>
                            <div class="col text-end">₹<?= number_format($order['total_amount'], 2) ?></div>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <a href="invoice.php?order_id=<?= $order['id'] ?>" class="btn btn-outline-primary w-100">
                            <i class="fas fa-download me-2"></i>Download Invoice
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Column: Tracking Details -->
            <div class="tracking-details">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-truck me-2"></i>Delivery Status
                    </div>
                    <div class="card-body">
                        <?php if ($current_status !== 'cancelled'): ?>
                            <!-- Map and Delivery Partner -->
                            <div class="map-placeholder"></div>
                            <div class="delivery-partner-card">
                                <div class="partner-info flex-grow-1">
                                    <h5><?= $delivery_partner['name'] ?> is on the way! 🚴💨</h5>
                                    <p>Arriving in approx. <strong><?= $delivery_partner['eta'] ?></strong></p>
                                </div> 
                                <div class="partner-actions">
                                    <a href="#" class="btn btn-success btn-sm"><i class="fas fa-phone"></i></a>
                                    <a href="#" class="btn btn-primary btn-sm"><i class="fas fa-comment-dots"></i></a>
                                </div>
                            </div>

                            <!-- Timeline -->
                            <div class="timeline">
                                <?php foreach ($all_statuses as $status_key => $status_details):
                                    $status_index = array_search($status_key, $status_keys, true);
                                    $class = 'pending';
                                    if ($current_status_index !== false) {
                                        if ($status_index < $current_status_index) {
                                            $class = 'completed';
                                        } elseif ($status_index === $current_status_index) {
                                            $class = 'active';
                                        }
                                    }
                                ?>
                                <div class="timeline-item <?= $class ?>">
                                    <div class="timeline-icon">
                                        <?php if ($class === 'completed' || $class === 'active'): ?>
                                            <i class="fas fa-check"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="timeline-content">
                                        <h5><?= $status_details['title'] ?></h5>
                                        <p><?= $status_details['text'] ?></p>
                                        <?php
                                            $timestamp = 0;
                                            if ($status_key === 'pending') {
                                                $timestamp = strtotime($order['created_at']);
                                            } elseif ($class === 'active' || ($class === 'completed' && $status_key === 'delivered')) {
                                                $timestamp = strtotime($order['updated_at']);
                                            }
                                            if ($timestamp > 0) { echo '<small class="timeline-time"><i class="far fa-clock me-1"></i>' . date('h:i A, M d', $timestamp) . '</small>'; }
                                        ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <!-- Cancelled Status -->
                            <div class="timeline">
                                <div class="timeline-item cancelled">
                                    <div class="timeline-icon"><i class="fas <?= $cancelled_status['icon'] ?>"></i></div>
                                    <div class="timeline-content">
                                        <h5><?= $cancelled_status['title'] ?></h5>
                                        <p><?= $cancelled_status['text'] ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const stars = document.querySelectorAll('.star-rating .fa-star');
            
            stars.forEach(star => {
                star.addEventListener('mouseover', function() {
                    resetStars();
                    const rating = this.dataset.value;
                    for (let i = 0; i < rating; i++) {
                        stars[i].classList.replace('far', 'fas');
                    }
                });

                star.addEventListener('click', function() {
                    const rating = this.dataset.value;
                    // Here you would typically send the rating to the server
                    alert(`You rated this delivery ${rating} stars. Thank you!`);
                });
            });

            function resetStars() { stars.forEach(s => s.classList.replace('fas', 'far')); }
        });
    </script>
</body>
</html>