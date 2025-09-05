<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get and validate input
$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';

// Validate status
$valid_statuses = ['pending', 'completed', 'cancelled'];
if (!in_array($status, $valid_statuses)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

// Update order status
$stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $order_id);

if ($stmt->execute()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to update status']);
}

$stmt->close();
$conn->close(); 