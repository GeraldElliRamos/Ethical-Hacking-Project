<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id'])) {
    // require login for checkout
    header('Location: signup.html');
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// fetch cart items
$items = [];
$stmt = $conn->prepare("SELECT product_id, product_name, image, quantity, price FROM cart_items WHERE user_id = ?");
if ($stmt) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $items[] = $r;
}

if (empty($items)) {
    header('Location: cartpage.php');
    exit;
}

// create orders table and order_items
$conn->query("CREATE TABLE IF NOT EXISTS orders (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, total DECIMAL(10,2), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$conn->query("CREATE TABLE IF NOT EXISTS order_items (id INT AUTO_INCREMENT PRIMARY KEY, order_id INT NOT NULL, product_id INT, product_name VARCHAR(255), image VARCHAR(255), quantity INT, price DECIMAL(10,2)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// compute total (matching cartpage UI: subtotal + 150 delivery fee)
$subtotal = 0;
foreach ($items as $it) $subtotal += ($it['price'] ?: 0) * $it['quantity'];

$deliveryFee = 150;
$total = $subtotal + $deliveryFee;

$ins = $conn->prepare("INSERT INTO orders (user_id,total) VALUES (?,?)");
$ins->bind_param('id', $user_id, $total);
$ins->execute();
$order_id = $conn->insert_id;

$insItem = $conn->prepare("INSERT INTO order_items (order_id,product_id,product_name,image,quantity,price) VALUES (?,?,?,?,?,?)");
foreach ($items as $it) {
    $insItem->bind_param('iissid', $order_id, $it['product_id'], $it['product_name'], $it['image'], $it['quantity'], $it['price']);
    $insItem->execute();
}

// clear cart
$del = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
$del->bind_param('i', $user_id);
$del->execute();

// redirect to thank you
header('Location: thank_you.html');
exit;

?>
