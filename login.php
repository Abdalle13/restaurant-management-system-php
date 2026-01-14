<?php
include("config/db.php");
include("config/auth.php");

$error = "";

if (isset($_POST['login'])) {
    verify_csrf();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = mysqli_prepare($conn, "SELECT id, username, password, role FROM users WHERE username=? AND status='active' LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if ($res && mysqli_num_rows($res) === 1) {
        $user = mysqli_fetch_assoc($res);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if (!empty($_POST['remember'])) {
                setcookie("username", $user['username'], time() + 604800, "/", "", false, true);
            }

            // --- KALA SAARISTA ADMIN IYO USER ---
            if ($user['role'] === 'admin') {
                header("Location: dashboard/dashboard.php");
            } else {
                header("Location: index.php"); // Tani waa User Home Page-ka
            }
            exit;

        } else {
            $error = "Incorrect password. Please try again.";
        }
    } else {
        $error = "User not found or account is inactive.";
    }
}

$flash = get_flash();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --orange: #d35400;
            --dark: #1a2035;
        }
        body {
            background-color: #f4f7f6;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Public Sans', sans-serif;
        }
        .login-card {
            background: #fff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .brand-logo {
            width: 70px;
            height: 70px;
            background: var(--orange);
            color: white;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            font-weight: bold;
            margin: 0 auto 20px;
        }
        .login-card h3 {
            text-align: center;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 5px;
        }
        .login-card p {
            text-align: center;
            color: #777;
            margin-bottom: 30px;
        }
        .form-label {
            font-weight: 600;
            font-size: 14px;
            color: var(--dark);
        }
        .form-control {
            border-radius: 10px;
            padding: 12px;
            border: 1px solid #ddd;
        }
        .form-control:focus {
            border-color: var(--orange);
            box-shadow: 0 0 0 0.25 red;
            box-shadow: 0 0 0 0.25rem rgba(211, 84, 0, 0.15);
        }
        .password-wrapper {
            position: relative;
        }
        .eye-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #888;
        }
        .btn-orange {
            background-color: var(--orange);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            width: 100%;
            margin-top: 10px;
            transition: 0.3s;
        }
        .btn-orange:hover {
            background-color: #a04000;
            color: white;
        }
        .login-footer {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
        }
        .login-footer a {
            color: var(--orange);
            text-decoration: none;
            font-weight: bold;
        }
        .alert {
            border-radius: 10px;
            font-size: 14px;
            border: none;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="brand-logo shadow-sm">
            <i class="fas fa-utensils"></i>
        </div>
        <h3> Pizza Place</h3>
        <p>Please enter your details</p>

        <?php if (!empty($flash)): ?>
            <div class="alert alert-info py-2"><?php echo htmlspecialchars($flash['message']); ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger py-2"><i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter your username" value="<?php echo htmlspecialchars($_COOKIE['username'] ?? ''); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="login-password" class="form-control" placeholder="••••••••" required>
                    <i class="fas fa-eye eye-icon" onclick="togglePassword('login-password', this)"></i>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label small" for="remember">Remember me</label>
                </div>
                <a href="#" class="small text-muted text-decoration-none">Forgot Password?</a>
            </div>

            <button type="submit" name="login" class="btn btn-orange">Sign In</button>
        </form>

        <div class="login-footer">
            Don’t have an account? <a href="register.php">Create Account</a>
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

</body>
</html>