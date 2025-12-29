<?php
session_start();
include 'config.php';
$tax_rate = 0.09;
// Assuming you've installed Razorpay SDK via Composer

$autoloader = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoloader)) {
    // Provide a clear error message if the autoloader is missing.
    $composer_error_message = "<h2>Composer Autoloader Not Found</h2>";
    $composer_error_message .= "<p>The file <code>vendor/autoload.php</code> is missing. This file is essential for the Razorpay payment gateway to work.</p>";
    $composer_error_message .= "<p>Please run <code>composer install</code> or <code>composer require razorpay/razorpay</code> in your project's root directory (<code>C:\xampp\htdocs\DUNZO\</code>) to generate it.</p>";
    die($composer_error_message);
}
require $autoloader;
use Razorpay\Api\Api;

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];


// Fetch user details
$stmt_user = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();
$is_prime_member = ($user && $user['membership_expiry_date'] && strtotime($user['membership_expiry_date']) >= time());

$address_line1 = '';
$address_line2 = '';
$city = '';
$pincode = '';
$state = '';

// --- Location Data Handling ---
// Priority 1: Use the location just set in the session (from location.php).
if (isset($_SESSION['location']) && !empty($_SESSION['location'])) {
    $full_address = $_SESSION['location'];
    $city = $_SESSION['city'] ?? '';
    $pincode = $_SESSION['pincode'] ?? '';

    // Simple parsing for the full address string from session
    $parts = explode(',', $full_address);
    $address_line1 = trim($parts[0] ?? '');
    if (count($parts) > 3) {
        $address_line2 = trim($parts[1] ?? '');
    }
    // If city/pincode weren't in session, try to extract them
    if (empty($city)) {
        $city = trim($parts[count($parts) - 3] ?? ($parts[count($parts) - 2] ?? ''));
    }
    if (empty($pincode)) {
        preg_match('/\b\d{6}\b/', $full_address, $matches);
        $pincode = $matches[0] ?? '';
    }
} 
// Priority 2: Use the user's saved address from their profile.
else if (isset($user['address']) && !empty($user['address'])) {
    $saved_address = $user['address'];
    $decoded_address = json_decode($saved_address, true);

    // Check if the saved address is structured JSON
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded_address)) {
        $address_line1 = $decoded_address['line1'] ?? '';
        $address_line2 = $decoded_address['line2'] ?? '';
        $city = $decoded_address['city'] ?? '';
        $state = $decoded_address['state'] ?? '';
        $pincode = $decoded_address['pincode'] ?? '';
    } else {
        // Fallback for old, non-JSON addresses. Only line 1 is reliable.
        $address_line1 = $saved_address;
    }
}

// Fetch cart items to calculate total
$stmt_cart = $conn->prepare("
    SELECT c.quantity, p.price
    FROM cart c 
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?");
$stmt_cart->bind_param("i", $user_id);
$stmt_cart->execute();
$result = $stmt_cart->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);

if (empty($cart_items)) {
    header("Location: product.php"); // Redirect if cart is empty
    exit();
}

// Calculate totals (same logic as cart.php)
$subtotal = 0;
$item_count = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $item_count += $item['quantity'];
}

$applied_coupon = $_SESSION['applied_coupon'] ?? null;
$discount = $applied_coupon ? ($subtotal * ($applied_coupon['discount_percentage'] / 100)) : 0;
$order_value = $subtotal - $discount;
$shipping = calculate_shipping_charge($order_value);
$tax = ($subtotal - $discount) * $tax_rate;
$total = $subtotal - $discount + $shipping + $tax;
$total_in_paise = round($total * 100); // Razorpay requires amount in paise

// --- Razorpay Integration ---
// Using keys from config.php is recommended. We check if they are defined and not placeholders.
$keyId = (defined('RAZORPAY_KEY_ID') && RAZORPAY_KEY_ID !== 'rzp_test_xxxxxxxx') ? RAZORPAY_KEY_ID : 'YOUR_KEY_ID';
$keySecret = (defined('RAZORPAY_KEY_SECRET') && RAZORPAY_KEY_SECRET !== 'xxxxxxxxxxxxxxx') ? RAZORPAY_KEY_SECRET : 'YOUR_KEY_SECRET';

