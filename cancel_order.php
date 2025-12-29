<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

// 1. Basic Security Checks
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'message' => 'You must be logged in to cancel an order.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['order_id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Invalid order ID.']);
    exit();
}

$order_id = (int)$data['order_id'];
$user_id = $_SESSION['user_id'];

// Fetch the order to verify ownership and status
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404); // Not Found
    echo json_encode(['success' => false, 'message' => 'Order not found or you do not have permission.']);
    exit();
}

$order = $result->fetch_assoc();
$time_since_order = time() - strtotime($order['created_at']);
$cancellation_window = 60; // 60 seconds

if ($time_since_order > $cancellation_window || $order['status'] !== 'pending') {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'The cancellation window has expired or the order cannot be cancelled.']);
    exit();
}

// Proceed with cancellation
$update_stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
$update_stmt->bind_param("i", $order_id);

if ($update_stmt->execute()) {
    // Here you would also trigger a refund process via your payment gateway API
    echo json_encode(['success' => true, 'message' => 'Order has been successfully cancelled.']);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Failed to update order status.']);
}

$stmt->close();
$update_stmt->close();
$conn->close();