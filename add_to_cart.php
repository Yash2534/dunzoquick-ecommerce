<?php
session_start();
include 'config.php';

// We are sending JSON response
header('Content-Type: application/json');

// ---------------------------
// 1. Check if user is logged in
// ---------------------------
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please log in to add items to your cart.'
    ]);
    exit();
}

$user_id = $_SESSION['user_id'];

// ---------------------------
// 2. Check if form is submitted
// ---------------------------
if (!isset($_POST['add_to_cart'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request.'
    ]);
    exit();
}

// ---------------------------
// 3. Get product and quantity
// ---------------------------
$product_id = $_POST['product_id'];
$quantity   = $_POST['quantity'];

// Basic validation
if (!is_numeric($product_id) || !is_numeric($quantity) || $quantity < 1) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid product or quantity.'
    ]);
    exit();
}

// ---------------------------
// 4. Check if product exists
// ---------------------------
$product_sql = "SELECT id FROM products WHERE id = $product_id";
$product_result = $conn->query($product_sql);

if ($product_result->num_rows == 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Product not found.'
    ]);
    exit();
}

// ---------------------------
// 5. Check if product already in cart
// ---------------------------
$check_sql = "SELECT quantity FROM cart 
              WHERE user_id = $user_id AND product_id = $product_id";
$check_result = $conn->query($check_sql);

if ($check_result->num_rows > 0) {
    // Already in cart → update quantity
    $row = $check_result->fetch_assoc();
    $new_quantity = $row['quantity'] + $quantity;

    $update_sql = "UPDATE cart 
                   SET quantity = $new_quantity 
                   WHERE user_id = $user_id AND product_id = $product_id";
    $conn->query($update_sql);

} else {
    // Not in cart → insert new row
    $insert_sql = "INSERT INTO cart (user_id, product_id, quantity)
                   VALUES ($user_id, $product_id, $quantity)";
    $conn->query($insert_sql);
}

// ---------------------------
// 6. Get updated cart count
// ---------------------------
$count_sql = "SELECT SUM(quantity) AS total_items 
              FROM cart WHERE user_id = $user_id";
$count_result = $conn->query($count_sql);
$count_row = $count_result->fetch_assoc();

$cart_count = $count_row['total_items'];

// ---------------------------
// 7. Final Response
// ---------------------------
echo json_encode([
    'success' => true,
    'message' => 'Item added to cart!',
    'cart_count' => $cart_count
]);
exit();

?>
