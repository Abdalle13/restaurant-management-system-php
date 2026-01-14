<?php
require_once('../config/db.php');
require_once('../config/auth.php');

// Security check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

/**
 * 1. XALKA REVENUE-GA: 
 * Maadaama 'payments' ay madhnaan karto, waxaan dakhliga ka soo saaraynaa 
 * dalabaadka xaaladoodu tahay 'completed'.
 */
$total_res = $conn->query("SELECT SUM(total_price) as total FROM orders WHERE status = 'completed'");
$total_revenue = 0;
if ($total_res) {
    $total_revenue = $total_res->fetch_assoc()['total'] ?? 0;
}

// 2. Soo saar dhamaan xogta Payments-ka si loogu muujiyo Table-ka
$payments = $conn->query("SELECT p.*, o.id as order_num, u.username 
                          FROM payments p 
                          LEFT JOIN orders o ON p.order_id = o.id 
                          LEFT JOIN users u ON o.user_id = u.id
                          ORDER BY p.id DESC");

// 3. Tirada guud ee dalabaadka (Transactions)
$trans_res = $conn->query("SELECT COUNT(*) as total FROM orders");
$total_transactions = $trans_res->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Financial Overview | Somaal Bistro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --orange: #d35400;
            --dark-blue: #1a2035;
            --light-bg: #f8faff;
        }

        body {
            background: var(--light-bg);
            font-family: 'Public Sans', sans-serif;
            color: #444;
        }

        .main-content {
            margin-left: 260px;
            width: calc(100% - 260px);
            min-height: 100vh;
        }

        .navbar-custom {
            background: white;
            border-bottom: 1px solid #edf2f7;
            padding: 15px 30px;
        }

        .stat-card {
            border: none;
            border-radius: 16px;
            padding: 25px;
            background: white;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            transition: 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .icon-circle {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .table-container {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
        }

        .table {
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .table tbody tr {
            background: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
            border-radius: 10px;
        }

        .method-tag {
            font-size: 12px;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 8px;
        }

        .bg-cash-light {
            background: #e6fffa;
            color: #319795;
        }

        .bg-card-light {
            background: #ebf4ff;
            color: #3182ce;
        }

        .bg-mobile-light {
            background: #fff5f5;
            color: #e53e3e;
        }

        .badge-paid {
            background: #dcfce7;
            color: #15803d;
            border-radius: 30px;
            padding: 6px 15px;
            font-weight: 700;
            font-size: 11px;
        }

        .badge-unpaid {
            background: #fee2e2;
            color: #b91c1c;
            border-radius: 30px;
            padding: 6px 15px;
            font-weight: 700;
            font-size: 11px;
        }

        .bg-orange {
            background-color: var(--orange) !important;
        }
    </style>
</head>

<body>

    <div class="d-flex">
        <?php include('../dashboard/sidebar.php'); ?>

        <div class="main-content">
            <nav class="navbar-custom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">Financial Analytics</h5>
                <div class="d-flex align-items-center">
                <span class="me-3 d-none d-md-block text-dark">Welcome, <strong><?= htmlspecialchars($_SESSION['username']); ?></strong></span>
                <div class="bg-orange text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width:40px; height:40px; font-weight: bold;">
                    <?= strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
            </div>
            </nav>

            <div class="p-4">
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="stat-card d-flex align-items-center border-start border-success border-5">
                            <div class="icon-circle bg-success bg-opacity-10 text-success me-3">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-0 fw-bold">TOTAL REVENUE (COMPLETED)</p>
                                <h3 class="fw-bold mb-0">$<?= number_format($total_revenue, 2) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card d-flex align-items-center border-start border-primary border-5">
                            <div class="icon-circle bg-primary bg-opacity-10 text-primary me-3">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-0 fw-bold">TOTAL ORDERS</p>
                                <h3 class="fw-bold mb-0"><?= $total_transactions ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Payment Records</h5>
                        <span class="badge bg-light text-dark border"><?= $payments->num_rows ?> Records Found</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Ref</th>
                                    <th>Customer</th>
                                    <th>Method</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th class="text-end">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($payments->num_rows > 0): ?>
                                    <?php while ($row = $payments->fetch_assoc()): ?>
                                        <tr>
                                            <td><span class="text-muted small">#PAY-<?= $row['id'] ?></span></td>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($row['username'] ?? 'Guest User') ?></td>
                                            <td>
                                                <?php
                                                $m = strtolower($row['payment_method'] ?? 'cash');
                                                $tag = ($m == 'card') ? 'bg-card-light' : (($m == 'mobile') ? 'bg-mobile-light' : 'bg-cash-light');
                                                $icon = ($m == 'card') ? 'fa-credit-card' : (($m == 'mobile') ? 'fa-mobile-screen' : 'fa-money-bill-wave');
                                                ?>
                                                <span class="method-tag <?= $tag ?>">
                                                    <i class="fas <?= $icon ?> me-1"></i> <?= strtoupper($m) ?>
                                                </span>
                                            </td>
                                            <td class="fw-bold text-success">$<?= number_format($row['amount'], 2) ?></td>
                                            <td>
                                                <span class="<?= ($row['status'] == 'paid') ? 'badge-paid' : 'badge-unpaid' ?>">
                                                    <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                                                    <?= strtoupper($row['status'] ?? 'pending') ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <div class="small fw-bold text-dark">
                                                    <?= $row['paid_at'] ? date('d M, Y', strtotime($row['paid_at'])) : '---' ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <img src="https://illustrations.popsy.co/gray/no-data.svg" style="width: 150px;" class="mb-3">
                                            <p class="text-muted">No specific payment records in the database yet.</p>
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

</body>

</html>