<?php
require_once('../../config/db.php');
require_once('../../config/auth.php');
require_admin();

// Check if required parameters are provided
if (!isset($_GET['id']) || !isset($_GET['status'])) {
    header('Location: orders.php?error=Missing required parameters');
    exit;
}

$order_id = (int)$_GET['id'];
$new_status = $_GET['status'];

// Validate status
$allowed_statuses = ['pending', 'processing', 'completed', 'cancelled'];
if (!in_array($new_status, $allowed_statuses)) {
    header('Location: orders.php?error=Invalid status');
    exit;
}

// Check if order exists
$order = $conn->query("SELECT * FROM orders WHERE id = $order_id")->fetch_assoc();
if (!$order) {
    header('Location: orders.php?error=Order not found');
    exit;
}

// Prevent updating completed or cancelled orders
if (in_array($order['status'], ['completed', 'cancelled']) && $order['status'] !== $new_status) {
    header('Location: orders.php?error=Cannot modify a ' . $order['status'] . ' order');
    exit;
}

// Update order status
$stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
$stmt->bind_param("si", $new_status, $order_id);

if ($stmt->execute()) {
    // If order is being marked as completed, update inventory if needed
    if ($new_status === 'completed') {
        // Add any post-completion logic here (e.g., update inventory, send notifications)
    }
    
    // Redirect back with success message
    header('Location: orders.php?success=Order status updated successfully');
} else {
    header('Location: orders.php?error=Failed to update order status: ' . $conn->error);
}

$stmt->close();
$conn->close();