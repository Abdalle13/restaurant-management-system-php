<?php
// Connect to database
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "restaurant_db";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// New admin password
$new_password = "admin123";

// Hash the new password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Update the admin password in the database
$sql = "UPDATE users SET password = ? WHERE username = 'admin' AND role = 'admin' LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $hashed_password);
    if (mysqli_stmt_execute($stmt)) {
        echo "Admin password has been updated successfully!<br>";
        echo "New password: " . $new_password . "<br>";
        echo "<a href='login.php'>Go to Login</a>";
    } else {
        echo "Error updating password: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
} else {
    echo "Error preparing statement: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
