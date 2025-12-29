<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to manage your wishlist.']);
    exit();
}

// Check if product_id is sent
if (!isset($_POST['product_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Product ID not provided.']);
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$product_id = (int)$_POST['product_id'];

// Check if the item is already in the wishlist
$stmt_check = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
$stmt_check->bind_param("ii", $user_id, $product_id);
$stmt_check->execute();
$is_in_wishlist = $stmt_check->get_result()->num_rows > 0;
$stmt_check->close();

try {
    if ($is_in_wishlist) {
        // Remove from wishlist
        $stmt_delete = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt_delete->bind_param("ii", $user_id, $product_id);
        $stmt_delete->execute();
        $stmt_delete->close();
        echo json_encode(['status' => 'success', 'action' => 'removed']);
    } else {
        // Add to wishlist
        $stmt_insert = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt_insert->bind_param("ii", $user_id, $product_id);
        $stmt_insert->execute();
        $stmt_insert->close();
        echo json_encode(['status' => 'success', 'action' => 'added']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'A database error occurred.']);
}

$conn->close();