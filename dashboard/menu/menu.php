<?php
require_once('../../config/db.php');
require_once('../../config/auth.php');
require_admin();

// Handle success/error messages
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Fetch all menu items
$menu_items = $conn->query("SELECT * FROM menu_items ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management</title>
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
            font-weight: bold; 
            border: none; 
        }
        .btn-orange:hover { 
            background: #a04000; 
            color: white; 
        }
        .bg-orange { 
            background-color: var(--orange) !important; 
        }
        .card { 
            border: none; 
            border-radius: 20px; 
            box-shadow: 0 5px 20px rgba(0,0,0,0.03); 
        }
        .menu-img { 
            width: 60px; 
            height: 60px; 
            border-radius: 12px; 
            object-fit: cover; 
            border: 2px solid #fff; 
        }
        .badge-available { 
            background: #e6fffa; 
            color: #234e52; 
            border: 1px solid #b2f5ea; 
        }
        .badge-out { 
            background: #fff5f5; 
            color: #822727; 
            border: 1px solid #feb2b2; 
        }
        .table thead th { 
            background: #fdfdfd; 
            text-transform: uppercase; 
            font-size: 11px; 
            letter-spacing: 1px;
            padding: 15px;
        }
        .table tbody tr {
            transition: all 0.2s;
        }
        .table tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.03);
        }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include('../sidebar.php'); ?>

    <div class="main-content">
        <nav class="navbar navbar-custom px-4 py-3 mb-4">
            <h5 class="mb-0 fw-bold">Menu Management</h5>
           <div class="d-flex align-items-center">
                <span class="me-3 d-none d-md-block text-dark">Welcome, <strong><?= htmlspecialchars($_SESSION['username']); ?></strong></span>
                <div class="bg-orange text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width:40px; height:40px; font-weight: bold;">
                    <?= strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
            </div>
        </nav>

        <div class="container-fluid px-4">
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-0">Menu Items</h4>
                    <p class="text-muted small">Manage your restaurant's menu items</p>
                </div>
                <a href="add_item.php" class="btn btn-orange px-4">
                    <i class="fas fa-plus me-2"></i> Add New Item
                </a>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">ITEM</th>
                                    <th>CATEGORY</th>
                                    <th class="text-center">PRICE</th>
                                    <th class="text-center">STATUS</th>
                                    <th class="text-end pe-4">ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($menu_items->num_rows > 0): ?>
                                    <?php while($item = $menu_items->fetch_assoc()): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <img src="../../assets/images/menu/<?= htmlspecialchars($item['image']) ?>" 
                                                     class="menu-img me-3 shadow-sm" 
                                                     onerror="this.src='https://via.placeholder.com/60?text=No+Image'">
                                                <div>
                                                    <div class="fw-bold text-dark"><?= htmlspecialchars($item['name']) ?></div>
                                                    <small class="text-muted text-truncate d-inline-block" style="max-width: 250px;">
                                                        <?= htmlspecialchars($item['description'] ?? 'No description') ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill bg-light text-dark px-3 border">
                                                <?= htmlspecialchars($item['category']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center fw-bold text-orange">
                                            $<?= number_format($item['price'], 2) ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if($item['status'] == 'available'): ?>
                                                <span class="badge badge-available px-3 py-2 rounded-pill">
                                                    <i class="fas fa-check me-1"></i> Available
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-out px-3 py-2 rounded-pill">
                                                    <i class="fas fa-times me-1"></i> Out of Stock
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group">
                                                <a href="edit_item.php?id=<?= $item['id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary border-0 rounded-circle me-1" 
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete_item.php?id=<?= $item['id'] ?>" 
                                                   class="btn btn-sm btn-outline-danger border-0 rounded-circle" 
                                                   onclick="return confirm('Are you sure you want to delete this item?')" 
                                                   title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="fas fa-utensils fa-3x mb-3 d-block"></i>
                                            No menu items found. Add your first item to get started.
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