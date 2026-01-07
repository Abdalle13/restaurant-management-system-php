<?php
session_start();

// Destroy session
session_unset();
session_destroy();

// Delete cookie
if (isset($_COOKIE['username'])) {
    setcookie("username", "", time() - 3600, "/");
}

header("Location: login.php");
exit();
?>
