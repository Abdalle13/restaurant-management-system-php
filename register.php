<?php
include("config/db.php");

$message = "";
$message_type = "";

if (isset($_POST['register'])) {

    $first_name = $_POST['first_name'];
    $last_name  = $_POST['last_name'];
    $gender     = $_POST['gender'];
    $username   = $_POST['username'];
    $password   = $_POST['password'];
    $phone      = $_POST['phone'];
    $email      = $_POST['email'];
    $role       = $_POST['role'];
    $status     = "active";

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users 
        (first_name,last_name,gender,username,password,phone,email,role,status)
        VALUES
        ('$first_name','$last_name','$gender','$username','$hashed_password','$phone','$email','$role','$status')";

    if (mysqli_query($conn, $sql)) {
        $message = "Registration successful. Redirecting to login...";
        $message_type = "success";
        header("Refresh:2; url=login.php");
    } else {
        $message = "Something went wrong!";
        $message_type = "error";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<div class="container">

    <div class="brand">
        <div class="logo">R</div>
        <p class="subtitle">Create your account</p>
    </div>

    <?php if ($message): ?>
        <div class="alert <?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <!-- First & Last Name -->
        <div class="form-row">
            <div class="field">
                <label>First Name</label>
                <input type="text" name="first_name" required>
            </div>

            <div class="field">
                <label>Last Name</label>
                <input type="text" name="last_name" required>
            </div>
        </div>

        <!-- Gender & Role -->
        <div class="form-row">
            <div class="field">
                <label>Gender</label>
                <select name="gender" required>
                    <option value="">Select gender</option>
                    <option>Male</option>
                    <option>Female</option>
                </select>
            </div>

            <div class="field">
                <label>Role</label>
                <select name="role">
                    <option value="admin">Admin</option>
                    <option value="staff">Staff</option>
                </select>
            </div>
        </div>

        <!-- Username -->
        <div class="field">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>

        <!-- Password -->
        <div class="field">
            <label>Password</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="reg-password" required minlength="8">
                <i class="fa-solid fa-eye eye-icon"
                   onclick="togglePassword('reg-password', this)"></i>
            </div>
        </div>

        <!-- Phone & Email -->
        <div class="form-row">
            <div class="field">
                <label>Phone</label>
                <input type="text" name="phone">
            </div>

            <div class="field">
                <label>Email</label>
                <input type="email" name="email">
            </div>
        </div>

        <button name="register" class="btn primary">Create Account</button>
    </form>

    <div class="form-footer">
        Already have an account? <a href="login.php">Sign in</a>
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
