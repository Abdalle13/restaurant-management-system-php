<?php
session_start();
header('Content-Type: application/json');

// Check if request is POST and has item ID
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$itemId = $_POST['id'];
$response = ['success' => false, 'message' => 'Item not found in cart'];

// Check if cart exists and has items
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    if (isset($_SESSION['cart'][$itemId])) {
        // Remove the item from the cart
        unset($_SESSION['cart'][$itemId]);
        
        // Update cart count
        $cartCount = array_sum(array_column($_SESSION['cart'] ?? [], 'qty'));
        
        $response = [
            'success' => true,
            'message' => 'Item removed from cart',
            'cart_count' => $cartCount
        ];
    }
}

echo json_encode($response);
