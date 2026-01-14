<?php
session_start();
require_once('config/db.php');

// 1. Ma hadda baa la dalbaday? (Check if coming from checkout)
$just_ordered = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_SESSION['cart'])) {
    $user_id = $_SESSION['user_id'] ?? 0;
    $name = $conn->real_escape_string($_POST['customer_name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    
    $subtotal = 0;
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['qty'];
    }
    $total = $subtotal + 2.00 + ($subtotal * 0.10);

    $sql = "INSERT INTO orders (user_id, total_price, status) VALUES ('$user_id', '$total', 'pending')";
    
    if ($conn->query($sql)) {
        $order_id = $conn->insert_id;
        foreach ($_SESSION['cart'] as $menu_id => $item) {
            $price = $item['price'];
            $qty = $item['qty'];
            $conn->query("INSERT INTO order_items (order_id, menu_item_id, quantity, price) 
                          VALUES ('$order_id', '$menu_id', '$qty', '$price')");
        }
        unset($_SESSION['cart']);
        $just_ordered = true; // Halkan fariinta ayaa ka dhalanaysa
    }
}

// 2. Soo saar History-ga qofka
$user_id = $_SESSION['user_id'] ?? 0;
$history_res = $conn->query("SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | Pizza Place</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --orange: #d35400;
            --dark-bg: #212529;
            --light-bg: #f8f9fa;
            --border-radius: 12px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #fcfcfc;
            color: #333;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Navbar */
        .navbar {
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }
        
        .navbar-brand {
            color: var(--orange) !important;
            font-weight: 800;
            font-size: 1.8rem;
            letter-spacing: -0.5px;
        }
        
        .nav-link {
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            color: var(--orange) !important;
        }
        
        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, #fff9f5 0%, #fff 100%);
            padding: 3rem 0 2rem;
            margin-bottom: 2.5rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .page-header h1 {
            font-weight: 800;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .page-header h1 span {
            color: var(--orange);
        }
        
        /* Success Message */
        .order-success-banner {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 1.25rem 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 2.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        }
        
        .order-success-banner i {
            font-size: 1.5rem;
            color: #4caf50;
        }

        /* Order Cards Grid */
        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .order-card {
            background: #fff;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .order-card-header {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        
        .order-meta {
            flex: 1;
            min-width: 200px;
        }
        
        .order-date {
            color: #6c757d;
            font-size: 0.85rem;
        }
        
        .order-status {
            display: inline-flex;
            align-items: center;
            margin: 0.5rem 0 0;
            font-size: 1rem;
            font-weight: 600;
        }
        
        .delivery-time {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
        }
        
        .delivery-time i {
            font-size: 1rem;
        }
        
        .order-actions {
            display: flex;
            gap: 0.4rem;
            flex-wrap: wrap;
        }
        
        .order-actions .btn {
            font-size: 0.8rem;
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
        }
        
        .order-items {
            padding: 1rem;
            flex-grow: 1;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            padding: 0.6rem 0;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 50px;
            height: 50px;
            border-radius: 6px;
            object-fit: cover;
            margin-right: 0.75rem;
        }
        
        .item-image-placeholder {
            width: 50px;
            height: 50px;
            border-radius: 6px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #aaa;
            margin-right: 0.75rem;
            font-size: 1.5rem;
        }
        
        .item-details {
            flex: 1;
            min-width: 0;
        }
        
        .item-name {
            font-weight: 600;
            margin-bottom: 0.2rem;
            color: #333;
            font-size: 0.9rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .item-meta {
            display: flex;
            gap: 0.5rem;
            font-size: 0.75rem;
            color: #6c757d;
            margin-bottom: 0.2rem;
            flex-wrap: wrap;
        }
        
        .item-price {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9rem;
        }
        
        .more-items {
            padding: 0.75rem 1.75rem;
            background: #f8f9fa;
            color: #6c757d;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            border-top: 1px solid rgba(0,0,0,0.05);
        }
        
        .more-items i {
            font-size: 0.9rem;
        }
        
        .order-summary {
            padding: 1rem;
            background: #f9f9f9;
            border-top: 1px solid #f0f0f0;
            margin-top: auto;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.4rem;
            font-size: 0.85rem;
        }
        
        .summary-total {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
            padding-top: 0.75rem;
            border-top: 1px dashed #ddd;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .total-amount {
            color: var(--orange);
            font-weight: 700;
        }
        
        .order-footer {
            padding: 1.5rem 1.75rem;
            border-top: 1px solid rgba(0,0,0,0.05);
            background: #fff;
        }
        
        .delivery-info {
            display: flex;
            gap: 2rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        
        .delivery-address,
        .delivery-contact {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-size: 0.9rem;
        }
        
        .delivery-address i,
        .delivery-contact i {
            color: var(--orange);
            margin-top: 0.2rem;
        }
        
        .order-cta {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(0,0,0,0.05);
        }
        
        .order-cta .btn {
            font-size: 0.8rem;
            padding: 0.4rem 0.9rem;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
        }
        
        .status-badge {
            padding: 0.35rem 0.9rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
            letter-spacing: 0.3px;
            display: inline-flex;
            align-items: center;
        }
        
        .status-pending {
            background: #fff8e6;
            color: #e6a700;
        }
        
        .status-completed {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        .status-cancelled {
            background: #ffebee;
            color: #c62828;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: #fff;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #e0e0e0;
            margin-bottom: 1.5rem;
            display: inline-block;
        }
        
        .empty-state h4 {
            color: #555;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        .empty-state p {
            color: #888;
            max-width: 400px;
            margin: 0 auto 1.5rem;
            line-height: 1.6;
        }
        
        /* Footer */
        footer {
            background: var(--dark-bg);
            color: #adb5bd;
            padding: 4rem 0 2rem;
            margin-top: auto;
        }
        
        footer h5 {
            color: var(--orange);
            font-weight: 700;
            margin-bottom: 1.25rem;
            font-size: 1.1rem;
        }
        
        footer p {
            color: #adb5bd;
            font-size: 0.9rem;
            line-height: 1.7;
            margin-bottom: 0.5rem;
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .footer-links li {
            margin-bottom: 0.5rem;
        }
        
        .footer-links a {
            color: #adb5bd;
            text-decoration: none;
            transition: color 0.2s ease;
            font-size: 0.9rem;
        }
        
        .footer-links a:hover {
            color: #fff;
            text-decoration: none;
        }
        
        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
            color: #adb5bd;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background: var(--orange);
            color: #fff;
            transform: translateY(-2px);
        }
        
        .copyright {
            text-align: center;
            padding-top: 2rem;
            margin-top: 3rem;
            border-top: 1px solid rgba(255,255,255,0.05);
            font-size: 0.85rem;
            color: #6c757d;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg bg-white sticky-top py-3 shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">Pizza Place</a>
            <div class="collapse navbar-collapse justify-content-center">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="full_menu.php">Menu</a></li>
                    <li class="nav-item"><a class="nav-link fw-bold text-dark border-bottom border-warning" href="track_orders.php">My Orders</a></li>
                </ul>
            </div>
            <div class="d-flex align-items-center">
                <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1>Your <span>Orders</span></h1>
            <p class="text-muted">Track and manage your delicious orders</p>
        </div>
    </div>

    <div class="container mb-5">
        
        <?php if ($just_ordered): ?>
            <div class="order-success-banner">
                <i class="fas fa-check-circle"></i>
                <div>
                    <h5 class="mb-1 fw-bold">Order Placed Successfully!</h5>
                    <p class="mb-0 small">Thank you for your order. Your food is being prepared with care.</p>
                </div>
            </div>
        <?php endif; ?>

        <div class="orders-grid">
            <?php if ($history_res && $history_res->num_rows > 0): ?>
                <?php 
                while ($row = $history_res->fetch_assoc()): 
                    $order_id = $row['id'];
                    $items_res = $conn->query("SELECT oi.*, mi.name, mi.image, mi.category FROM order_items oi JOIN menu_items mi ON oi.menu_item_id = mi.id WHERE oi.order_id = '$order_id'");
                    $status = strtolower($row['status']);
                    $status_class = $status == 'completed' ? 'status-completed' : 
                                 ($status == 'cancelled' ? 'status-cancelled' : 'status-pending');
                    $status_icon = $status == 'completed' ? 'fa-check-circle' : 
                                 ($status == 'cancelled' ? 'fa-times-circle' : 'fa-clock');
                    $order_date = new DateTime($row['created_at']);
                    $delivery_time = (clone $order_date)->modify('+45 minutes');
                    $now = new DateTime();
                    $is_delivered = $status == 'completed' || $now > $delivery_time;
                    $delivery_status = $is_delivered ? 'Delivered on ' . $delivery_time->format('M j, Y g:i A') : 'Estimated delivery by ' . $delivery_time->format('g:i A');
                ?>
                <div class="order-card">
                    <div class="order-card-header">
                        <div class="order-meta">
                            <div class="d-flex align-items-center mb-1">
                                <span class="order-id">ORDER #<?= str_pad($row['id'], 6, '0', STR_PAD_LEFT) ?></span>
                                <span class="order-date"><?= $order_date->format('M j, Y') ?></span>
                            </div>
                            <h5 class="order-status">
                                <i class="fas <?= $status_icon ?> me-2"></i>
                                <?= ucfirst($status) ?>
                            </h5>
                            <p class="delivery-time mb-0">
                                <i class="fas <?= $is_delivered ? 'fa-check-circle text-success' : 'fa-truck' ?> me-2"></i>
                                <?= $delivery_status ?>
                            </p>
                        </div>
                        <div class="order-actions">
                            <button class="btn btn-sm btn-outline-secondary me-2">
                                <i class="fas fa-redo me-1"></i> Reorder
                            </button>
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-question-circle me-1"></i> Help
                            </button>
                        </div>
                    </div>

                    <div class="order-items">
                        <?php 
                        $item_count = 0;
                        $max_items = 3;
                        $total_items = $items_res->num_rows;
                        $items_res->data_seek(0); // Reset pointer
                        
                        while ($item = $items_res->fetch_assoc()): 
                            $item_count++;
                            if ($item_count <= $max_items): 
                        ?>
                            <div class="order-item">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="assets/images/menu/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="item-image" onerror="this.src='https://via.placeholder.com/300x200?text=Food+Image'">
                                <?php else: ?>
                                    <div class="item-image-placeholder">
                                        <i class="fas fa-utensils"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="item-details">
                                    <h6 class="item-name"><?= htmlspecialchars($item['name']) ?></h6>
                                    <div class="item-meta">
                                        <span class="item-category"><?= htmlspecialchars($item['category']) ?></span>
                                        <span class="item-qty">Quantity: <?= $item['quantity'] ?></span>
                                    </div>
                                    <div class="item-price">$<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
                                </div>
                            </div>
                        <?php 
                            endif;
                        endwhile; 
                        
                        if ($total_items > $max_items): 
                            $more_items = $total_items - $max_items;
                        ?>
                            <div class="more-items">
                                <i class="fas fa-plus-circle me-2"></i>
                                <?= $more_items ?> more item<?= $more_items > 1 ? 's' : '' ?> in this order
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center mt-5">No orders found.</p>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5>Pizza Place</h5>
                    <p>Serving delicious food since 2023. We're committed to providing the best dining experience with quality ingredients and exceptional service.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-yelp"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="full_menu.php">Our Menu</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h5>Contact Us</h5>
                    <p>
                        <i class="fas fa-map-marker-alt me-2"></i> 123 Pizza Street, Foodie City<br>
                        <i class="fas fa-phone me-2 mt-2 d-inline-block"></i> (123) 456-7890<br>
                        <i class="fas fa-envelope me-2 mt-2 d-inline-block"></i> info@pizzaplace.com
                    </p>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h5>Opening Hours</h5>
                    <p class="mb-1">Monday - Friday: 11:00 AM - 10:00 PM</p>
                    <p class="mb-1">Saturday - Sunday: 12:00 PM - 11:00 PM</p>
                    <p class="mt-3">Delivery available during business hours</p>
                </div>
            </div>
            <div class="copyright">
                &copy; 2026 Pizza Place. All rights reserved.
            </div>
        </div>
    </footer>

</body>
</html>