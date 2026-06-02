<?php
session_start();
header('Content-Type: application/json');
include('config.php');

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

if (isset($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param('iii', $quantity, $user_id, $product_id);
    $ok = $stmt->execute();
    echo json_encode(['success' => $ok]);
} else {
    $key = (string)$product_id;
    if (isset($_SESSION['guest_cart'][$key])) {
        $_SESSION['guest_cart'][$key]['quantity'] = $quantity;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Item not in guest cart']);
    }
}
?>