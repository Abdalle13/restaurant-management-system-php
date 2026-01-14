<?php
session_start();
require_once('config/db.php');

// 1. Add item to cart session
if (isset($_POST['add_to_cart'])) {
    $id = $_POST['menu_id'];
    $_SESSION['cart'][$id] = [
        'name' => $_POST['name'],
        'price' => $_POST['price'],
        'image' => $_POST['image'],
        'qty' => ($_SESSION['cart'][$id]['qty'] ?? 0) + 1
    ];
}

$subtotal = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['qty'];
    }
}
$delivery = 2.00;
$tax = $subtotal * 0.10;
$total = $subtotal + $delivery + $tax;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Pizza Place</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --orange: #d35400;
            --dark-bg: #212529;
            --light-bg: #f8f9fa;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #fcfcfc;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Navbar Styles */
        .navbar-brand {
            color: var(--orange) !important;
            font-weight: bold;
            font-size: 1.5rem;
        }

        .nav-link {
            font-weight: 500;
            padding: 0.5rem 1rem !important;
        }

        .nav-link:hover {
            color: var(--orange) !important;
        }

        .cart-count {
            position: absolute;
            top: -5px;
            right: -8px;
            background: var(--orange);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 2rem 0;
        }

        /* Cart Styles */
        .checkout-header { text-align: center; padding: 40px 0; }
        .checkout-header h1 { font-weight: 800; color: #222; }
        .checkout-header span, .text-orange { color: var(--orange) !important; }

        .cart-section { 
            background: white; 
            border-radius: 15px; 
            padding: 25px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .cart-item { 
            border-bottom: 1px solid #eee; 
            padding: 15px 0; 
            display: flex; 
            flex-wrap: wrap;
            align-items: center; 
            justify-content: space-between; 
        }

        .cart-item:last-child { border-bottom: none; }
        
        .item-info { 
            display: flex; 
            align-items: center; 
            flex: 1;
            min-width: 200px;
            margin-bottom: 10px;
        }
        
        .item-img { 
            width: 80px; 
            height: 80px; 
            border-radius: 12px; 
            object-fit: cover; 
            margin-right: 15px; 
            border: 2px solid #fff; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.05); 
        }
        
        .item-name { 
            font-weight: 700; 
            margin-bottom: 5px; 
            color: #333;
        }
        
        .item-price { 
            color: var(--orange); 
            font-weight: bold; 
            font-size: 1.1rem;
        }

        /* Quantity Controls */
        .qty-box { 
            background: #f8f9fa; 
            border-radius: 8px; 
            padding: 5px 12px; 
            display: inline-flex; 
            align-items: center; 
            gap: 12px;
            border: 1px solid #e9ecef;
        }
        
        .qty-btn { 
            border: none; 
            background: none; 
            color: var(--orange); 
            font-weight: bold; 
            font-size: 1.2rem; 
            cursor: pointer;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            transition: all 0.2s;
        }
        
        .qty-btn:hover {
            background: rgba(211, 84, 0, 0.1);
        }
        
        .qty-display {
            min-width: 20px;
            text-align: center;
            font-weight: 600;
        }

        /* Summary Card */
        .summary-card { 
            background: white; 
            border-radius: 15px; 
            padding: 30px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            position: sticky;
            top: 30px;
        }
        
        .summary-title { 
            font-weight: 800; 
            margin-bottom: 25px; 
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f3f5; 
        }
        
        .form-label { 
            font-size: 0.9rem; 
            font-weight: 600; 
            color: #495057; 
            margin-bottom: 8px; 
        }
        
        .form-control { 
            border-radius: 10px; 
            padding: 12px 15px; 
            border: 1px solid #e9ecef; 
            background: #f8f9fa; 
            transition: all 0.3s; 
            font-size: 0.95rem;
        }
        
        .form-control:focus { 
            border-color: var(--orange); 
            box-shadow: 0 0 0 0.25rem rgba(211, 84, 0, 0.15); 
            background: white; 
        }

        .total-row { 
            border-top: 2px dashed #e9ecef; 
            padding-top: 20px; 
            margin-top: 20px; 
        }
        
        .btn-place-order { 
            background: var(--orange); 
            color: white; 
            border: none; 
            width: 100%; 
            padding: 14px; 
            border-radius: 10px; 
            font-weight: 700; 
            font-size: 1.05rem; 
            box-shadow: 0 5px 15px rgba(211, 84, 0, 0.2); 
            transition: all 0.3s; 
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-place-order:hover { 
            background: #b34700; 
            transform: translateY(-2px); 
            box-shadow: 0 7px 20px rgba(211, 84, 0, 0.25);
        }
        
        .btn-update { 
            background: white; 
            color: #6c757d; 
            border: 1px solid #dee2e6; 
            border-radius: 8px; 
            width: 100%; 
            padding: 10px; 
            font-weight: 600; 
            margin-top: 15px;
            transition: all 0.2s;
        }
        
        .btn-update:hover {
            background: #f8f9fa;
            border-color: #ced4da;
        }
        
        /* Footer Styles */
        footer {
            background: var(--dark-bg);
            color: #fff;
            padding: 4rem 0 2rem;
            margin-top: 3rem;
        }
        
        footer h5 {
            color: #fff;
            font-weight: 700;
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
        }
        
        footer p, footer a {
            color: #adb5bd;
            margin-bottom: 0.8rem;
            display: block;
            text-decoration: none;
            transition: color 0.2s;
        }
        
        footer a:hover {
            color: var(--orange);
            text-decoration: none;
        }
        
        .social-links a {
            display: inline-block;
            width: 36px;
            height: 36px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            text-align: center;
            line-height: 36px;
            margin-right: 10px;
            color: #fff;
            transition: all 0.3s;
        }
        
        .social-links a:hover {
            background: var(--orange);
            transform: translateY(-3px);
        }
        
        .copyright {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 1.5rem;
            margin-top: 3rem;
            text-align: center;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .cart-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .item-info {
                width: 100%;
                margin-bottom: 15px;
            }
            
            .qty-box {
                margin-top: 10px;
            }
            
            .summary-card {
                position: static;
                margin-top: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg bg-white sticky-top py-3 shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">Pizza Place</a>
            <div class="collapse navbar-collapse justify-content-center">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="full_menu.php">Menu</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link" href="complete_order.php">My Orders</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="d-flex align-items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="logout.php" class="btn btn-logout px-4">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-login px-4">Login</a>
                    <a href="register.php" class="btn btn-register px-4">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="container">
            <div class="checkout-header">
                <h1>Your Culinary <span>Journey</span></h1>
                <p class="text-muted">Review and complete your delicious order</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="cart-section">
                        <h5 class="fw-bold mb-4"><i class="fas fa-shopping-basket me-2 text-orange"></i> Your Selection</h5>
                        <?php if (!empty($_SESSION['cart'])): ?>
                            <?php foreach ($_SESSION['cart'] as $id => $item): ?>
                            <div class="cart-item" data-item-id="<?= $id ?>" data-unit-price="<?= $item['price'] ?>">
                                <div class="item-info">
                                    <img src="assets/images/menu/<?= $item['image'] ?>" class="item-img" onerror="this.src='https://via.placeholder.com/100'">
                                    <div>
                                        <div class="item-name"><?= $item['name'] ?></div>
                                        <div class="item-price">
                                            <span class="unit-price">$<?= number_format($item['price'], 2) ?></span>
                                            <span class="text-muted">x</span>
                                            <span class="item-total">$<?= number_format($item['price'] * $item['qty'], 2) ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="qty-box">
                                    <button class="qty-btn" type="button">-</button>
                                    <span class="qty-display fw-bold"><?= $item['qty'] ?></span>
                                    <button class="qty-btn" type="button">+</button>
                                    <a href="#" class="remove-item text-danger ms-2" data-item-id="<?= $id ?>" title="Remove item">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <img src="https://cdn-icons-png.flaticon.com/512/11329/11329060.png" width="100" class="mb-3 opacity-50">
                                <h5>Your cart is empty!</h5>
                                <a href="full_menu.php" class="btn btn-orange mt-3 text-white" style="background: var(--orange)">Go Back to Menu</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="summary-card">
                        <h4 class="summary-title">Order Summary</h4>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-bold" data-subtotal>$<?= number_format($subtotal, 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Delivery Fee</span>
                            <span class="fw-bold">$<?= number_format($delivery, 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="text-muted">Tax (10%)</span>
                            <span class="fw-bold" data-tax>$<?= number_format($tax, 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between total-row mb-4">
                            <h4 class="fw-bold">Total</h4>
                            <h4 class="fw-bold text-orange" data-total>$<?= number_format($total, 2) ?></h4>
                        </div>
                        <form action="complete_order.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label">FULL NAME</label>
                                <input type="text" name="customer_name" class="form-control" placeholder="e.g. John Doe" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">PHONE NUMBER</label>
                                <input type="tel" name="phone" class="form-control" placeholder="e.g. 123-456-7890" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">DELIVERY ADDRESS</label>
                                <textarea name="address" class="form-control" rows="3" placeholder="Enter your delivery address" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-orange btn-lg w-100 text-white" style="background-color: var(--orange);">PLACE ORDER</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <div class="row g-5">
                <div class="col-md-3">
                    <h5 class="text-orange">Pizza Place</h5>
                    <p class="small">Bringing authentic flavors to your table since 2010. We pride ourselves on traditional recipes with a modern twist.</p>
                    <div class="social-icons mt-4">
                        <i class="fab fa-facebook-f text-white me-3"></i>
                        <i class="fab fa-instagram text-white me-3"></i>
                        <i class="fab fa-twitter text-white"></i>
                    </div>
                </div>
                <div class="col-md-2">
                    <h5 class="text-orange">Quick Links</h5>
                    <a href="index.php" class="footer-link">Home</a>
                    <a href="full_menu.php" class="footer-link">Menu</a>
                    <a href="complete_order.php" class="footer-link">my orders</a>
                </div>
                <div class="col-md-3">
                    <h5 class="text-orange">Contact Us</h5>
                    <p class="small mb-2"><i class="fas fa-map-marker-alt me-2"></i> Digfeer Street, Mogadishu</p>
                    <p class="small mb-2"><i class="fas fa-phone me-2"></i> +252 619157381</p>
                    <p class="small mb-2"><i class="fas fa-envelope me-2"></i> info@pizzaplace.com</p>
                    <p class="small"><i class="fas fa-clock me-2"></i> Open Daily: 9:00 AM - 11:00 PM</p>
                </div>
                <div class="col-md-4">
                    <h5 class="text-orange">Newsletter</h5>
                    <p class="small mb-3">Subscribe for updates and special offers</p>
                    <div class="subscribe-group">
                        <input type="email" placeholder="Your email">
                        <button class="btn-subscribe px-3">Subscribe</button>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5 pt-4 border-top border-secondary">
                <p class="small mb-0">&copy; 2026 Pizza Place. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Format number to 2 decimal places
        function formatPrice(price) {
            return parseFloat(price).toFixed(2);
        }

        // Calculate and update item total price
        function updateItemTotal(itemRow, newQty) {
            const pricePerItem = parseFloat(itemRow.dataset.unitPrice);
            const totalPrice = pricePerItem * newQty;
            itemRow.querySelector('.item-total').textContent = `$${formatPrice(totalPrice)}`;
            return totalPrice;
        }

        // Update order summary
        function updateOrderSummary() {
            let subtotal = 0;
            
            // Calculate new subtotal from all items
            document.querySelectorAll('.cart-item').forEach(item => {
                const qty = parseInt(item.querySelector('.qty-display').textContent);
                subtotal += parseFloat(item.dataset.unitPrice) * qty;
            });
            
            const delivery = 2.00;
            const tax = subtotal * 0.10;
            const total = subtotal + delivery + tax;
            
            // Update the summary
            document.querySelector('[data-subtotal]').textContent = `$${formatPrice(subtotal)}`;
            document.querySelector('[data-tax]').textContent = `$${formatPrice(tax)}`;
            document.querySelector('[data-total]').textContent = `$${formatPrice(total)}`;
            
            return { subtotal, tax, total };
        }

        // Quantity Controls
        document.querySelectorAll('.qty-btn').forEach(button => {
            button.addEventListener('click', async function() {
                const qtyDisplay = this.parentElement.querySelector('.qty-display');
                let value = parseInt(qtyDisplay.textContent);
                const itemRow = this.closest('.cart-item');
                
                // Update quantity
                if (this.textContent === '+' && value < 20) {
                    value++;
                } else if (this.textContent === '-' && value > 1) {
                    value--;
                } else {
                    return; // No change needed
                }
                
                // Update display
                qtyDisplay.textContent = value;
                
                // Update item total price
                updateItemTotal(itemRow, value);
                
                // Update order summary
                updateOrderSummary();
                
                // Update cart in session via AJAX
                try {
                    const response = await fetch('update_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `item_id=${itemRow.dataset.itemId}&quantity=${value}`
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Update cart count in navbar
                        const cartCount = document.querySelector('.cart-count');
                        if (cartCount) {
                            cartCount.textContent = data.cart_count;
                        }
                    }
                } catch (error) {
                    console.error('Error updating cart:', error);
                }
            });
        });
        
        // Handle remove item
        document.addEventListener('click', async function(e) {
            if (e.target.closest('.remove-item')) {
                e.preventDefault();
                if (!confirm('Are you sure you want to remove this item from your cart?')) {
                    return;
                }
                
                const removeBtn = e.target.closest('.remove-item');
                const itemId = removeBtn.dataset.itemId;
                const itemRow = removeBtn.closest('.cart-item');
                
                // Show loading state
                const originalHTML = removeBtn.innerHTML;
                removeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                removeBtn.style.pointerEvents = 'none';
                
                try {
                    const response = await fetch('remove_from_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id=${encodeURIComponent(itemId)}`
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Remove the item row from the DOM with animation
                        itemRow.style.transition = 'all 0.3s ease';
                        itemRow.style.opacity = '0';
                        itemRow.style.height = '0';
                        itemRow.style.margin = '0';
                        itemRow.style.padding = '0';
                        itemRow.style.overflow = 'hidden';
                        
                        // Wait for animation to complete before removing
                        setTimeout(() => {
                            itemRow.remove();
                            
                            // Update cart count in navbar
                            const cartCount = document.querySelector('.cart-count');
                            if (cartCount) {
                                cartCount.textContent = data.cart_count || '0';
                                if (data.cart_count <= 0) {
                                    cartCount.style.display = 'none';
                                }
                            }
                            
                            // Update order summary
                            updateOrderSummary();
                            
                            // If cart is empty, show empty cart message
                            if (document.querySelectorAll('.cart-item').length === 0) {
                                const cartSection = document.querySelector('.cart-section');
                                cartSection.innerHTML = `
                                    <div class="text-center py-5">
                                        <img src="https://cdn-icons-png.flaticon.com/512/11329/11329060.png" width="100" class="mb-3 opacity-50">
                                        <h5>Your cart is empty!</h5>
                                        <a href="full_menu.php" class="btn btn-orange mt-3 text-white" style="background: var(--orange)">Go Back to Menu</a>
                                    </div>
                                `;
                            }
                        }, 300);
                    } else {
                        throw new Error(data.message || 'Failed to remove item');
                    }
                } catch (error) {
                    console.error('Error removing item:', error);
                    alert(error.message || 'An error occurred while removing the item. Please try again.');
                    removeBtn.innerHTML = originalHTML;
                    removeBtn.style.pointerEvents = 'auto';
                }
            }
        });

        // Update cart count in navbar on page load
        function updateCartCount() {
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) {
                const count = document.querySelectorAll('.cart-item').length;
                cartCount.textContent = count;
                cartCount.style.display = count > 0 ? 'flex' : 'none';
            }
        }

        // Initialize cart functionality
        document.addEventListener('DOMContentLoaded', () => {
            // Update item totals
            document.querySelectorAll('.cart-item').forEach(item => {
                const qty = parseInt(item.querySelector('.qty-display').textContent);
                updateItemTotal(item, qty);
            });
            
            // Update order summary
            updateOrderSummary();
            
            // Update cart count
            updateCartCount();
            
            // Add animation to cart items
            const cartItems = document.querySelectorAll('.cart-item');
            cartItems.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateX(-20px)';
                item.style.transition = 'all 0.3s ease';
                
                setTimeout(() => {
                    item.style.opacity = '1';
                    item.style.transform = 'translateX(0)';
                }, 100 * index);
            });
        });
    </script>
</body>
</html>