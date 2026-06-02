<?php
session_start();
header('Content-Type: application/json');
include('config.php');

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

if (isset($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param('ii', $user_id, $product_id);
    $ok = $stmt->execute();
    echo json_encode(['success' => $ok]);
} else {
    $key = (string)$product_id;
    unset($_SESSION['guest_cart'][$key]);
    echo json_encode(['success' => true]);
}
?>