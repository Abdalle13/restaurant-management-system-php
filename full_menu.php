<?php
session_start();
require_once('config/db.php');

// 1. Soo qaado categories-ka
$categories = $conn->query("SELECT DISTINCT category FROM menu_items ORDER BY category ASC");

// 2. Logic-ga Sifaynta (Filter by Category Name)
$cat_filter = "";
$active_cat = "";
if (isset($_GET['cat']) && !empty($_GET['cat'])) {
    $active_cat = $_GET['cat'];
    $cat_name = $conn->real_escape_string($_GET['cat']); 
    $cat_filter = " AND category = '$cat_name'"; 
}

// 3. Soo qaado menu-ga iyadoo la eegayo filter-ka
$menu_res = $conn->query("SELECT * FROM menu_items WHERE status = 'available' $cat_filter");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Menu | Pizza Place</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --orange: #d35400;
            --dark-bg: #212529;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #fcfcfc;
        }

        /* Navbar Styles */
        .navbar-brand {
            color: var(--orange) !important;
            font-weight: bold;
            font-size: 1.5rem;
        }

        .nav-link:hover {
            color: var(--orange) !important;
        }

        /* Menu Header */
        .menu-header {
            padding: 40px 0;
            text-align: center;
        }

        .category-btn {
            border-radius: 20px;
            border: 1px solid #ddd;
            padding: 8px 20px;
            margin: 5px;
            color: #555;
            text-decoration: none;
            display: inline-block;
            transition: 0.3s;
            background: white;
        }

        .category-btn.active,
        .category-btn:hover {
            background: var(--orange);
            color: white;
            border-color: var(--orange);
        }

        /* Item Cards */
        .item-card {
            border: none;
            border-radius: 15px;
            background: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            height: 100%;
            transition: 0.3s;
        }

        .item-card:hover {
            transform: translateY(-5px);
        }

        .item-img-container {
            position: relative;
            height: 200px;
        }

        .item-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .status-badge {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background: rgba(255, 255, 255, 0.9);
            padding: 2px 10px;
            border-radius: 10px;
            font-size: 12px;
            color: #27ae60;
            font-weight: 600;
        }

        .btn-add {
            background: var(--orange);
            color: white;
            border-radius: 5px;
            padding: 6px 15px;
            border: none;
            font-size: 14px;
            transition: 0.3s;
            cursor: pointer;
        }

        .btn-add:hover {
            background: #a04000;
            color: white;
        }

        .price-text {
            color: var(--orange);
            font-weight: bold;
            font-size: 1.1rem;
        }

        /* Footer Styles from Index */
        footer {
            background: var(--dark-bg);
            color: #adb5bd;
            padding: 60px 0 20px;
            margin-top: 50px;
        }

        footer h5 {
            color: var(--orange);
            font-weight: bold;
            margin-bottom: 25px;
        }

        .footer-link {
            color: #adb5bd;
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
        }

        .footer-link:hover {
            color: var(--orange);
        }

        .social-icons i {
            font-size: 20px;
            margin-right: 15px;
            cursor: pointer;
        }

        .subscribe-group {
            display: flex;
        }

        .subscribe-group input {
            border-radius: 4px 0 0 4px;
            border: none;
            padding: 10px;
            flex: 1;
        }

        .btn-subscribe {
            background: var(--orange);
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            padding: 0 20px;
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
                    <li class="nav-item"><a class="nav-link fw-bold text-dark" href="full_menu.php">Menu</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link text-primary" href="track_orders.php">My Orders</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="d-flex align-items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="logout.php" class="btn btn-outline-danger px-4 btn-sm">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-sm btn-outline-dark me-2">Login</a>
                    <a href="register.php" class="btn btn-sm btn-warning">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="menu-header">
            <h2 class="fw-bold">Our Menu</h2>
            <p class="text-muted">Delicious dishes made with love</p>

            <div class="mt-4">
                <a href="full_menu.php" class="category-btn <?= ($active_cat == "") ? 'active' : '' ?>">All Items</a>
                <?php while ($c = $categories->fetch_assoc()): ?>
                    <a href="?cat=<?= urlencode($c['category']) ?>" class="category-btn <?= ($active_cat == $c['category']) ? 'active' : '' ?>">
                        <?= $c['category'] ?>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <?php if ($menu_res && $menu_res->num_rows > 0): ?>
                <?php while ($item = $menu_res->fetch_assoc()): ?>
                    <div class="col-md-3">
                        <div class="card item-card">
                            <div class="item-img-container">
                                <img src="assets/images/menu/<?= $item['image'] ?>" class="item-img" onerror="this.src='https://via.placeholder.com/300x200?text=Food+Image'">
                                <span class="status-badge"><i class="fas fa-circle small"></i> Available</span>
                            </div>
                            <div class="card-body p-3">
                                <h6 class="fw-bold mb-1"><?= $item['name'] ?></h6>
                                <p class="text-muted small mb-3" style="font-size: 11px;"><?= substr($item['description'], 0, 60) ?>...</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="price-text">$<?= number_format($item['price'], 2) ?></span>
                                    
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <form method="POST" action="checkout.php">
                                            <input type="hidden" name="menu_id" value="<?= $item['id'] ?>">
                                            <input type="hidden" name="name" value="<?= htmlspecialchars($item['name']) ?>">
                                            <input type="hidden" name="price" value="<?= $item['price'] ?>">
                                            <input type="hidden" name="image" value="<?= $item['image'] ?>">
                                            <button type="submit" name="add_to_cart" class="btn-add">Add to Cart</button>
                                        </form>
                                    <?php else: ?>
                                        <a href="login.php" class="btn-add text-decoration-none">Login</a>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">No items found in this category.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <div class="container">
            <div class="row g-5">
                <div class="col-md-3">
                    <h5>Pizza Place</h5>
                    <p class="small">Bringing authentic flavors to your table since 2010. We pride ourselves on traditional recipes with a modern twist.</p>
                    <div class="social-icons mt-4">
                        <i class="fab fa-facebook-f text-white me-3"></i>
                        <i class="fab fa-instagram text-white me-3"></i>
                        <i class="fab fa-twitter text-white"></i>
                    </div>
                </div>
                <div class="col-md-2">
                    <h5>Quick Links</h5>
                    <a href="index.php" class="footer-link">Home</a>
                    <a href="full_menu.php" class="footer-link">Menu</a>
                    <a href="#" class="footer-link">About Us</a>
                    <a href="#" class="footer-link">Reservations</a>
                </div>
                <div class="col-md-3">
                    <h5>Contact Us</h5>
                    <p class="small mb-2"><i class="fas fa-map-marker-alt text-orange me-2"></i> Digfeer Street, Mogadishu</p>
                    <p class="small mb-2"><i class="fas fa-phone text-orange me-2"></i> +252 619157381</p>
                    <p class="small mb-2"><i class="fas fa-envelope text-orange me-2"></i> info@pizzaplace.com</p>
                    <p class="small"><i class="fas fa-clock text-orange me-2"></i> Open Daily: 9:00 AM - 11:00 PM</p>
                </div>
                <div class="col-md-4">
                    <h5>Newsletter</h5>
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
</body>
</html>