// Check for placeholder keys to prevent authentication errors
if ($keyId === 'YOUR_KEY_ID' || $keySecret === 'YOUR_KEY_SECRET') {
    $api_error_message = '<h2>Payment Gateway Not Configured</h2>';
    $api_error_message .= '<p>The Razorpay API keys are missing. Please add your actual keys to the <code>config.php</code> file.</p>';
    $api_error_message .= '<p><a href="cart.php">Return to Cart</a></p>';
    die($api_error_message);
}

$api = new Api($keyId, $keySecret);

// Create a Razorpay Order
$orderData = [
    'receipt'         => 'rcptid_' . uniqid(),
    'amount'          => $total_in_paise,
    'currency'        => 'INR',
    'payment_capture' => 1 // Auto capture payment
];

try {
    $razorpayOrder = $api->order->create($orderData);
    $razorpayOrderId = $razorpayOrder['id'];

    $_SESSION['razorpay_order_id'] = $razorpayOrderId;
    $_SESSION['total_amount'] = $total; // Store final amount for verification
} catch (\Razorpay\Api\Errors\Error $e) {
    // If the API call fails, display a friendly error message and stop the page.
    // In a production environment, you would log this error and show a more generic message.
    $api_error_message = '<h2>Oops! Something went wrong with the payment gateway.</h2>';
    $api_error_message .= '<p>We are unable to create a payment order at this time. Please try again later.</p>';
    $api_error_message .= '<p><strong>Error Details:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    $api_error_message .= '<p><a href="cart.php">Return to Cart</a></p>';
    die($api_error_message);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Checkout - DUNZO</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root { 
        --primary: #6c63ff; 
        --secondary: #ff6584; 
        --light: #f8f9fa; 
        --dark: #212529; 
        --success: #28a745; 
        --gray-600: #6c757d; 
        --border-radius: 12px; 
        --box-shadow: 0 10px 25px rgba(0,0,0,0.05); 
        --transition: all 0.3s ease;
    }
    body { 
        background-color: #f9f9ff; 
        font-family: 'Poppins', sans-serif; 
    }
    .navbar { 
        background-color: #ffffff; 
        padding: 15px 0; 
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); 
    }
    .logo { 
        font-size: 32px; 
        font-weight: 700; 
    }
    .logo .yellow { color: #febd69; }
    .logo .green { color: #00a651; }
    .checkout-container { 
        max-width: 900px; /* Increased width for better spacing */
        margin: 40px auto; 
        padding: 0 15px; 
    }
    .checkout-header { 
        text-align: center; 
        margin-bottom: 30px; 
    }
    .checkout-header h1 { 
        font-weight: 700; 
        color: var(--primary); 
    }
    .checkout-header p {
        color: var(--gray-600);
        font-size: 1.1rem;
    }
    .checkout-card {
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        border: none;
        overflow: hidden; /* To keep border-radius on children */
    }
    .summary-panel {
        background-color: #f8f9fa !important; /* Use Bootstrap's light color */
    }
    .input-group-text {
        width: 42px;
        justify-content: center;
        background-color: #f1f3f5;
        border: 1px solid #ced4da;
        border-right: none;
        color: var(--primary);
    }
    .form-control {
        border-left: none;
        padding-left: 0.5rem;
    }
    .form-control:focus {
        box-shadow: none;
        border-color: #ced4da;
    }
    .form-control[readonly] {
        background-color: #e9ecef;
    }
    .summary-details .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        font-size: 0.95rem;
    }
    .summary-details .summary-row span:first-child,
    .summary-details .summary-row span i {
        color: var(--gray-600);
    }
    .summary-details .summary-row span:last-child {
        font-weight: 500;
        color: var(--dark);
    }
    .summary-row.savings span {
        color: var(--success) !important;
    }
    .summary-row.total {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--dark);
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #dee2e6;
    }
    .checkout-btn { 
        background: linear-gradient(90deg, var(--primary), var(--secondary)); 
        color: white; 
        border: none; 
        border-radius: 50px; 
        padding: 15px; 
        font-weight: 600; 
        font-size: 1.1rem; 
        width: 100%; 
        transition: var(--transition);
        box-shadow: 0 4px 15px rgba(108, 99, 255, 0.3);
    }
    .checkout-btn:hover { 
        transform: translateY(-3px); 
        box-shadow: 0 8px 20px rgba(108, 99, 255, 0.4); 
    }
    @media (min-width: 992px) {
        .summary-panel {
            border-left: 1px solid #dee2e6;
        }
    }
    @media (max-width: 991.98px) {
        .summary-panel {
            border-top: 1px solid #dee2e6;
        }
    }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
  <div class="container">
   <div class="logo">
        <span class="yellow">dun</span><span class="green">zo</span>
    </div>
    <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="cart.php"><i class="fas fa-arrow-left me-1"></i> Back to Cart</a>
        </li>
    </ul>
  </div>
