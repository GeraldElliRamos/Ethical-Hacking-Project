<?php
// Run this once to create sample products
include('config.php');

$create = "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(50) UNIQUE,
    name VARCHAR(255),
    image VARCHAR(255),
    price DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($create);

$items = [
    ['sku'=>'HYDBP','name'=>'Hydration Backpack','image'=>'assets/Hydro-Backpack.png','price'=>495],
    ['sku'=>'RUNJKT','name'=>'All-Weather Running Jacket','image'=>'assets/runningJacket.png','price'=>980],
    ['sku'=>'TRLSHO','name'=>'Agility Series Trail Shoes','image'=>'assets/Agility-Shoes.png','price'=>1590],
    ['sku'=>'ECLSHD','name'=>'Eclipse UV Shades','image'=>'assets/Shades.png','price'=>450],
    ['sku'=>'RESBND','name'=>'Resistance Band Set','image'=>'assets/Resistance-Bands.png','price'=>212],
    ['sku'=>'WJPROP','name'=>'Weighted Jump Rope','image'=>'assets/Jump-rope.png','price'=>130],
    ['sku'=>'CMPJRS','name'=>'Compression Jersey Set','image'=>'assets/Compression-Shirt.png','price'=>65],
    ['sku'=>'SPTSUN','name'=>'Sports Sunglasses','image'=>'assets/sportsSunglasses.png','price'=>240],
    ['sku'=>'AGLLAD','name'=>'Agility Ladder','image'=>'assets/Agility-Ladder.png','price'=>80],
    ['sku'=>'LWJKT','name'=>'Lightweight Sports Jacket','image'=>'assets/runningJacket.png','price'=>120],
    ['sku'=>'PRMYGA','name'=>'Premium Yoga Mat','image'=>'assets/Shades.png','price'=>180],
    ['sku'=>'TRNGLV','name'=>'Training Gloves','image'=>'assets/Hydro-Backpack.png','price'=>99],
    ['sku'=>'HYDSTL','name'=>'Hydro Steel Bottle','image'=>'assets/hydration.png','price'=>59]
];

$ins = $conn->prepare("INSERT INTO products (sku,name,image,price) VALUES (?,?,?,?)");
foreach ($items as $it) {
    // ignore duplicates
    $check = $conn->prepare("SELECT id FROM products WHERE sku=? LIMIT 1");
    $check->bind_param('s', $it['sku']);
    $check->execute();
    $res = $check->get_result();
    if ($res && $res->num_rows>0) continue;
    $ins->bind_param('sssd', $it['sku'], $it['name'], $it['image'], $it['price']);
    $ins->execute();
}

echo "Seeded products.";

?>
