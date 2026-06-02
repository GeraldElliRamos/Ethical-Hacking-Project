<?php
session_start();
include('config.php');

// support guest or logged-in cart
$items = [];
if (isset($_SESSION['user_id'])) {
  $user_id = (int)$_SESSION['user_id'];
  $stmt = $conn->prepare("SELECT product_id, product_name, image, quantity, price FROM cart_items WHERE user_id = ? ORDER BY added_at DESC");
  if ($stmt) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) $items[] = $row;
  }
} else {
  if (isset($_SESSION['guest_cart']) && is_array($_SESSION['guest_cart'])) {
    foreach ($_SESSION['guest_cart'] as $entry) $items[] = $entry;
  }
}

$totalQty = 0;
foreach ($items as $it) $totalQty += $it['quantity'];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Your Cart</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    .item-image { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
    .quantity-control button { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 4px; border: 1px solid #dee2e6; background: #fff; }
    .cart-item { background: #fff; border-radius: 12px; padding: 1.5rem; border: 1px solid #edf2f7; transition: all 0.2s ease; }
    .cart-item:hover { border-color: #cbd5e0; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .sticky-summary { position: sticky; top: 2rem; }
    .order-summary { background: #fff; border-radius: 12px; padding: 1.5rem; }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-3 px-4 mb-4">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center" href="index.html">
        <img src="./assets/Logo.png" alt="Logo" height="32" class="me-2">
        <span class="fw-bold">Gear Summit</span>
      </a>
      <div class="d-flex ms-auto align-items-center gap-3">
        <a href="Apparel.html" class="text-dark">Shop</a>
        <a href="profile.php" class="text-dark">Account</a>
        <a href="cartpage.php" class="btn btn-sm btn-outline-primary position-relative">
          <i class="bi bi-cart-fill"></i>
          <span id="cartBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?php echo $totalQty; ?></span>
        </a>
      </div>
    </div>
  </nav>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-end mb-4">
      <h1 class="h2 fw-bold mb-0">Shopping Cart</h1>
      <a href="Apparel.html" class="text-decoration-none text-muted small"><i class="fa fa-arrow-left me-1"></i> Continue Shopping</a>
    </div>

    <div id="emptyCart" class="alert alert-info" style="<?php echo empty($items) ? '' : 'display:none;'; ?>">
      Your cart is empty. <a href="Apparel.html">Continue shopping</a>
    </div>

    <?php if (!empty($items)): ?>
      <div class="row g-4">
        <!-- Left Side: Items -->
        <div class="col-lg-8">
          <div id="cartItemsContainer">
            <?php foreach ($items as $it): ?>
              <div class="cart-item row align-items-center mb-3 g-0" data-id="<?php echo htmlspecialchars($it['product_id']); ?>">
                <div class="col-auto me-3">
                  <img src="<?php echo htmlspecialchars($it['image'] ?: 'assets/placeholder.png'); ?>" class="item-image" alt="Product">
                </div>
                <div class="col">
                  <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($it['product_name'] ?: 'Product'); ?></h5>
                  <button class="remove-item btn btn-link text-danger p-0 small text-decoration-none">Remove</button>
                </div>
                <div class="col-auto text-end">
                  <p class="fw-bold mb-2">₱<span class="price-value"><?php echo htmlspecialchars($it['price']); ?></span></p>
                  <div class="quantity-control d-flex align-items-center gap-2">
                    <button class="quantity-decrease"><i class="fa fa-minus small"></i></button>
                    <span class="quantity-value fw-semibold mx-1"><?php echo htmlspecialchars($it['quantity']); ?></span>
                    <button class="quantity-increase"><i class="fa fa-plus small"></i></button>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Right Side: Summary -->
        <div class="col-lg-4">
          <div class="order-summary sticky-summary shadow-sm">
            <h4 class="fw-bold mb-4">Order Summary</h4>
            <div class="summary-item d-flex justify-content-between mb-2">
              <span class="text-muted">Subtotal</span> <span>₱<span id="subtotal">0</span></span>
            </div>
            <div class="summary-item d-flex justify-content-between mb-2 text-success">
              <span>Discount (<span id="discountPercent">0</span>%)</span> <span>-₱<span id="discountAmount">0</span></span>
            </div>
            <div class="summary-item d-flex justify-content-between mb-3">
              <span class="text-muted">Delivery Fee</span> <span>₱<span id="deliveryFee">150</span></span>
            </div>
            <hr>
            <div class="summary-item d-flex justify-content-between fw-bold h5 mb-4">
              <span>Total</span> <span>₱<span id="totalAmount">0</span></span>
            </div>
            
            <label for="promoInput" class="form-label small text-muted">Have a promo code?</label>
            <div class="input-group mb-4">
              <input type="text" id="promoInput" class="form-control form-control-sm" placeholder="e.g. SAVE20">
              <button class="btn btn-outline-dark btn-sm" type="button" id="applyPromo">Apply</button>
            </div>
            
            <a href="checkout.php" id="checkoutBtn" class="btn btn-primary w-100 py-2 fw-bold">Proceed to Checkout</a>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Toasts for user feedback -->
  <div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="cartToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body">Cart updated successfully!</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
    <div id="promoToast" class="toast align-items-center text-white bg-info border-0" role="alert">
      <div class="d-flex">
        <div class="toast-body">Promo code applied!</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
    <div id="errorToast" class="toast align-items-center text-white bg-danger border-0" role="alert">
      <div class="d-flex">
        <div class="toast-body">Invalid promo code.</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="cartpage.js"></script>
</body>
</html>
