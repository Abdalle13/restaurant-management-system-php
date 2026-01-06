<?php
include("config/db.php");

$message = "";

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

    // Password encryption
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert query
    $sql = "INSERT INTO users 
    (first_name, last_name, gender, username, password, phone, email, role, status)
    VALUES
    ('$first_name','$last_name','$gender','$username','$hashed_password','$phone','$email','$role','$status')";

    if (mysqli_query($conn, $sql)) {
        $message = "Registration successful";
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="container">

    <h2>User Registration</h2>

    <p class="message"><?php echo $message; ?></p>

    <form method="POST">

        <input type="text" name="first_name" placeholder="First Name" required>

        <input type="text" name="last_name" placeholder="Last Name" required>

        <select name="gender" required>
            <option value="">Select Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select>

        <input type="text" name="username" placeholder="Username" required>

        <input type="password" name="password" placeholder="Password" required>

        <input type="text" name="phone" placeholder="Phone">

        <input type="email" name="email" placeholder="Email">

        <select name="role">
            <option value="admin">Admin</option>
            <option value="staff">Staff</option>
        </select>

        <button type="submit" name="register">Register</button>

    </form>

</div>

</body>
</html>
