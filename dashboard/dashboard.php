<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Session expire after 5 minutes (300 seconds)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 300)) {
    session_unset();
    session_destroy();
    header("Location: ../login.php?session=expired");
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <div class="container">

        <h2>Welcome, <?php echo $_SESSION['username']; ?></h2>

        <p>Role: <strong><?php echo $_SESSION['role']; ?></strong></p>

        <hr>

        <ul>
            <li><a href="users.php">Manage Users</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>

    </div>

</body>

</html>