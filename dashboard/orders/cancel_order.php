<?php
require_once('../../config/db.php');
require_once('../../config/auth.php');
require_admin();

// Check if order ID is provided
if (!isset($_GET['id'])) {
    header('Location: orders.php?error=Order ID is required');
    exit;
}

$order_id = (int)$_GET['id'];

// Get order details
$order = $conn->query("SELECT * FROM orders WHERE id = $order_id")->fetch_assoc();
if (!$order) {
    header('Location: orders.php?error=Order not found');
    exit;
}

// Prevent cancelling already completed or cancelled orders
if ($order['status'] === 'completed') {
    header('Location: orders.php?error=Cannot cancel a completed order');
    exit;
}

if ($order['status'] === 'cancelled') {
    header('Location: orders.php?error=Order is already cancelled');
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Update order status to cancelled
    $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    
    // Add cancellation note if provided
    $cancel_reason = $_POST['reason'] ?? 'No reason provided';
    $stmt = $conn->prepare("INSERT INTO order_notes (order_id, note, created_by) VALUES (?, ?, ?)");
    $admin_id = $_SESSION['user_id'] ?? 0;
    $note = "Order cancelled. Reason: " . $cancel_reason;
    $stmt->bind_param("isi", $order_id, $note, $admin_id);
    $stmt->execute();
    
    // If you want to implement inventory restocking:
    // $items = $conn->query("SELECT * FROM order_items WHERE order_id = $order_id");
    // while ($item = $items->fetch_assoc()) {
    //     $conn->query("UPDATE menu_items SET stock = stock + {$item['quantity']} WHERE id = {$item['menu_item_id']}");
    // }
    
    $conn->commit();
    header('Location: orders.php?success=Order has been cancelled');
    
} catch (Exception $e) {
    $conn->rollback();
    header('Location: orders.php?error=Failed to cancel order: ' . $e->getMessage());
}

$conn->close();