<?php
session_start();
include 'config.php';
$tax_rate = 0.09;

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
    die('Order not found or you do not have permission to view this invoice.');
}

$order = $order_result->fetch_assoc();
$stmt_order->close();

// Fetch order items
$stmt_items = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items_result = $stmt_items->get_result();
$items = $items_result->fetch_all(MYSQLI_ASSOC);
$stmt_items->close();
// Fetch user details (for billing info)
$stmt_user = $conn->prepare("SELECT full_name, email FROM users WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result();
$user = $user_result->fetch_assoc();
$stmt_user->close();

function escapeHTML($str)
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= escapeHTML($order['order_number']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0ea86f;
            /* Blinkit green */
            --secondary: #20c997;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #28a745;
            --danger: #dc3545;
            --gray-100: #f8f9fa;
            --gray-200: #e9ecef;
            --gray-300: #dee2e6;
            --gray-600: #6c757d;
            --gray-800: #343a40;
            --border-radius: 16px;
            --box-shadow: 0 8px 25px rgba(0, 0, 0, 0.07);
        }

        .main-container {
            max-width: 850px;
            margin: 40px auto;
        }

        body {
            background-color: #f7f8fa;
            font-family: 'Poppins', sans-serif;
            color: var(--gray-800);
        }

        .invoice-wrapper {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: 1px solid var(--gray-200);
        }

        .invoice-actions {
            padding: 1rem 2rem;
            background-color: var(--gray-100);
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .invoice-actions .btn {
            border-radius: 50px;
            padding: 8px 20px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background: #0c8056;
            border-color: #0c8056;
            color: white;
        }

        .btn-outline-secondary {
            background: transparent;
            color: var(--gray-800);
            border-color: var(--gray-300);
        }

        .btn-outline-secondary:hover {
            background: var(--gray-800);
            color: white;
            border-color: var(--gray-800);
        }

        .invoice-container {
            padding: 2.5rem;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .logo {
            font-size: 2rem;
            font-weight: 700;
        }

        .logo .yellow {
            color: #febd69;
        }

        .logo .green {
            color: var(--primary);
        }

        .invoice-details h2 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }

        .invoice-details .order-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
        }

        .invoice-details p {
            font-size: 0.9rem;
            color: var(--gray-600);
            margin-bottom: 2px;
        }

        .billing-details {
            margin-bottom: 2.5rem;
        }

        .billing-details h5 {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.75rem;
            font-size: 1rem;
        }

        .billing-details address {
            font-size: 0.95rem;
            color: var(--gray-600);
            line-height: 1.7;
            font-style: normal;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            font-weight: 600;
            color: var(--gray-600);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--gray-300);
            background: transparent;
            padding: 0.75rem 1rem;
        }

        .table td {
            vertical-align: middle;
            padding: 1rem;
            border-top: 1px solid var(--gray-200);
        }

        .table tbody tr:first-child td {
            border-top: none;
        }

        .item-name {
            font-weight: 600;
        }

        .summary-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }

        .summary-table {
            width: 100%;
            max-width: 350px;
        }

        .summary-table td {
            padding: 0.5rem 0;
            border: none;
        }

        .summary-table .label {
            color: var(--gray-600);
        }

        .summary-table .value {
            text-align: right;
            font-weight: 600;
        }

        .summary-table .grand-total .label,
        .summary-table .grand-total .value {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--dark);
            padding-top: 1rem;
            border-top: 2px solid var(--gray-300);
        }

        .invoice-footer {
            text-align: center;
            margin-top: 2.5rem;
            padding: 1.5rem;
            background-color: var(--gray-100);
            border-top: 1px solid var(--gray-200);
        }

        .invoice-footer p {
            color: var(--gray-600);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        @media print {
            body {
                background-color: white;
            }

            .invoice-wrapper {
                margin: 0;
                padding: 0;
                box-shadow: none;
                border: none;
                border-radius: 0;
            }

            .invoice-actions {
                display: none !important;
            }

            .invoice-container {
                padding: 20px;
            }

            .logo .green {
                color: #0ea86f !important;
                /* Ensure color prints */
            }
        }
    </style>
</head>

<body>
    <div class="main-container">
        <div class="invoice-wrapper">
            <div class="invoice-actions">
                <a href="order.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Orders</a>
                <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print me-2"></i>Print Invoice</button>
            </div>

            <div class="invoice-container">
                <header class="invoice-header">
                    <div>
                        <div class="logo"><span class="yellow">dun</span><span class="green">zo</span></div>
                        <p class="text-muted mb-0 mt-2">
                            Dunzo Technologies Pvt. Ltd.<br>
                            45 Market Street, Rajkot, Gujarat – 360001
                        </p>
                    </div>
                    <div class="text-end invoice-details">
                        <h2>Invoice</h2>
                        <div class="order-number"><?= escapeHTML($order['order_number']) ?></div>
                        <p class="mt-2"><strong>Date:</strong> <?= date('M d, Y', strtotime($order['created_at'])) ?></p>
                        <p><strong>Payment ID:</strong> <?= escapeHTML($order['payment_id'] ?? 'N/A') ?></p>
                        <?php if (isset($order['delivery_priority']) && $order['delivery_priority'] === 'priority'): ?>
                            <p class="mt-2"><span class="badge rounded-pill" style="background-color: #e6f6f2; color: #0ea86f; border: 1px solid #0ea86f;"><i class="fas fa-crown me-1"></i> Priority</span></p>
                        <?php endif; ?>
                    </div>
                </header>

                <div class="row billing-details">
                    <div class="col-md-6">
                        <h5>Billed To:</h5>
                        <address>
                            <strong><?= escapeHTML($user['full_name']) ?></strong><br>
                            <?= escapeHTML($user['email']) ?>
                        </address>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h5>Shipping To:</h5>
                        <address>
                            <?= nl2br(escapeHTML($order['delivery_address'])) ?>
                        </address>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th>Item Description</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1;
                            foreach ($items as $item): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td>
                                        <div class="item-name"><?= escapeHTML($item['product_name']) ?></div>
                                    </td>
                                    <td class="text-center"><?= $item['quantity'] ?></td>
                                    <td class="text-end">₹<?= number_format($item['price'], 2) ?></td>
                                    <td class="text-end fw-bold">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="row mt-4">
                    <div class="col-lg-7 col-md-6 text-muted small">
                        Note: All prices are in INR. This is a computer-generated invoice and does not require a signature.
                    </div>
                    <div class="col-lg-5 col-md-6">
                        <table class="summary-table">

                            <tr class="grand-total">
                                <td class="label">Grand Total</td>
                                <td class="value">₹<?= number_format($order['total_amount'], 2) ?></td>
                            </tr>

                        </table>
                    </div>
                </div>

                <footer class="invoice-footer">
                    <p>Thank you for shopping with <strong>Dunzo</strong>! We appreciate your trust in us and look forward to serving you again.</p>
                    <p>If you have any questions or need support, please reach out to us at
                        <a href="mailto:Dunzo25@gmail.com">Dunzo25@gmail.com</a> or call our helpline at <strong>9723653140</strong> (available 24x7).
                    </p>

                </footer>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>