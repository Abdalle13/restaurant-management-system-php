<?php
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../config/auth.php');

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
// Get current directory for active state
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Function to check if menu item is active
function isActive($page, $dir = null)
{
    global $current_page, $current_dir;
    if (is_array($page)) {
        return in_array($current_page, $page) || ($dir && in_array($current_dir, $page));
    }
    return $current_page === $page || ($dir && $current_dir === $page);
}
?>

<style>
    :root {
        --orange: #d35400;
        --dark-blue: #1a2035;
    }

    .sidebar {
        width: 260px;
        height: 100vh;
        background: var(--dark-blue);
        position: fixed;
        color: white;
        z-index: 1000;
        display: flex;
        flex-direction: column;
    }

    .sidebar-header {
        padding: 30px 20px;
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .nav-pills .nav-link {
        color: #b9bbbe !important;
        padding: 12px 20px;
        margin: 4px 15px;
        border-radius: 10px;
        transition: 0.3s;
        display: flex;
        align-items: center;
        text-decoration: none;
        font-size: 14px;
    }

    .nav-pills .nav-link:hover {
        background: rgba(255, 255, 255, 0.1);
        color: white !important;
    }

    .nav-pills .nav-link.active {
        background: var(--orange) !important;
        color: white !important;
    }

    .role-label {
        font-size: 11px;
        color: #8391a2;
        padding: 20px 30px 5px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: bold;
    }

    .sidebar-footer {
        margin-top: auto;
        padding-bottom: 20px;
    }
</style>

<div class="sidebar shadow">
    <div class="sidebar-header">
        <h3 class="fw-bold mb-0" style="color: var(--orange);">Admin Panel </h3>
    </div>

    <div class="nav flex-column nav-pills mt-3">

        <?php if (isAdmin()): ?>
            <div class="role-label">Admin Tools</div>
            <a href="<?= ADMIN_URL ?>dashboard.php" class="nav-link <?= isActive('dashboard.php', 'dashboard') ? 'active' : '' ?>">
                <i class="fas fa-chart-line me-2"></i> Dashboard
            </a>
            <a href="<?= ADMIN_URL ?>users/manage_users.php" class="nav-link <?= isActive('manage_users.php', 'users') ? 'active' : '' ?>">
                <i class="fas fa-users-cog me-2"></i> Manage Users
            </a>
            

                    <a href="<?= ADMIN_URL ?>menu/menu.php" class="nav-link <?= isActive('menu.php', 'menu') || isActive(['add_item.php', 'edit_item.php', 'delete_item.php']) ? 'active' : '' ?>">
            <i class="fas fa-utensils me-2"></i> Food Menu
        </a>

    

        <a href="<?= ADMIN_URL ?>orders/orders.php" class="nav-link <?= isActive('orders.php', 'orders') || isActive(['view_order.php', 'add_order.php', 'update_status.php']) ? 'active' : '' ?>">
            <i class="fas fa-shopping-basket me-2"></i> Manage Orders
        </a>

        <a href="<?= ADMIN_URL ?>../payments/payments.php" class="nav-link <?= isActive('payments.php', 'payments') ? 'active' : '' ?>">
                <i class="fas fa-wallet me-2"></i> Payments
            </a>
        <?php endif; ?>


    </div>

    <div class="sidebar-footer">
        <hr class="mx-3 opacity-25 text-white">
        <a href="<?= BASE_URL ?>logout.php" class="nav-link text-danger mx-3">
            <i class="fas fa-sign-out-alt me-2"></i> Logout
        </a>
    </div>
</div>