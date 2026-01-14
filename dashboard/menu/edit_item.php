<?php
require_once('../../config/db.php');
require_once('../../config/auth.php');
require_admin();

$id = intval($_GET['id']);
$item = $conn->query("SELECT * FROM menu_items WHERE id = $id")->fetch_assoc();

if (isset($_POST['update_menu'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = floatval($_POST['price']);
    $description = $_POST['description'];
    $status = $_POST['status'];

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        // Remove old image if exists
        if (!empty($item['image']) && file_exists("../../assets/images/menu/" . $item['image'])) {
            unlink("../../assets/images/menu/" . $item['image']);
        }
        
        $image = time() . '_' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], "../../assets/images/menu/" . $image);
    } else {
        $image = $item['image'];
    }

    $stmt = $conn->prepare("UPDATE menu_items SET name=?, category=?, price=?, description=?, image=?, status=? WHERE id=?");
    $stmt->bind_param("ssdsssi", $name, $category, $price, $description, $image, $status, $id);
    
    if ($stmt->execute()) {
        header("Location: menu.php?msg=Item updated successfully");
        exit();
    } else {
        $error = "Error updating item: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Menu Item</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { 
            --orange: #d35400; 
            --dark-blue: #1a2035; 
        }
        body { 
            background: #f5f7fd; 
            font-family: 'Public Sans', sans-serif; 
        }
        .main-content { 
            margin-left: 260px; 
            width: calc(100% - 260px); 
            min-height: 100vh; 
        }
        .btn-orange { 
            background: var(--orange); 
            color: white; 
            border: none; 
            border-radius: 10px;
            font-weight: 600;
            padding: 10px 25px;
        }
        .btn-orange:hover { 
            background: #a04000; 
            color: white; 
        }
        .card { 
            border: none; 
            border-radius: 20px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); 
        }
        .form-control, .form-select {
            padding: 12px 15px;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            background: #f9f9f9;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--orange);
            box-shadow: 0 0 0 0.25rem rgba(211, 84, 0, 0.15);
        }
        .current-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid #eee;
            margin-bottom: 15px;
        }
        .navbar-custom {
            background: white;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        }
        .bg-orange { background-color: var(--orange); }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include('../sidebar.php'); ?>

    <div class="main-content">
        <nav class="navbar navbar-custom px-4 py-3 mb-4">
            <h5 class="mb-0 fw-bold">Edit Menu Item</h5>
            <div class="d-flex align-items-center">
                <span class="me-3 d-none d-md-block text-dark">Welcome, <strong><?= htmlspecialchars($_SESSION['username']); ?></strong></span>
                <div class="bg-orange text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width:40px; height:40px; font-weight: bold;">
                    <?= strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
            </div>
        </nav>

        <div class="container-fluid px-4">
            <div class="row">
                <div class="col-12">
                    <div class="card p-4">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="row g-4">
                                <div class="col-md-8">
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label fw-bold">Item Name</label>
                                            <input type="text" name="name" class="form-control" 
                                                   value="<?= htmlspecialchars($item['name']) ?>" required>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Category</label>
                                            <input type="text" name="category" class="form-control" 
                                                   value="<?= htmlspecialchars($item['category']) ?>" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Price ($)</label>
                                            <input type="number" step="0.01" name="price" class="form-control" 
                                                   value="<?= number_format($item['price'], 2) ?>" required>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-bold">Description</label>
                                            <textarea name="description" class="form-control" 
                                                      rows="3"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-bold">Status</label>
                                            <select name="status" class="form-select">
                                                <option value="available" <?= $item['status'] == 'available' ? 'selected' : '' ?>>Available</option>
                                                <option value="out_of_stock" <?= $item['status'] == 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="text-center mb-3">
                                            <img src="../../assets/images/menu/<?= $item['image'] ?>" 
                                                 class="current-img" 
                                                 onerror="this.src='https://via.placeholder.com/150?text=No+Image'">
                                            <p class="small text-muted mb-0">Current Image</p>
                                        </div>
                                        
                                        <div class="w-100">
                                            <label class="form-label fw-bold">Change Image</label>
                                            <input type="file" name="image" class="form-control" accept="image/*">
                                            <div class="form-text">Leave blank to keep current image</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <button type="submit" name="update_menu" class="btn btn-orange px-4 py-3">
                                        <i class="fas fa-save me-2"></i> Update Item
                                    </button>
                                    <a href="menu.php" class="btn btn-outline-secondary px-4 py-3 ms-2">
                                        <i class="fas fa-times me-2"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Preview image before upload
    document.querySelector('input[type="file"]').addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.querySelector('.current-img').src = e.target.result;
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
</script>
</body>
</html>