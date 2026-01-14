<?php
require_once('../../config/db.php');
require_once('../../config/auth.php');
require_admin();

// Get filter parameters
$status = $_GET['status'] ?? '';
$date = $_GET['date'] ?? date('Y-m-d');

// Build the query
$where = [];
$params = [];
$types = '';

if ($status) {
    $where[] = "o.status = ?";
    $params[] = $status;
    $types .= 's';
}

if ($date) {
    $where[] = "DATE(o.created_at) = ?";
    $params[] = $date;
    $types .= 's';
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get orders
$query = "SELECT o.*, u.username as customer_name 
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id 
          $whereClause 
          ORDER BY o.created_at DESC";
$stmt = $conn->prepare($query);

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$res = $stmt->get_result();

// Get stats
$statsQuery = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN LOWER(status) = 'completed' THEN total_price ELSE 0 END) as total_revenue,
    SUM(CASE WHEN LOWER(status) = 'pending' THEN 1 ELSE 0 END) as pending_count,
    SUM(CASE WHEN LOWER(status) = 'processing' THEN 1 ELSE 0 END) as processing_count
    FROM orders";
$stats = $conn->query($statsQuery)->fetch_assoc();
$stats['total_revenue'] = (float)($stats['total_revenue'] ?? 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management</title>
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
        }
        .navbar-custom {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: white;
            border-bottom: 1px solid #edf2f7;
        }
        .btn-orange { 
            background: var(--orange); 
            color: white; 
            border-radius: 10px; 
            font-weight: 600;
            border: none;
            padding: 8px 20px;
        }
        .btn-orange:hover { 
            background: #a04000; 
            color: white; 
        }
        .card { 
            border: none; 
            border-radius: 15px; 
            box-shadow: 0 5px 20px rgba(0,0,0,0.03); 
        }
        .stat-card {
            background: white;
            border-radius: 15px;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }
        .badge-status {
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: 500;
        }
        .badge-pending {
            background: #fff3cd;
            color: #856404;
        }
        .badge-processing {
            background: #cce5ff;
            color: #004085;
        }
        .badge-completed {
            background: #d4edda;
            color: #155724;
        }
        .badge-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        .bg-orange { background-color: var(--orange); }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include('../sidebar.php'); ?>

    <div class="main-content">
        <nav class="navbar navbar-custom px-4 py-3 mb-4">
            <h5 class="mb-0 fw-bold">Order Management</h5>
            <div class="d-flex align-items-center">
                <span class="me-3 d-none d-md-block text-dark small">
                    Welcome, <strong><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></strong>
                </span>
                <div class="bg-orange text-white rounded-circle d-flex align-items-center justify-content-center" 
                     style="width:40px; height:40px; font-weight: bold;">
                    <?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)); ?>
                </div>
            </div>
        </nav>

        <div class="container-fluid px-4">
            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="stat-card p-4 h-100">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3 me-3">
                                <i class="fas fa-shopping-cart fa-lg"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-0"><?= $stats['total_orders'] ?? 0 ?></h4>
                                <small class="text-muted">Total Orders</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card p-4 h-100">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3 me-3">
                                <i class="fas fa-clock fa-lg"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-0 text-warning"><?= $stats['pending_count'] ?? 0 ?></h4>
                                <small class="text-muted">Pending Orders</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card p-4 h-100">
                        <div class="d-flex align-items-center">
                            <div class="bg-info bg-opacity-10 text-info p-3 rounded-3 me-3">
                                <i class="fas fa-spinner fa-spin fa-lg"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-0 text-info"><?= $stats['processing_count'] ?? 0 ?></h4>
                                <small class="text-muted">Processing</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card p-4 h-100">
                        <div class="d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 text-success p-3 rounded-3 me-3">
                                <i class="fas fa-chart-line fa-lg"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-0 text-success">
                                    $<?= number_format($stats['total_revenue'] ?? 0, 2) ?>
                                </h4>
                                <small class="text-muted">Total Revenue</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Filter -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body p-3">
                            <form method="GET" id="statusForm" class="d-flex align-items-center">
                                <label class="form-label mb-0 me-2 fw-bold">Show:</label>
                                <select name="status" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                                    <option value="">All Orders</option>
                                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending Orders</option>
                                    <option value="processing" <?= $status === 'processing' ? 'selected' : '' ?>>Processing</option>
                                    <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                                <?php if ($status): ?>
                                <a href="orders.php" class="btn btn-sm btn-outline-secondary ms-2">
                                    <i class="fas fa-times me-1"></i> Clear Filter
                                </a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">ORDER #</th>
                                    <th>CUSTOMER</th>
                                    <th>ITEMS</th>
                                    <th>TOTAL</th>
                                    <th>STATUS</th>
                                    <th>DATE</th>
                                    <th class="text-end pe-4">ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($res->num_rows > 0): ?>
                                    <?php while ($order = $res->fetch_assoc()): 
                                        $statusClass = [
                                            'pending' => 'badge-pending',
                                            'processing' => 'badge-processing',
                                            'completed' => 'badge-completed',
                                            'cancelled' => 'badge-cancelled'
                                        ][$order['status']] ?? 'badge-secondary';
                                    ?>
                                    <tr>
                                        <td class="ps-4 fw-bold">#<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                    <i class="fas fa-user text-muted"></i>
                                                </div>
                                                <span class="fw-medium"><?= htmlspecialchars($order['customer_name'] ?? 'Guest') ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                            $items = $conn->query("
                                                SELECT COUNT(*) as count 
                                                FROM order_items 
                                                WHERE order_id = " . (int)$order['id']
                                            )->fetch_assoc();
                                            echo $items['count'] ?? 0;
                                            ?> items
                                        </td>
                                        <td>$<?= number_format((float)($order['total_price'] ?? 0), 2) ?></td>
                                        <td>
                                            <?php 
                                            $statusIcons = [
                                                'pending' => 'fa-clock',
                                                'processing' => 'fa-spinner fa-spin',
                                                'completed' => 'fa-check-circle',
                                                'cancelled' => 'fa-times-circle'
                                            ];
                                            $statusLabels = [
                                                'pending' => 'Pending',
                                                'processing' => 'Processing',
                                                'completed' => 'Completed',
                                                'cancelled' => 'Cancelled'
                                            ];
                                            ?>
                                            <span class="badge rounded-pill d-inline-flex align-items-center <?= $statusClass ?> px-3 py-1">
                                                <i class="fas <?= $statusIcons[$order['status']] ?? 'fa-question-circle' ?> me-1"></i>
                                                <?= $statusLabels[$order['status']] ?? ucfirst($order['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="small text-muted"><?= date('M d, Y', strtotime($order['created_at'])) ?></div>
                                            <div class="extra-small text-muted"><?= date('h:i A', strtotime($order['created_at'])) ?></div>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex gap-2 justify-content-end">
                                                <a href="view_order.php?id=<?= $order['id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                                   title="View Order">
                                                    <i class="fas fa-eye me-1"></i> View
                                                </a>
                                                <?php if ($order['status'] === 'pending'): ?>
                                                <a href="update_status.php?id=<?= $order['id'] ?>&status=processing" 
                                                   class="btn btn-sm btn-outline-info rounded-pill px-3"
                                                   title="Process Order"
                                                   onclick="return confirm('Mark this order as processing?')">
                                                    <i class="fas fa-spinner me-1"></i> Process
                                                </a>
                                                <?php elseif ($order['status'] === 'processing'): ?>
                                                <a href="update_status.php?id=<?= $order['id'] ?>&status=completed" 
                                                   class="btn btn-sm btn-outline-success rounded-pill px-3"
                                                   title="Complete Order"
                                                   onclick="return confirm('Mark this order as completed?')">
                                                    <i class="fas fa-check-circle me-1"></i> Complete
                                                </a>
                                                <?php endif; ?>
                                                <?php if ($order['status'] !== 'completed' && $order['status'] !== 'cancelled'): ?>
                                                <a href="update_status.php?id=<?= $order['id'] ?>&status=cancelled" 
                                                   class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                                   title="Cancel Order"
                                                   onclick="return confirm('Are you sure you want to cancel this order?')">
                                                    <i class="fas fa-times-circle me-1"></i> Cancel
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                            No orders found
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