<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

// Basic validation
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit;
}
if (!isset($_GET['order_id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Order ID is missing.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = (int)$_GET['order_id'];

// Fetch order status, ensuring it belongs to the logged-in user
$stmt = $conn->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($order = $result->fetch_assoc()) {
    echo json_encode(['success' => true, 'status' => $order['status']]);
} else {
    http_response_code(404); // Not Found
    echo json_encode(['success' => false, 'message' => 'Order not found.']);
}

$stmt->close();