</nav>

<div class="checkout-container">
    <div class="checkout-header">
        <h1><i class="fas fa-shield-alt me-2"></i>Secure Checkout</h1>
        <p>Complete your purchase with confidence</p>
    </div>

    <div class="card checkout-card">
        <div class="card-body p-0">
            <div class="row g-0">
                <div class="col-lg-7 p-4 p-md-5">
                    <h5 class="mb-4">Shipping Information</h5>
                    <form id="checkout-form" action="verify.php" method="POST">
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" placeholder="Full Name" value="<?= htmlspecialchars($user['full_name']) ?>" readonly>
                        </div>
                        <div class="input-group mb-4">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" placeholder="Email Address" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                        </div>
                        <!-- Structured Address Fields -->
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="address_line1" class="form-label">Address Line 1</label>
                                <input type="text" class="form-control" id="address_line1" value="<?= htmlspecialchars($address_line1) ?>" required>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="address_line2" class="form-label">Address Line 2 <span class="text-muted">(Optional)</span></label>
                                <input type="text" class="form-control" id="address_line2" value="<?= htmlspecialchars($address_line2) ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city"  value="<?= htmlspecialchars($city) ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control" id="state"  value="<?= htmlspecialchars($state) ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="pincode" class="form-label">Pincode</label>
                                <input type="text" class="form-control" id="pincode"  value="<?= htmlspecialchars($pincode) ?>" required>
                            </div>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="save_address" id="save_address" checked>
                            <label class="form-check-label" for="save_address">
                                Save this address to my profile for future use
                            </label>
                        </div>

                        <!-- Hidden fields for Razorpay response -->
                        <input type="hidden" id="delivery_address" name="delivery_address">
                        <input type="hidden" id="structured_address" name="structured_address">
                        <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
                        <input type="hidden" name="razorpay_signature" id="razorpay_signature">
                        <input type="hidden" name="razorpay_order_id" value="<?= $razorpayOrderId ?>">
                        <input type="hidden" name="is_prime_member" value="<?= $is_prime_member ? '1' : '0' ?>">
                    </form>
                </div>
                <div class="col-lg-5 p-4 p-md-5 summary-panel">
                    <h5 class="mb-4">Order Summary</h5>
                    <?php if($is_prime_member): ?>
                    <div class="summary-row">
                        <span><i class="fas fa-shipping-fast text-primary me-2"></i>Delivery</span>
                        <span class="fw-bold text-primary">Priority Delivery</span>
                    </div>
                    <?php endif; ?>
                    <div class="summary-details">
                        <div class="summary-row">
                            <span><i class="fas fa-shopping-basket text-muted me-2"></i>Subtotal (<?= $item_count ?> items)</span>
                            <span>₹<?= number_format($subtotal, 2) ?></span>
                        </div>
                        
                        <?php if($discount > 0): ?>
                        <div class="summary-row savings">
                            <span><i class="fas fa-tag me-2"></i>Discount (<?= htmlspecialchars($applied_coupon['code']) ?>)</span>
                            <span>-₹<?= number_format($discount, 2) ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="summary-row">
                            <span><i class="fas fa-truck text-muted me-2"></i>Shipping</span>
                            <span id="summary-shipping"><?= $shipping == 0 ? 'FREE' : '₹' . number_format($shipping, 2) ?></span>

                        </div>
                        
                        <div class="summary-row">
                            <span><i class="fas fa-receipt text-muted me-2"></i>Tax</span>
                            <span>₹<?= number_format($tax, 2) ?></span>
                        </div>
                        
                        <hr class="my-4">

                        <div class="summary-row total">
                            <strong>Total</strong>
                            <strong>₹<?= number_format($total, 2) ?></strong>
                        </div>
                    </div>

                    <button id="razorpay-btn" type="button" class="checkout-btn mt-4 w-100">
                        <i class="fas fa-credit-card me-2"></i> Pay Securely ₹<?= number_format($total, 2) ?>
                    </button>
                    <div class="text-center mt-3">
                        <small class="text-muted"><i class="fas fa-lock"></i> 100% Secure Payments powered by Razorpay</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
