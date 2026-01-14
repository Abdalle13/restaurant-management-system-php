<?php
session_start();
header('Content-Type: application/json');

// Check if request is POST and has required parameters
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['item_id']) || !isset($_POST['quantity'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$itemId = $_POST['item_id'];
$quantity = (int)$_POST['quantity'];
$response = ['success' => false, 'message' => 'Item not found in cart'];

// Check if cart exists and has the item
if (isset($_SESSION['cart'][$itemId])) {
    // Update the quantity
    $_SESSION['cart'][$itemId]['qty'] = $quantity;
    
    // Calculate new cart count
    $cartCount = array_sum(array_column($_SESSION['cart'], 'qty'));
    
    $response = [
        'success' => true,
        'message' => 'Cart updated',
        'cart_count' => $cartCount
    ];
}

echo json_encode($response);
