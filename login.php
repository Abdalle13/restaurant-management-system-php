<?php
session_start();
include("config/db.php");

$error = "";

if (isset($_POST['login'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username='$username' AND status='active'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) === 1) {

        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if (!empty($_POST['remember'])) {
                setcookie("username", $user['username'], time() + 604800, "/");
            }

            header("Location: dashboard/dashboard.php");
            exit;
        } else {
            $error = "Incorrect password";
        }
    } else {
        $error = "User not found or inactive";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>

    <div class="container">

        <div class="brand">
            <div class="logo">R</div>
            <p class="subtitle">Welcome back</p>
        </div>

        <?php if ($error): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">

            <div class="field">
                <label>Username</label>
                <input type="text" name="username"
                    value="<?php echo htmlspecialchars($_COOKIE['username'] ?? ''); ?>" required>
            </div>

            <div class="field">
                <label>Password</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="login-password" required>
                    <i class="fas fa-eye eye-icon" onclick="togglePassword('login-password', this)"></i>
                </div>
            </div>
            <div class="field-inline">
                <label class="checkbox">
                    <input type="checkbox" name="remember">
                    Remember me
                </label>
                <a href="#" class="forgot">Forgot?</a>
            </div>

            <button name="login" class="btn primary">Sign In</button>
        </form>

        <div class="form-footer">
            Don’t have an account? <a href="register.php">Sign up</a>
        </div>

    </div>

    <script>
        function togglePassword(id, el) {
            const input = document.getElementById(id);
            if (input.type === "password") {
                input.type = "text";
                el.classList.remove("fa-eye");
                el.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                el.classList.remove("fa-eye-slash");
                el.classList.add("fa-eye");
            }
        }
    </script>

</body>

</html>