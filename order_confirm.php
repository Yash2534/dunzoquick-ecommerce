<?php
session_start();
include 'config.php';

// Basic validation
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
if (!isset($_GET['order_id'])) {
    die("Order ID is missing.");
}

$user_id = $_SESSION['user_id'];
$order_id = (int)$_GET['order_id'];

// Fetch order details, ensuring it belongs to the logged-in user
$stmt_order = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt_order->bind_param("ii", $order_id, $user_id);
$stmt_order->execute();
$order_result = $stmt_order->get_result();

if ($order_result->num_rows === 0) {
    die('Order not found or you do not have permission to view this page.');
}

$order = $order_result->fetch_assoc();

// Check if the order is new enough for cancellation
$time_since_order = time() - strtotime($order['created_at']);
$cancellation_window = 60; // 60 seconds (1 minute)
$can_cancel = ($time_since_order < $cancellation_window && $order['status'] === 'pending');
$stmt_order->close();

function escapeHTML($str)
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation - <?= escapeHTML($order['order_number']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0ea86f;
            --secondary: #20c997;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #28a745;
            --danger: #dc3545;
            --gray-100: #f8f9fa;
            --gray-200: #e9ecef;
            --gray-600: #6c757d;
            --gray-800: #343a40;
            --border-radius: 16px;
            --box-shadow: 0 8px 25px rgba(0,0,0,0.07);
        }
        body {
            background-color: #f7f8fa;
            font-family: 'Poppins', sans-serif;
            color: var(--gray-800);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .main-container {
            max-width: 850px;
            width: 100%;
        }
        .order-status-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: 1px solid var(--gray-200);
            text-align: center;
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
        }
        .order-status-card .success-icon {
            font-size: 4rem;
            color: var(--success);
            margin-bottom: 1rem;
            animation: popIn 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        }
        @keyframes popIn {
            0% { transform: scale(0.5); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        .order-status-card h2 {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        .order-status-card .status-message {
            font-size: 1.1rem;
            color: var(--gray-600);
            margin-bottom: 2rem;
        }
        .order-status-card .order-meta {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
            font-size: 0.9rem;
            color: var(--gray-600);
        }
        .order-meta strong {
            color: var(--dark);
            display: block;
            font-weight: 600;
        }
        .cancellation-widget {
            background-color: #fff9f0;
            border: 1px solid #ffecb3;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 2rem;
            display: none;
            align-items: center;
            gap: 20px;
        }
        .cancellation-widget.visible {
            display: flex;
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .cancellation-widget p { margin: 0; font-weight: 500; color: #b95000; flex-grow: 1; line-height: 1.4; }
        .cancellation-widget p strong { font-size: 1.1em; color: #e65100; }
        .countdown-timer { position: relative; width: 50px; height: 50px; flex-shrink: 0; }
        .timer-ring { transform: rotate(-90deg); transform-origin: 50% 50%; }
        .timer-ring circle { transition: stroke-dashoffset 1s linear; }
        .ring-bg { fill: none; stroke: #ffe8cc; stroke-width: 4; }
        .ring-progress { fill: none; stroke: #ff9800; stroke-width: 4; stroke-linecap: round; }
        .timer-text { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-weight: 700; font-size: 1rem; color: #e65100; } .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
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
                 <a href="index.php" class="back-btn">&larr; Back to Home</a>

    <div class="main-container">

        <div class="order-status-card" id="orderStatusCard">
            <div id="success-view">
                <div class="success-icon"><i class="fas fa-check-circle"></i></div>
                <h2>Order Placed Successfully!</h2>
                <p class="status-message">Your items are on their way. You can track the status below.</p>
            </div>
            <div id="cancelled-view" style="display: none;">
                <div class="success-icon" style="color: var(--danger);"><i class="fas fa-times-circle"></i></div>
                <h2>Order Cancelled</h2>
                <p class="status-message">Your order has been successfully cancelled and a refund has been initiated.</p>
            </div>
            <div id="expired-view" style="display: none;">
                <div class="success-icon" style="color: var(--primary);"><i class="fas fa-shipping-fast"></i></div>
                <h2>Order Confirmed</h2>
                <p class="status-message">The cancellation window has expired. Your order is now confirmed and being prepared.</p>
            </div>

            <div class="order-meta">
                <div>Order ID<strong>#<?= escapeHTML($order['order_number']) ?></strong></div>
                <div>Delivery ETA<strong>15-20 mins</strong></div>
                <div>Delivery To<strong><?= nl2br(escapeHTML($order['delivery_address'])) ?></strong></div>
            </div>

            <?php if ($can_cancel): ?>
            <div class="cancellation-widget" id="cancellationWidget">
                <div class="countdown-timer">
                    <svg class="timer-ring" viewBox="0 0 36 36">
                        <circle class="ring-bg" cx="18" cy="18" r="15.9155"></circle>
                        <circle class="ring-progress" id="timerRingProgress" cx="18" cy="18" r="15.9155" stroke-dasharray="100 100" stroke-dashoffset="0"></circle>
                    </svg>                    
                </div>
                <p>You have <strong>1 minute</strong> to cancel this order for a full refund.</p>
                <button class="btn btn-danger flex-shrink-0" data-bs-toggle="modal" data-bs-target="#cancelConfirmationModal" id="cancelBtn">
                    <i class="fas fa-times-circle me-1"></i> Cancel
                </button>
            </div>
            <?php endif; ?>

            <div class="mt-4" id="tracking-actions" style="display: <?= $can_cancel ? 'none' : 'block' ?>;">
                <a href="track_order.php?order_number=<?= escapeHTML($order['order_number']) ?>" class="btn btn-primary me-2">
                    <i class="fas fa-truck me-2"></i>Track Order Live
                </a>
                <a href="invoice.php?order_id=<?= $order['id'] ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-file-invoice me-2"></i>View Invoice
                </a>
            </div>
        </div>
    </div>

    <!-- Cancellation Confirmation Modal -->
    <div class="modal fade" id="cancelConfirmationModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px;">
          <div class="modal-header border-0 pb-0"><h5 class="modal-title" id="cancelModalLabel">⚠️ Confirm Cancellation</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
          <div class="modal-body pt-2"><p>Do you really want to cancel this order? The refund will be credited instantly.</p></div>
          <div class="modal-footer border-0"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep Order</button><button type="button" class="btn btn-danger" id="confirmCancelBtn">Yes, Cancel</button></div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    <?php if ($can_cancel): ?>
    document.addEventListener('DOMContentLoaded', function () {
        const cancellationWidget = document.getElementById('cancellationWidget');
        const timerText = document.getElementById('timerText');
        const timerRing = document.getElementById('timerRingProgress');
        const cancelBtn = document.getElementById('cancelBtn');
        const confirmCancelBtn = document.getElementById('confirmCancelBtn');
        const cancelModal = new bootstrap.Modal(document.getElementById('cancelConfirmationModal'));

        cancellationWidget.classList.add('visible');

        const totalDuration = <?= $cancellation_window ?>;
        let timeLeft = Math.max(0, totalDuration - <?= $time_since_order ?>);
        const circumference = 2 * Math.PI * timerRing.r.baseVal.value;
        timerRing.style.strokeDasharray = `${circumference} ${circumference}`;

        function updateTimerDisplay() {
            const progress = timeLeft / totalDuration;
            const offset = circumference - progress * circumference;
            timerRing.style.strokeDashoffset = offset;
        }

        const timerInterval = setInterval(() => {
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                cancellationWidget.style.display = 'none';
                document.getElementById('success-view').style.display = 'none';
                document.getElementById('expired-view').style.display = 'block';
                document.getElementById('tracking-actions').style.display = 'block';
                cancelBtn.disabled = true;
                return;
            }
            timeLeft--;
            updateTimerDisplay();
        }, 1000);

        confirmCancelBtn.addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Cancelling...';
            fetch('cancel_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },

                body: JSON.stringify({ order_id: <?= $order_id ?> })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('success-view').style.display = 'none';
                    document.getElementById('cancelled-view').style.display = 'block';
                    cancellationWidget.style.display = 'none';
                    cancelModal.hide();
                    clearInterval(timerInterval);
                } else {
                    alert(data.message || 'Failed to cancel order.');
                    this.disabled = false;
                    this.innerHTML = 'Yes, Cancel';
                }
            });
        });

        updateTimerDisplay();
    });
    <?php else: ?>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('success-view').style.display = 'none';
        document.getElementById('expired-view').style.display = 'block';
        document.getElementById('tracking-actions').style.display = 'block';
    });
    <?php endif; ?>
    </script>
</body>
</html>