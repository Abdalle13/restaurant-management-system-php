<?php
session_start();
require_once('config/db.php');

// Soo qaado 4 cunto oo kaliya (Signature Section)
$menu_res = $conn->query("SELECT * FROM menu_items WHERE status = 'available' LIMIT 4");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pizza Place | Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --orange: #d35400;
            --dark-bg: #212529;
        }

        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #333;
        }

        /* Navbar Styles */
        .navbar-brand {
            color: var(--orange) !important;
            font-weight: bold;
            font-size: 1.5rem;
        }

        .nav-link {
            color: #555 !important;
            font-weight: 500;
            margin: 0 10px;
        }

        .btn-login {
            border: 1px solid var(--orange);
            color: var(--orange);
        }

        .btn-register {
            background: var(--orange);
            color: white;
            margin-left: 10px;
        }

        .btn-logout {
            border: 1px solid #dc3545;
            color: #dc3545;
            font-weight: 500;
        }

        .btn-logout:hover {
            background: #dc3545;
            color: white;
        }

        /* Hero Section - Halkan ayaan ku saxay sawirka galka ku jira */
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('assets/images/hero.jpg');
            /* Hubi in magacu yahay hero-bg.jpg */
            background-size: cover;
            background-position: center;
            height: 600px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
        }

        .btn-orange {
            background: var(--orange);
            color: white;
            border: none;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
        }

        .btn-outline-white {
            border: 1px solid white;
            color: white;
            padding: 12px 30px;
            margin-left: 15px;
            text-decoration: none;
            border-radius: 5px;
        }

        /* Sections */
        .section-padding {
            padding: 80px 0;
        }

        /* Why Choose Us Icons */
        .icon-circle {
            width: 80px;
            height: 80px;
            background: var(--orange);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin: 0 auto 20px;
        }

        /* Dishes Card - Halkan ayaan sawirada ku saxay */
        .dish-card {
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: 0.3s;
            height: 100%;
            border-radius: 10px;
            overflow: hidden;
        }

        .dish-card:hover {
            transform: translateY(-5px);
        }

        .dish-img {
            height: 200px;
            width: 100%;
            object-fit: cover;
        }

        /* object-fit wuxuu saxayaa sawirka stretch-ka noqonaya */

        .price {
            color: var(--orange);
            font-weight: bold;
            font-size: 1.2rem;
        }

        .btn-order {
            border: 1px solid var(--orange);
            color: var(--orange);
            font-size: 0.8rem;
            text-decoration: none;
            font-weight: 600;
        }

        .btn-order:hover {
            background: var(--orange);
            color: white;
        }

        /* Footer */
        footer {
            background: var(--dark-bg);
            color: #adb5bd;
            padding: 60px 0 20px;
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
                    <li class="nav-item"><a class="nav-link" href="full_menu.php">Menu</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link text-primary" href="complete_order.php">My Orders</a></li>
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

    <section class="hero">
        <div class="container">
            <h1 class="display-3 fw-bold mb-3">Welcome to Pizza Place</h1>
            <p class="fs-5 mb-5 text-light">Experience authentic flavors in a modern setting</p>
            <div class="d-flex justify-content-center">
                <a href="full_menu.php" class="btn btn-orange rounded shadow">View Menu</a>
                <a href="complete_order.php" class="btn btn-outline-white rounded shadow">See your Orders</a>
            </div>
        </div>
    </section>

    <section class="section-padding" id="menu">
        <div class="container text-center">
            <h2 class="fw-bold mb-5">Our Signature Dishes</h2>
            <div class="row g-4">
                <?php while ($item = $menu_res->fetch_assoc()): ?>
                    <div class="col-md-3 text-start">
                        <div class="card dish-card">
                            <img src="assets/images/menu/<?= $item['image'] ?>" class="card-img-top dish-img" alt="<?= $item['name'] ?>" onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'">
                            <div class="card-body">
                                <h5 class="fw-bold mb-2"><?= $item['name'] ?></h5>
                                <p class="text-muted small mb-3"><?= substr($item['description'], 0, 70) ?>...</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="price">$<?= number_format($item['price'], 2) ?></span>
                                    <a href="full_menu.php" class="btn btn-order px-3 py-1">Order Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <a href="full_menu.php" class="btn btn-orange mt-5 px-5 rounded">View Full Menu</a>
        </div>
    </section>

    <section class="section-padding bg-light">
        <div class="container text-center">
            <h2 class="fw-bold mb-5">Why Choose Us</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="icon-circle"><i class="fas fa-utensils"></i></div>
                    <h5 class="fw-bold">Authentic Somali Flavors</h5>
                    <p class="text-muted px-4">Traditional recipes passed down through generations</p>
                </div>
                <div class="col-md-4">
                    <div class="icon-circle"><i class="fas fa-calendar-alt"></i></div>
                    <h5 class="fw-bold">Easy Online Booking</h5>
                    <p class="text-muted px-4">Reserve your favorite meal in just 3 clicks</p>
                </div>
                <div class="col-md-4">
                    <div class="icon-circle"><i class="fas fa-truck"></i></div>
                    <h5 class="fw-bold">Fast Delivery</h5>
                    <p class="text-muted px-4">Hot meals delivered directly to your door</p>
                </div>
            </div>
        </div>
    </section>

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