// This script handles the Razorpay payment process.
// It is triggered when the user clicks the 'Pay Securely' button.
document.getElementById('razorpay-btn').onclick = function(e) {
    e.preventDefault(); // Prevent the default button action.

    // --- Step 1: Validate and collect the delivery address from the form ---
    const address_line1 = document.getElementById('address_line1').value.trim();
    const address_line2 = document.getElementById('address_line2').value.trim();
    const city = document.getElementById('city').value.trim();
    const state = document.getElementById('state').value.trim();
    const pincode = document.getElementById('pincode').value.trim();

    // Simple validation for required fields
    if (address_line1 === '') {
        alert('Please enter Address Line 1.');
        document.getElementById('address_line1').focus();
        return; // Stop if validation fails.
    }
    if (city === '') {
        alert('Please enter your City.');
        document.getElementById('city').focus();
        return;
    }
    if (state === '') {
        alert('Please enter your State.');
        document.getElementById('state').focus();
        return;
    }
    if (pincode === '' || !/^\d{6}$/.test(pincode)) {
        alert('Please enter a valid 6-digit Pincode.');
        document.getElementById('pincode').focus();
        return;
    }

    // --- Step 2: Prepare the address data for the server ---
    // Combine address parts into a single string for display on invoices.
    let fullAddressString = [address_line1, address_line2, city, state].filter(Boolean).join(', ');
    fullAddressString += ' - ' + pincode;
    document.getElementById('delivery_address').value = fullAddressString;

    // Create a structured JSON object to save the address in the user's profile.
    // This is a more reliable way to store addresses than a single string.
    const addressObject = {
        line1: address_line1,
        line2: address_line2,
        city: city,
        state: state,
        pincode: pincode
    };
    // Set the value of the hidden input for the structured address.
    document.getElementById('structured_address').value = JSON.stringify(addressObject);

    // --- Step 3: Configure and open the Razorpay payment pop-up ---
    var options = {
        "key": "<?= $keyId ?>", // Your Razorpay Key ID (from PHP).
        "amount": "<?= $total_in_paise ?>", // Amount in the smallest currency unit (paise).
        "currency": "INR",
        "name": "DUNZO", // Your business name.
        "description": "Order Payment",
        "image": "https://dunzo.com/images/dunzo-logo-full.svg", // Your logo.
        "order_id": "<?= $razorpayOrderId ?>", // The order_id created by PHP.

        // --- Step 4: Handle the payment response ---
        "handler": function (response){
            // This function is called when the payment is successful.
            // It receives the payment_id and signature from Razorpay.

            // Populate the hidden form fields with the response data.
            document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
            document.getElementById('razorpay_signature').value = response.razorpay_signature;

            // Submit the form to 'verify.php' for server-side verification.
            document.getElementById('checkout-form').submit();
        },
        "prefill": {
            // Pre-fill user details in the Razorpay pop-up.
            "name": "<?= htmlspecialchars($user['full_name']) ?>",
            "email": "<?= htmlspecialchars($user['email']) ?>",
            "contact": "<?= htmlspecialchars($user['mobile'] ?? '') ?>"
        },
        "notes": {
            "address": "Note: This is a test transaction"
        },
        "theme": {
            "color": "#6c63ff"
        },
        // --- Step 5: Handle payment failure and modal closing ---
        "modal": {
            "ondismiss": function(){
                // This function is called when the user closes the pop-up without paying.
                console.log("Checkout form closed by user.");
            }
        },
        "events": {
            "payment.failed": function (response){
                // This function is called when the payment fails.
                // We redirect to a failure page with details about the error.
                let errorUrl = 'payment_failed.php?reason=' + response.error.reason;
                errorUrl += '&description=' + encodeURIComponent(response.error.description);
                if (response.error.metadata && response.error.metadata.order_id) {
                    errorUrl += '&order_id=' + response.error.metadata.order_id;
                }
                if (response.error.metadata && response.error.metadata.payment_id) {
                    errorUrl += '&payment_id=' + response.error.metadata.payment_id;
                }
                window.location.href = errorUrl;
            }
        }
    };
    var rzp1 = new Razorpay(options);
    // Open the Razorpay payment pop-up.
    rzp1.open();
};
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>