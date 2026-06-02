<?php
session_start();
include('config.php');

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$products = [];

if ($query !== '') {
    $search = "%$query%";
    $stmt = $conn->prepare("SELECT id, name, image, price FROM products WHERE name LIKE ? OR sku LIKE ?");
    $stmt->bind_param('ss', $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results - Gear Summit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-light bg-white shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.html">Gear Summit</a>
            <form class="d-flex" action="search.php" method="GET">
                <input class="form-control me-2" type="search" name="q" value="<?php echo htmlspecialchars($query); ?>">
                <button class="btn btn-outline-primary" type="submit">Search</button>
            </form>
        </div>
    </nav>

    <div class="container">
        <h3>Search Results for "<?php echo htmlspecialchars($query); ?>"</h3>
        <hr>
        <?php if (empty($products)): ?>
            <div class="alert alert-warning">No products found matching your search.</div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($products as $p): ?>
                    <div class="col-md-3">
                        <div class="card h-100 text-center shadow-sm">
                            <img src="<?php echo htmlspecialchars($p['image']); ?>" class="card-img-top p-3" alt="...">
                            <div class="card-body">
                                <h6><?php echo htmlspecialchars($p['name']); ?></h6>
                                <p>₱<?php echo number_format($p['price'], 2); ?></p>
                                <button class="btn btn-sm btn-primary add-to-cart" data-id="<?php echo $p['id']; ?>">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', function() {
            const pid = this.getAttribute('data-id');
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ product_id: pid, quantity: 1 })
            }).then(r => r.json()).then(data => {
                if(data.success) alert('Added to cart!');
                else alert(data.message);
            });
        });
    });
    </script>
</body>
</html>