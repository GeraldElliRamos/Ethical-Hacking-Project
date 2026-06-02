<?php
session_start();
header('Content-Type: application/json');
include('config.php');

$count = 0;
if (isset($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart_items WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $count = (int)$row['total'];
    }
} else {
    if (isset($_SESSION['guest_cart']) && is_array($_SESSION['guest_cart'])) {
        foreach ($_SESSION['guest_cart'] as $item) {
            $count += $item['quantity'];
        }
    }
}

echo json_encode(['count' => $count]);