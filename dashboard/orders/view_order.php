<?php
require_once('../../config/db.php');
require_once('../../config/auth.php');
require_admin();

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: orders.php');
    exit();
}

$order_id = (int)$_GET['id'];

// Get order details
$order_query = "SELECT o.*, u.username as customer_name, u.email as customer_email, 
                u.phone as customer_phone
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                WHERE o.id = ?";
$stmt = $conn->prepare($order_query);
$stmt->bind_param('i', $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: orders.php?error=Order not found');
    exit();
}

// Get order items
$items_query = "SELECT oi.*, mi.name as item_name, mi.price as item_price, mi.image as item_image, 
               mi.category as item_category
               FROM order_items oi
               LEFT JOIN menu_items mi ON oi.menu_item_id = mi.id
               WHERE oi.order_id = ?";
$stmt = $conn->prepare($items_query);
$stmt->bind_param('i', $order_id);
$stmt->execute();
$items = $stmt->get_result();

// Get status class for styling
$statusClass = [
    'pending' => 'bg-warning',
    'processing' => 'bg-info',
    'completed' => 'bg-success',
    'cancelled' => 'bg-danger'
][$order['status']] ?? 'bg-secondary';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?= $order['id'] ?> - Somaal Bistro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { 
            --orange: #d35400; 
            --dark-blue: #1a2035; 
        }
        body { 
            background: #f8faff; 
            font-family: 'Public Sans', sans-serif; 
            margin: 0; 
        }
        .main-content { 
            margin-left: 260px; 
            width: calc(100% - 260px); 
            min-height: 100vh; 
            padding: 20px;
        }
        .order-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .order-header {
            background: var(--dark-blue);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .order-body {
            padding: 25px;
        }
        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            text-transform: capitalize;
        }
        .item-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        .btn-print {
            background: var(--orange);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
        }
        .btn-print:hover {
            background: #b34700;
            color: white;
        }
        .customer-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .order-summary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
        }
        .back-btn {
            color: var(--orange);
            text-decoration: none;
            font-weight: 600;
        }
        .back-btn:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include('../sidebar.php'); ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="orders.php" class="back-btn">
                    <i class="fas fa-arrow-left me-2"></i>Back to Orders
                </a>
                <h4 class="mb-0 ms-3 d-inline">Order #<?= $order['id'] ?></h4>
            </div>
            <div>
                <button onclick="window.print()" class="btn btn-print">
                    <i class="fas fa-print me-2"></i>Print
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="order-card mb-4">
                    <div class="order-header">
                        <div>
                            <h5 class="mb-0">Order Details</h5>
                            <p class="mb-0 text-white-50">Placed on <?= date('F d, Y \a\t h:i A', strtotime($order['created_at'])) ?></p>
                        </div>
                        <span class="status-badge <?= $statusClass ?>">
                            <?= ucfirst($order['status']) ?>
                        </span>
                    </div>
                    <div class="order-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Price</th>
                                        <th>Qty</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $subtotal = 0;
                                    while ($item = $items->fetch_assoc()): 
                                        $item_total = $item['price'] * $item['quantity'];
                                        $subtotal += $item_total;
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($item['item_image'])): ?>
                                                    <img src="../../assets/images/menu/<?= htmlspecialchars($item['item_image']) ?>" 
                                                         class="item-image me-3" 
                                                         alt="<?= htmlspecialchars($item['item_name']) ?>">
                                                <?php else: ?>
                                                    <div class="item-image me-3 bg-light d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-utensils text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <h6 class="mb-0"><?= htmlspecialchars($item['item_name']) ?></h6>
                                                    <small class="text-muted"><?= ucfirst($item['item_category']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>$<?= number_format($item['price'], 2) ?></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td class="text-end">$<?= number_format($item_total, 2) ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="order-card">
                    <div class="order-header">
                        <h5 class="mb-0">Customer Information</h5>
                    </div>
                    <div class="order-body">
                        <div class="customer-info">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <h6>Contact Information</h6>
                                    <p class="mb-1"><?= htmlspecialchars($order['customer_name']) ?></p>
                                    <?php if (!empty($order['customer_email'])): ?>
                                        <p class="mb-1"><?= htmlspecialchars($order['customer_email']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($order['customer_phone'])): ?>
                                        <p class="mb-0"><?= htmlspecialchars($order['customer_phone']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
<h6>Delivery Address</h6>
                                    <p class="mb-0">
                                        <?php 
                                        $delivery_address = !empty($order['delivery_address']) ? $order['delivery_address'] : 
                                            (!empty($order['customer_address']) ? $order['customer_address'] : 'Not specified');
                                        echo nl2br(htmlspecialchars($delivery_address)); 
                                        ?>
                                    </p>
                                    <?php if (!empty($order['special_instructions'])): ?>
                                        <div class="mt-3">
                                            <h6>Special Instructions</h6>
                                            <p class="mb-0"><?= nl2br(htmlspecialchars($order['special_instructions'])) ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="order-card">
                    <div class="order-header">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="order-body">
                        <div class="order-summary">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <span>$<?= number_format($subtotal, 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Delivery Fee</span>
                                <span>$<?= number_format($order['delivery_fee'] ?? 0, 2) ?></span>
                            </div>
                            <?php if (!empty($order['discount_amount']) && $order['discount_amount'] > 0): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Discount</span>
                                <span class="text-success">-$<?= number_format($order['discount_amount'], 2) ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Tax</span>
                                <span>$<?= number_format($order['tax_amount'] ?? 0, 2) ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-0">
                                <h5 class="mb-0">Total</h5>
                                <h5 class="mb-0">$<?= number_format($order['total_price'], 2) ?></h5>
                            </div>
                        </div>

                        <?php if ($order['status'] !== 'completed' && $order['status'] !== 'cancelled'): ?>
                        <div class="mt-4 d-grid gap-2">
                            <a href="update_status.php?id=<?= $order['id'] ?>&status=processing" 
                               class="btn btn-primary">
                                <i class="fas fa-spinner me-2"></i>Mark as Processing
                            </a>
                            <a href="update_status.php?id=<?= $order['id'] ?>&status=completed" 
                               class="btn btn-success"
                               onclick="return confirm('Mark this order as completed?')">
                                <i class="fas fa-check-circle me-2"></i>Mark as Completed
                            </a>
                            <a href="update_status.php?id=<?= $order['id'] ?>&status=cancelled" 
                               class="btn btn-outline-danger"
                               onclick="return confirm('Are you sure you want to cancel this order?')">
                                <i class="fas fa-times-circle me-2"></i>Cancel Order
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($order['payment_method'])): ?>
                <div class="order-card mt-4">
                    <div class="order-header">
                        <h5 class="mb-0">Payment Information</h5>
                    </div>
                    <div class="order-body">
                        <p class="mb-2">
                            <strong>Payment Method:</strong> 
                            <span class="text-capitalize"><?= $order['payment_method'] ?></span>
                        </p>
                        <?php if (!empty($order['transaction_id'])): ?>
                            <p class="mb-0">
                                <strong>Transaction ID:</strong> 
                                <span><?= htmlspecialchars($order['transaction_id']) ?></span>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
