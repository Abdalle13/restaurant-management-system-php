<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'];
$user = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT * FROM users WHERE id=$id")
);

if (isset($_POST['update'])) {

    $role = $_POST['role'];
    $status = $_POST['status'];

    mysqli_query(
        $conn,
        "UPDATE users SET role='$role', status='$status' WHERE id=$id"
    );

    header("Location: users.php");
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Edit User</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <div class="container">
        <h2>Edit User</h2>

        <form method="POST">

            <input type="text" value="<?php echo $user['username']; ?>" disabled>

            <select name="role">
                <option value="admin" <?php if ($user['role'] == "admin") echo "selected"; ?>>Admin</option>
                <option value="staff" <?php if ($user['role'] == "staff") echo "selected"; ?>>Staff</option>
            </select>

            <select name="status">
                <option value="active" <?php if ($user['status'] == "active") echo "selected"; ?>>Active</option>
                <option value="inactive" <?php if ($user['status'] == "inactive") echo "selected"; ?>>Inactive</option>
            </select>

            <button type="submit" name="update">Update</button>
        </form>
    </div>

</body>

</html>