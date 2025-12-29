<?php
session_start();
include 'config.php';

// Require Razorpay SDK
$autoloader = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoloader)) {
    die("<h2>Composer Autoloader Not Found</h2><p>Please run <code>composer install</code> to generate it.</p>");
}
require $autoloader;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

// 1. Basic Validations
// =================================================
if (!isset($_SESSION['user_id'])) {
    // Redirect to login if user is not logged in
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['razorpay_payment_id'])) {
    // Redirect to cart if the page is accessed directly or without payment ID
    header("Location: cart.php?error=payment_failed");
    exit();
}

// 2. Razorpay Signature Verification
// =================================================
// Use keys from config.php and check that they are not placeholders.
$keyId = (defined('RAZORPAY_KEY_ID') && RAZORPAY_KEY_ID !== 'rzp_test_xxxxxxxx') ? RAZORPAY_KEY_ID : 'YOUR_KEY_ID';
$keySecret = (defined('RAZORPAY_KEY_SECRET') && RAZORPAY_KEY_SECRET !== 'xxxxxxxxxxxxxxx') ? RAZORPAY_KEY_SECRET : 'YOUR_KEY_SECRET';

// Check for placeholder keys to prevent signature verification errors
if ($keyId === 'YOUR_KEY_ID' || $keySecret === 'YOUR_KEY_SECRET') {
    error_log("Razorpay keys are not configured in config.php. Signature verification cannot proceed.");
    header("Location: payment_failed.php?reason=config_error");
    exit();
}

$api = new Api($keyId, $keySecret);

$razorpay_order_id = $_SESSION['razorpay_order_id'];
$razorpay_payment_id = $_POST['razorpay_payment_id'];
$razorpay_signature = $_POST['razorpay_signature'];

$attributes = [
    'razorpay_order_id' => $razorpay_order_id,
    'razorpay_payment_id' => $razorpay_payment_id,
    'razorpay_signature' => $razorpay_signature
];

try {
    $api->utility->verifyPaymentSignature($attributes);
    // If we reach here, the signature is valid.
} catch(SignatureVerificationError $e) {
    // Signature is not valid.
    // Log the error and redirect to a failure page.
    error_log("Razorpay Signature Verification Failed: " . $e->getMessage());
    header("Location: payment_failed.php?reason=signature_mismatch");
    exit();
}

// 3. Process the Order (Signature is VERIFIED)
// =================================================
$user_id = $_SESSION['user_id'];
$delivery_address = $_POST['delivery_address'];
$is_prime_member = isset($_POST['is_prime_member']) && $_POST['is_prime_member'] === '1';

// Start a transaction
$conn->begin_transaction();

try {
    // 3.1. Fetch cart items and calculate totals (SERVER-SIDE)
    // This is crucial to prevent price manipulation from the client-side.
    $stmt_cart = $conn->prepare("
        SELECT c.product_id, c.quantity, p.name, p.price, p.image
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
    ");
    $stmt_cart->bind_param("i", $user_id);
    $stmt_cart->execute();
    $cart_items_result = $stmt_cart->get_result();
    $cart_items = $cart_items_result->fetch_all(MYSQLI_ASSOC);
    $stmt_cart->close();

    if (empty($cart_items)) {
        throw new Exception("Cart is empty. Cannot process order.");
    }

    // Recalculate totals
    $subtotal = 0;
    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }

    $applied_coupon = $_SESSION['applied_coupon'] ?? null;
    $discount_amount = 0;
    $coupon_code = null;
    if ($applied_coupon && $subtotal >= ($applied_coupon['min_spend'] ?? 0)) {
        $discount_amount = $subtotal * ($applied_coupon['discount_percentage'] / 100);
        $coupon_code = $applied_coupon['code'];
    }
    
    $order_value = $subtotal - $discount_amount;
    $shipping_amount = calculate_shipping_charge($order_value, $is_prime_member);
    $tax_amount = ($subtotal - $discount_amount) * $tax_rate; // Use $tax_rate from config.php
    $total_amount = $subtotal - $discount_amount + $shipping_amount + $tax_amount;

    // 3.2. Insert into `orders` table
    $order_number = 'DUNZO-' . time() . $user_id;
    $payment_method = 'Razorpay';
    $delivery_priority = 'standard';

    $stmt_order = $conn->prepare("
        INSERT INTO orders (user_id, order_number, subtotal, discount_amount, coupon_code, shipping_amount, tax_amount, total_amount, delivery_address, payment_id, razorpay_order_id, payment_method, delivery_priority)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt_order->bind_param(
        "isddssddsssss",
        $user_id, $order_number, $subtotal, $discount_amount, $coupon_code,
        $shipping_amount, $tax_amount, $total_amount, $delivery_address,
        $razorpay_payment_id, $razorpay_order_id, $payment_method, $delivery_priority
    );
    $stmt_order->execute();
    $order_id = $conn->insert_id; // Get the ID of the new order
    $stmt_order->close();

    if (!$order_id) {
        throw new Exception("Failed to create order record.");
    }

    // 3.3. Insert into `order_items` table
    $stmt_items = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price, image) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($cart_items as $item) {
        $stmt_items->bind_param("iisids", $order_id, $item['product_id'], $item['name'], $item['quantity'], $item['price'], $item['image']);
        $stmt_items->execute();
    }
    $stmt_items->close();

    // 3.4. Clear the user's cart
    $stmt_clear_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt_clear_cart->bind_param("i", $user_id);
    $stmt_clear_cart->execute();
    $stmt_clear_cart->close();

    // 3.5. Clean up session variables
    unset($_SESSION['razorpay_order_id'], $_SESSION['total_amount'], $_SESSION['applied_coupon']);

    // If all went well, commit the transaction
    $conn->commit();

    // 4. Redirect to a success page (invoice)
    header("Location: order_confirm.php?order_id=" . $order_id);
    exit();

} catch (Exception $e) {
    // Something went wrong, rollback the transaction
    $conn->rollback();
    error_log("Order processing failed for user_id {$user_id}: " . $e->getMessage());
    header("Location: payment_failed.php?reason=server_error");
    exit();
}