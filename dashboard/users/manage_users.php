<?php
require_once('../../config/db.php');
require_once('../../config/auth.php');

// Only admin can access this page
require_admin(); 

// 1. Handle permanent user deletion
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $my_id = $_SESSION['user_id'];

    // Prevent admin from deleting themselves
    if ($id !== $my_id) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            header("Location: manage_users.php?msg=User deleted permanently");
            exit();
        }
    }
}

// 2. Fetch all users with proper role handling
$users = $conn->query("
    SELECT id, username, first_name, last_name, email, 
            COALESCE(role, 'user') as role 
    FROM users 
    ORDER BY 
        CASE 
            WHEN role = 'admin' THEN 1 
            ELSE 2 
        END, 
        first_name ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage All Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { 
            --danger: #e74c3c; 
            --dark: #2c3e50; 
            --admin-bg: #3498db;
            --user-bg: #6c757d;
            --orange: #d35400;
        }
        body { background: #f8f9fa; }
        .main-content { margin-left: 260px; padding: 0; } /* Ka saar padding-ka halkan si nav-ku u dhuufo */
        .content-body { padding: 30px; } /* Padding-ka halkan ku dar */
        .table-card { 
            background: white; 
            border-radius: 12px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.05); 
            border: none; 
        }
        .badge-admin { 
            background: var(--admin-bg); 
            color: white; 
        }
        .badge-user { 
            background: var(--user-bg); 
            color: white;
        }
        .action-btn {
            transition: all 0.2s;
        }
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .navbar-custom {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: white;
            border-bottom: 1px solid #eee;
        }
        .bg-orange { background-color: var(--orange); }
    </style>
</head>
<body>

<div class="d-flex">
    <?php include('../sidebar.php'); ?>

    <div class="main-content w-100">
        <nav class="navbar navbar-custom shadow-sm px-4 py-3">
            <h5 class="mb-0 text-muted fw-bold">User Management</h5>
            <div class="d-flex align-items-center">
                <span class="me-3 d-none d-md-block text-dark">Welcome, <strong><?= htmlspecialchars($_SESSION['username']); ?></strong></span>
                <div class="bg-orange text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width:40px; height:40px; font-weight: bold;">
                    <?= strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
            </div>
        </nav>

        <div class="content-body">
            <div class="mb-4">
                <h2 class="fw-bold text-dark">System Users</h2>
                <p class="text-muted">Admin panel to manage, promote, or permanently remove accounts.</p>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_GET['msg']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card table-card">
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3 ps-4">FULL NAME</th>
                                <th>USERNAME</th>
                                <th>ROLE</th>
                                <th>EMAIL</th>
                                <th class="text-end pe-4">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($u = $users->fetch_assoc()): 
                                $role = !empty($u['role']) ? $u['role'] : 'user';
                                $isCurrentUser = $u['id'] == $_SESSION['user_id'];
                            ?>
                            <tr>
                                <td class="fw-bold ps-4"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
                                <td><span class="text-primary">@<?= htmlspecialchars($u['username']) ?></span></td>
                                <td>
                                    <span class="badge rounded-pill <?= $role === 'admin' ? 'badge-admin' : 'badge-user' ?> px-3 py-2">
                                        <?= ucfirst($role) ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row['first_name'] ?? ''); ?></td>
                                <td class="text-end pe-4">
                                    <?php if(!$isCurrentUser): ?>
                                        <a href="manage_users.php?delete_id=<?= $u['id'] ?>" 
                                           class="btn btn-sm btn-danger px-3 action-btn" 
                                           onclick="return confirm('WARNING: This action is permanent. Delete this user?')">
                                            <i class="fas fa-user-times me-1"></i> Delete
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small fst-italic">You (Current Admin)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>