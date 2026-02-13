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

// Check if item is already in wishlist
$stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Remove from wishlist
    $delete_stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $delete_stmt->bind_param("ii", $user_id, $product_id);
    if ($delete_stmt->execute()) {
        echo json_encode(['status' => 'success', 'action' => 'removed', 'message' => 'Removed from wishlist']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to remove from wishlist']);
    }
    $delete_stmt->close();
} else {
    // Add to wishlist
    $insert_stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $insert_stmt->bind_param("ii", $user_id, $product_id);
    if ($insert_stmt->execute()) {
        echo json_encode(['status' => 'success', 'action' => 'added', 'message' => 'Added to wishlist']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add to wishlist']);
    }
    $insert_stmt->close();
}
$stmt->close();
$conn->close();