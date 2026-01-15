<?php
include("config/db.php");
include("config/auth.php");

if (isset($_POST['register'])) {
    verify_csrf();

    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $gender     = trim($_POST['gender'] ?? '');
    $username   = trim($_POST['username'] ?? '');
    $password   = $_POST['password'] ?? '';
    $phone      = trim($_POST['phone'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    
    // ROLE QARSOON: Qof kasta oo halkan iska diiwaangeliya waa 'user'
    $role       = "user"; 
    $status     = "active";

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = mysqli_prepare($conn, "INSERT INTO users (first_name,last_name,gender,username,password,phone,email,role,status) VALUES (?,?,?,?,?,?,?,?,?)");
    mysqli_stmt_bind_param($stmt, "sssssssss", $first_name, $last_name, $gender, $username, $hashed_password, $phone, $email, $role, $status);

    if (mysqli_stmt_execute($stmt)) {
        set_flash("Account created successfully! Please login.", 'success');
        header("Location: login.php");
        exit;
    } else {
        set_flash("Something went wrong! Username might be taken.", 'danger');
    }
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { 
            --orange-main: #d35400; 
            --orange-gradient: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
            --dark-blue: #1a2035;
        }
        body { 
            background: #f8f9fa;
            font-family: 'Public Sans', sans-serif;
            display: flex; align-items: center; justify-content: center; min-height: 100vh;
            margin: 0; padding: 20px 0;
        }
        .register-card {
            background: #ffffff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            width: 100%; max-width: 550px;
            border-top: 6px solid var(--orange-main);
        }
        .brand-logo {
            width: 60px; height: 60px; background: var(--orange-gradient);
            color: white; border-radius: 15px; display: flex;
            align-items: center; justify-content: center;
            font-size: 24px; font-weight: bold; margin: 0 auto 15px;
            box-shadow: 0 5px 15px rgba(211, 84, 0, 0.2);
        }
        .form-label { font-weight: 600; color: var(--dark-blue); font-size: 14px; }
        .form-control, .form-select {
            padding: 11px; border-radius: 10px; border: 1px solid #dee2e6;
            transition: 0.3s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--orange-main); 
            box-shadow: 0 0 0 4px rgba(211, 84, 0, 0.1);
        }
        .btn-register {
            background: var(--orange-gradient);
            color: white; border: none; padding: 13px;
            border-radius: 10px; font-weight: 700; width: 100%;
            margin-top: 20px; text-transform: uppercase; letter-spacing: 0.5px;
            transition: 0.3s;
        }
        .btn-register:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 8px 15px rgba(211, 84, 0, 0.3); 
            color: #fff; 
        }
        .password-wrapper { position: relative; }
        .eye-icon {
            position: absolute; right: 15px; top: 50%; transform: translateY(-50%);
            cursor: pointer; color: #888;
        }
        .text-orange { color: var(--orange-main) !important; }
        .login-link { font-weight: 700; color: var(--orange-main); text-decoration: none; }
        .login-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <div class="register-card">
        <div class="text-center mb-4">
            <div class="brand-logo">
                <i class="fas fa-user-plus"></i>
            </div>
            <h3 class="fw-bold" style="color: var(--dark-blue);">Create Account</h3>
            <p class="text-muted small">Join Dha Dhan Wanaag and start ordering today.</p>
        </div>

        <?php if (!empty($flash)): ?>
            <div class="alert alert-<?= $flash['type']; ?> alert-dismissible fade show border-0 shadow-sm py-2">
                <small><?= $flash['message']; ?></small>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token(); ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-control" placeholder="John" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control" placeholder="Doe" required>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select" required>
                        <option value="" disabled selected>Choose gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="johndoe123" required>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="reg-password" class="form-control" placeholder="Min. 8 characters" required minlength="8">
                        <i class="fa-solid fa-eye eye-icon" onclick="togglePassword('reg-password', this)"></i>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" placeholder="061xxxxxxx">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="john@example.com">
                </div>
            </div>

            <button name="register" type="submit" class="btn btn-register">Sign Up Now</button>
        </form>

        <div class="text-center mt-4">
            <span class="text-muted small">Already have an account?</span> 
            <a href="login.php" class="login-link small">Sign in here</a>
        </div>
    </div>

    <script>
        function togglePassword(id, el) {
            const input = document.getElementById(id);
            if (input.type === "password") {
                input.type = "text";
                el.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                input.type = "password";
                el.classList.replace("fa-eye-slash", "fa-eye");
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>