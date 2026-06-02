<?php
session_start();
header('Content-Type: application/json');
include('config.php');

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    http_response_code(400);
    exit;
}

// Fetch product info from DB to prevent price manipulation
$pStmt = $conn->prepare("SELECT name, image, price FROM products WHERE id = ? LIMIT 1");
$pStmt->bind_param('i', $product_id);
$pStmt->execute();
$pRes = $pStmt->get_result();
$product = $pRes->fetch_assoc();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

$product_name = $product['name'];
$image = $product['image'];
$price = $product['price'];

// If user is logged in, persist to DB; otherwise keep in session (guest cart)
if (isset($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];
    // ensure cart_items table exists
    $createSql = "CREATE TABLE IF NOT EXISTS cart_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        product_name VARCHAR(255) DEFAULT NULL,
        image VARCHAR(255) DEFAULT NULL,
        quantity INT NOT NULL DEFAULT 1,
        price DECIMAL(10,2) DEFAULT NULL,
        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY ux_user_product (user_id, product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conn->query($createSql);

    // try update existing
    $stmt = $conn->prepare("SELECT quantity FROM cart_items WHERE user_id = ? AND product_id = ? LIMIT 1");
    $stmt->bind_param('ii', $user_id, $product_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $row = $res->fetch_assoc()) {
        $newQty = $row['quantity'] + $quantity;
        $up = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $up->bind_param('iii', $newQty, $user_id, $product_id);
        $ok = $up->execute();
        if ($ok) {
            echo json_encode(['success' => true, 'message' => 'Cart updated', 'quantity' => $newQty]);
            exit;
        }
    } else {
        $ins = $conn->prepare("INSERT INTO cart_items (user_id, product_id, product_name, image, quantity, price) VALUES (?,?,?,?,?,?)");
        $ins->bind_param('iissid', $user_id, $product_id, $product_name, $image, $quantity, $price);
        if ($ins->execute()) {
            echo json_encode(['success' => true, 'message' => 'Added to cart', 'quantity' => $quantity]);
            exit;
        }
    }

    echo json_encode(['success' => false, 'message' => 'Could not add to cart']);
    http_response_code(500);
    exit;

} else {
    // guest cart in session
    if (!isset($_SESSION['guest_cart']) || !is_array($_SESSION['guest_cart'])) $_SESSION['guest_cart'] = [];
    $key = (string)$product_id;
    if (isset($_SESSION['guest_cart'][$key])) {
        $_SESSION['guest_cart'][$key]['quantity'] += $quantity;
    } else {
        $_SESSION['guest_cart'][$key] = [
            'product_id' => $product_id,
            'product_name' => $product_name,
            'image' => $image,
            'quantity' => $quantity,
            'price' => $price
        ];
    }
    echo json_encode(['success' => true, 'message' => 'Added to guest cart', 'quantity' => $_SESSION['guest_cart'][$key]['quantity']]);
    exit;
}

?>
