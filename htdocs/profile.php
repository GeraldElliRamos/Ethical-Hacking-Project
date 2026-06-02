<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, username, email, full_name, phone_number, address, created_at FROM users WHERE id = ? LIMIT 1");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $userId);
if (!$stmt->execute()) {
    die("Select failed: " . $stmt->error);
}

$result = $stmt->get_result();
$user = $result ? $result->fetch_assoc() : null;

if (!$user) {
    die('Profile not found.');
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.html');
    exit;
}
// prepare display name and initials for avatar
$displayName = $user['full_name'] ?: $user['username'];
$parts = preg_split('/\s+/', trim($displayName));
$initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
// get cart count for this user
$cartCount = 0;
if (isset($conn) && $userId > 0) {
  $cstmt = $conn->prepare("SELECT SUM(quantity) as cnt FROM cart_items WHERE user_id = ?");
  if ($cstmt) {
    $cstmt->bind_param('i', $userId);
    $cstmt->execute();
    $cres = $cstmt->get_result();
    if ($cres && $crow = $cres->fetch_assoc()) {
      $cartCount = (int)$crow['cnt'];
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Profile - Gear Summit</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    body { background: linear-gradient(135deg,#f8fbff 0%, #ffffff 100%); min-height: 100vh; }
    .card { border: 0; transition: transform .18s ease, box-shadow .18s ease; }
    .card:hover { transform: translateY(-6px); box-shadow: 0 1.5rem 3rem rgba(16,24,40,.12); }
    .profile-hero { background: linear-gradient(180deg,#ffffffcc,#f6f9ff); border-radius: .75rem; }
    .avatar { width:96px; height:96px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; color:#fff; background:linear-gradient(45deg,#6c5ce7,#00b4d8); font-size:28px; box-shadow:0 10px 20px rgba(14,30,37,.08); }
    .meta-label { font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; color:#6c757d; }
    .fw-semibold { color:#212529; font-weight:600; }
    .detail-icon { width:44px; height:44px; border-radius:8px; display:flex; align-items:center; justify-content:center; background:#f1f5f9; color:#0d6efd; font-size:18px; }
    .detail-box { display:flex; gap:12px; align-items:center; }
    .action-btns .btn { min-width:110px; }
    footer small { color:#6c757d; }
    @media (max-width: 575.98px) { .avatar { width:72px; height:72px; font-size:22px; } }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-2 px-4">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center" href="index.html">
        <img src="./assets/Logo.png" alt="Gear Summit Logo" height="35" class="me-2">
        <span class="fw-bold">Gear Summit</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="mainNavbar">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="Product.html">Products</a></li>
          <li class="nav-item"><a class="nav-link" href="contact.html">Contact</a></li>
        </ul>
        <form class="d-flex me-3" role="search" action="search.php" method="get">
          <input class="form-control form-control-sm me-2" type="search" name="q" placeholder="Search" aria-label="Search">
          <button class="btn btn-sm btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
        </form>
        <div class="d-flex align-items-center gap-2">
          <a href="cartpage.php" class="btn btn-sm btn-outline-primary position-relative">
            <i class="bi bi-cart-fill"></i>
            <span id="cartBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?php echo $cartCount; ?></span>
          </a>
          <div class="dropdown">
            <a class="btn btn-sm btn-light dropdown-toggle d-flex align-items-center" href="#" role="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
              <span class="me-2 avatar" style="width:28px;height:28px;font-size:12px;padding:4px;line-height:1;"><?php echo htmlspecialchars($initials); ?></span>
              <span class="d-none d-sm-inline"><?php echo htmlspecialchars($user['username']); ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
              <li><a class="dropdown-item" href="profile.php">Profile</a></li>
              <li><a class="dropdown-item" href="edit_profile.php">Edit profile</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="profile.php?logout=1">Logout</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <main class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
          <div class="card-body p-4 p-md-5 profile-hero">
            <div class="d-flex flex-column flex-md-row align-items-center gap-3 mb-4">
              <div class="d-flex align-items-center gap-3">
                <div class="avatar" aria-hidden="true"><?php echo htmlspecialchars($initials); ?></div>
                <div>
                  <p class="meta-label mb-1">Account</p>
                  <h1 class="h3 fw-bold mb-0">Hi, <?php echo htmlspecialchars($user['username']); ?></h1>
                  <div class="text-muted small"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
              </div>
              <div class="ms-md-auto text-md-end action-btns mt-3 mt-md-0">
                <p class="meta-label mb-1">Member since</p>
                <div class="fw-semibold mb-2"><?php echo htmlspecialchars($user['created_at']); ?></div>
                <a href="edit_profile.php" class="btn btn-sm btn-primary me-2">Edit Profile</a>
                <a href="profile.php?logout=1" class="btn btn-outline-dark btn-sm">Logout</a>
              </div>
            </div>

            <div class="row g-3">
              <div class="col-md-6">
                <div class="border rounded-3 p-3 bg-white">
                  <div class="detail-box">
                    <div class="detail-icon"><i class="bi bi-person-fill"></i></div>
                    <div>
                      <div class="meta-label mb-1">Username</div>
                      <div class="fw-semibold"><?php echo htmlspecialchars($user['username']); ?></div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="border rounded-3 p-3 bg-white">
                  <div class="detail-box">
                    <div class="detail-icon"><i class="bi bi-envelope-fill"></i></div>
                    <div>
                      <div class="meta-label mb-1">Email</div>
                      <div class="fw-semibold"><?php echo htmlspecialchars($user['email']); ?></div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="border rounded-3 p-3 bg-white">
                  <div class="detail-box">
                    <div class="detail-icon"><i class="bi bi-person-badge-fill"></i></div>
                    <div>
                      <div class="meta-label mb-1">Full Name</div>
                      <div class="fw-semibold"><?php echo htmlspecialchars($user['full_name'] ?: 'Not set'); ?></div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="border rounded-3 p-3 bg-white">
                  <div class="detail-box">
                    <div class="detail-icon"><i class="bi bi-phone-fill"></i></div>
                    <div>
                      <div class="meta-label mb-1">Phone Number</div>
                      <div class="fw-semibold"><?php echo htmlspecialchars($user['phone_number'] ?: 'Not set'); ?></div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-12">
                <div class="border rounded-3 p-3 bg-white">
                  <div class="detail-box">
                    <div class="detail-icon"><i class="bi bi-house-fill"></i></div>
                    <div>
                      <div class="meta-label mb-1">Address</div>
                      <div class="fw-semibold"><?php echo htmlspecialchars($user['address'] ?: 'Not set'); ?></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <footer class="mt-3 text-center">
          <small>© <?php echo date('Y'); ?> Gear Summit. All rights reserved.</small>
        </footer>
      </div>
    </div>
  </main>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
