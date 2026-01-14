<?php
require_once('../config/db.php');
require_once('../config/auth.php');

// 1. Security: Kaliya Admin ayaa geli kara
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// 2. Variables Init
$user_count = 0;
$menu_count = 0;
$order_count = 0;
$revenue = 0;
$recent_orders = [];

try {
    // Tirada Macamiisha (Excluding Admins)
    $res_users = $conn->query("SELECT COUNT(*) as total FROM users WHERE role != 'admin'");
    if ($res_users) $user_count = $res_users->fetch_assoc()['total'];

    // Tirada Cuntooyinka ku jira Menu-ga
    $res_menu = $conn->query("SELECT COUNT(*) as total FROM menu_items");
    if ($res_menu) $menu_count = $res_menu->fetch_assoc()['total'];

    // Dalabaadka sugaya in la qabto (Pending)
    $res_orders = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status='pending'");
    if ($res_orders) $order_count = $res_orders->fetch_assoc()['total'];

    /**
     * UPDATE: XALKA REVENUE-GA
     * Waxaan dakhliga ka xisaabinaynaa table-ka 'orders' halkii ay ahaan lahayd 'payments'
     * si aan u helno lacagta xataa haddii payment record uusan weli abuurmin.
     */
    $res_revenue = $conn->query("SELECT SUM(total_price) as total FROM orders WHERE status='completed'");
    if ($res_revenue) {
        $revenue = $res_revenue->fetch_assoc()['total'] ?? 0;
    }

    // 5-tii dalab ee ugu dambaysay ee dhacay
    $recent_orders = $conn->query("SELECT o.id, u.username, o.total_price, o.status 
                                   FROM orders o 
                                   JOIN users u ON o.user_id = u.id 
                                   ORDER BY o.id DESC LIMIT 5");
} catch (Exception $e) {
    $error_message = "Cilad farsamo: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --orange: #d35400;
            --dark-blue: #1a2035;
            --light-bg: #f5f7fd;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Public Sans', sans-serif;
            margin: 0;
        }

        .main-content {
            margin-left: 260px;
            width: calc(100% - 260px);
            min-height: 100vh;
        }

        .navbar-custom {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: white;
            border-bottom: 1px solid #edf2f7;
        }

        .card {
            border: none;
            border-radius: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.07);
        }

        .bg-orange {
            background-color: var(--orange) !important;
        }

        .text-orange {
            color: var(--orange) !important;
        }

        .btn-orange {
            background-color: var(--orange);
            color: white;
            border: none;
            font-weight: 600;
            border-radius: 8px;
        }

        .btn-orange:hover {
            background-color: #a04000;
            color: white;
        }

        .icon-box {
            width: 55px;
            height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
        }

        .table thead th {
            background-color: #f8f9fa;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #747d8c;
            border: none;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <?php include('sidebar.php'); ?>

        <div class="main-content">
            <nav class="navbar navbar-custom px-4 py-3 shadow-sm">
                <h5 class="mb-0 text-muted fw-bold">Dashboard Overview</h5>
                <div class="d-flex align-items-center">
                <span class="me-3 d-none d-md-block text-dark">Welcome, <strong><?= htmlspecialchars($_SESSION['username']); ?></strong></span>
                <div class="bg-orange text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width:40px; height:40px; font-weight: bold;">
                    <?= strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
            </div>
            </nav>

            <div class="p-4">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger border-0 shadow-sm mb-4">
                        <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>

                <div class="row g-4 mb-5">
                    <div class="col-md-3">
                        <div class="card p-3">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-primary bg-opacity-10 text-primary me-3"><i class="fas fa-users fa-lg"></i></div>
                                <div>
                                    <p class="text-muted mb-0 small fw-bold text-uppercase">Total Users</p>
                                    <h3 class="mb-0 fw-bold"><?= number_format($user_count); ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card p-3">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-warning bg-opacity-10 text-warning me-3"><i class="fas fa-hamburger fa-lg"></i></div>
                                <div>
                                    <p class="text-muted mb-0 small fw-bold text-uppercase">Menu Items</p>
                                    <h3 class="mb-0 fw-bold"><?= number_format($menu_count); ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card p-3 border-start border-danger border-4">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-danger bg-opacity-10 text-danger me-3"><i class="fas fa-clock fa-lg"></i></div>
                                <div>
                                    <p class="text-muted mb-0 small fw-bold text-uppercase">Pending Orders</p>
                                    <h3 class="mb-0 fw-bold"><?= number_format($order_count); ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card p-3 border-start border-success border-4">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-success bg-opacity-10 text-success me-3"><i class="fas fa-dollar-sign fa-lg"></i></div>
                                <div>
                                    <p class="text-muted mb-0 small fw-bold text-uppercase">Total Revenue</p>
                                    <h3 class="mb-0 fw-bold text-success">$<?= number_format($revenue, 2); ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-history me-2 text-orange"></i>Recent Activity</h5>
                            <a href="orders/orders.php" class="btn btn-sm btn-orange px-4 py-2">Manage All Orders</a>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th class="ps-3">Order ID</th>
                                        <th>Customer Name</th>
                                        <th>Amount</th>
                                        <th class="text-end pe-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_orders && $recent_orders->num_rows > 0): ?>
                                        <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                            <tr>
                                                <td class="ps-3 fw-bold text-orange">#ORD-<?= $order['id']; ?></td>
                                                <td class="text-dark fw-medium"><?= htmlspecialchars($order['username']); ?></td>
                                                <td class="fw-bold text-dark">$<?= number_format($order['total_price'], 2); ?></td>
                                                <td class="text-end pe-3">
                                                    <?php
                                                    $status = strtolower($order['status']);
                                                    $badge_class = ($status == 'completed') ? 'bg-success' : (($status == 'pending') ? 'bg-warning' : 'bg-danger');
                                                    ?>
                                                    <span class="badge rounded-pill px-3 py-2 <?= $badge_class ?>">
                                                        <?= ucfirst($order['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted">
                                                <i class="fas fa-folder-open fa-3x mb-3 d-block opacity-25"></i>
                                                Ma jiraan wax dalabaad ah weli.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>