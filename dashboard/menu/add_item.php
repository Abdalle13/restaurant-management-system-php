<?php
require_once('../../config/db.php');
require_once('../../config/auth.php');
require_admin();

if (isset($_POST['add_menu'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = floatval($_POST['price']);
    $description = $_POST['description'];
    $status = $_POST['status'];
    $image = '';

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $image = time() . '_' . basename($_FILES['image']['name']);
        $target = "../../assets/images/menu/" . $image;
        
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $error = "Failed to upload image. Please try again.";
        }
    } else {
        $error = "Please select an image for the menu item.";
    }

    if (!isset($error)) {
        $stmt = $conn->prepare("INSERT INTO menu_items (name, category, price, description, image, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsss", $name, $category, $price, $description, $image, $status);
        
        if ($stmt->execute()) {
            header("Location: menu.php?msg=Menu item added successfully");
            exit();
        } else {
            $error = "Error adding menu item: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Menu Item</title>
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
        .preview-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px dashed #ddd;
            margin-bottom: 15px;
            display: none;
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
            <h5 class="mb-0 fw-bold">Add New Menu Item</h5>
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
                                            <input type="text" name="name" class="form-control" required>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Category</label>
                                            <input type="text" name="category" class="form-control" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Price ($)</label>
                                            <input type="number" step="0.01" name="price" class="form-control" required>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-bold">Description</label>
                                            <textarea name="description" class="form-control" rows="3"></textarea>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-bold">Status</label>
                                            <select name="status" class="form-select" required>
                                                <option value="available">Available</option>
                                                <option value="out_of_stock">Out of Stock</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="text-center mb-3">
                                            <img id="imagePreview" class="preview-img">
                                            <p class="small text-muted mb-0" id="imageText">No image selected</p>
                                        </div>
                                        
                                        <div class="w-100">
                                            <label class="form-label fw-bold">Item Image</label>
                                            <input type="file" name="image" id="imageUpload" class="form-control" accept="image/*" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <button type="submit" name="add_menu" class="btn btn-orange px-4 py-3">
                                        <i class="fas fa-plus-circle me-2"></i> Add Menu Item
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
    // Image preview functionality
    document.getElementById('imageUpload').addEventListener('change', function(e) {
        const file = this.files[0];
        const preview = document.getElementById('imagePreview');
        const imageText = document.getElementById('imageText');
        
        if (file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                imageText.textContent = file.name;
                imageText.classList.remove('text-muted');
                imageText.classList.add('text-success', 'fw-bold');
            }
            
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
            imageText.textContent = 'No image selected';
            imageText.classList.remove('text-success', 'fw-bold');
            imageText.classList.add('text-muted');
        }
    });
</script>
</body>
</html>