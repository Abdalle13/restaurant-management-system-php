<?php
session_start();
include("../config/db.php");

// Protect page
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Delete user
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM users WHERE id=$id");
    header("Location: users.php");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Users</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="container">
    <h2>Users List</h2>

    <a href="add_user.php">➕ Add User</a>
    <br><br>

    <table border="1" width="100%" cellpadding="5">
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Role</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php
        $result = mysqli_query($conn, "SELECT * FROM users");
        while ($row = mysqli_fetch_assoc($result)) {
        ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['username']; ?></td>
            <td><?php echo $row['role']; ?></td>
            <td><?php echo $row['status']; ?></td>
            <td>
                <a href="edit_user.php?id=<?php echo $row['id']; ?>">Edit</a> |
                <a href="users.php?delete=<?php echo $row['id']; ?>"
                   onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php } ?>

    </table>
</div>

</body>
</html>
