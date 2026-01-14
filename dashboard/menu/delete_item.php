<?php
require_once('../../config/db.php');
require_once('../../config/auth.php');
require_admin();

if (!isset($_GET['id'])) {
    header('Location: menu.php?error=No ID specified');
    exit;
}

$id = intval($_GET['id']);

// Get item details before deleting
$item = $conn->query("SELECT * FROM menu_items WHERE id = $id")->fetch_assoc();

if (!$item) {
    header('Location: menu.php?error=Item not found');
    exit;
}

// Delete the item's image if it exists
if (!empty($item['image']) && file_exists("../../assets/images/menu/" . $item['image'])) {
    unlink("../../assets/images/menu/" . $item['image']);
}

// Delete the item from database
if ($conn->query("DELETE FROM menu_items WHERE id = $id")) {
    header('Location: menu.php?success=Item deleted successfully');
} else {
    header('Location: menu.php?error=Error deleting item: ' . $conn->error);
}
exit;