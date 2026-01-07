<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (isset($_POST['add'])) {

    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $status = $_POST['status'];

    mysqli_query($conn,
        "INSERT INTO users (username, password, role, status)
         VALUES ('$username','$password','$role','$status')"
    );

    header("Location: users.php");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add User</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="container">
    <h2>Add User</h2>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>

        <select name="role">
            <option value="admin">Admin</option>
            <option value="staff">Staff</option>
        </select>

        <select name="status">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>

        <button type="submit" name="add">Add User</button>
    </form>
</div>

</body>
</html>
