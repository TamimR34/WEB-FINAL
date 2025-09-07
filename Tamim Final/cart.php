<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['user_id'])) {
  header('Location: signin.php?next=' . urlencode('cart.php'));
  exit;
}
$userId = (int)$_SESSION['user_id'];

// --- DB connect ---
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = mysqli_connect('sql100.infinityfree.com', 'if0_39788671', 'TamimAlasmar04', 'if0_39788671_shoplite');
if (!$conn) { die('Database connection failed: ' . mysqli_connect_error()); }
mysqli_set_charset($conn, 'utf8mb4');

// Handle updates (quantity changes / remove)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // 1) Single-item remove via "Remove" button
  if (isset($_POST['remove'])) {
    $pid = (int)$_POST['remove'];
    if ($pid > 0) {
      $stmt = $conn->prepare("DELETE FROM cart WHERE user_id=? AND product_id=?");
      $stmt->bind_param('ii', $userId, $pid);
      $stmt->execute();
      $stmt->close();
    }
    mysqli_close($conn);
    header('Location: cart.php');
    exit;
  }

  // 2) Bulk quantity update
  if (isset($_POST['qty']) && is_array($_POST['qty'])) {
    foreach ($_POST['qty'] as $pid => $q) {
      $pid = (int)$pid; 
      $q = max(0, (int)$q);
      if ($pid <= 0) { continue; }

      if ($q === 0) {
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id=? AND product_id=?");
        $stmt->bind_param('ii', $userId, $pid);
        $stmt->execute();
        $stmt->close();
      } else {
        $stmt = $conn->prepare("UPDATE cart SET quantity=? WHERE user_id=? AND product_id=?");
        $stmt->bind_param('iii', $q, $userId, $pid);
        $stmt->execute();
        $stmt->close();
      }
    }
    mysqli_close($conn);
    header('Location: cart.php');
    exit;
  }
}

// Fetch items
$stmt = $conn->prepare("
  SELECT p.id, p.title, p.price, p.image_url, c.quantity
  FROM cart c
  JOIN products p ON p.id = c.product_id
  WHERE c.user_id = ?
  ORDER BY p.title ASC
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total = 0.0;
foreach ($items as $it) { $total += $it['price'] * $it['quantity']; }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ShopLite — Cart</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="css/base.css"/>
  <link rel="stylesheet" href="css/cart.css"/>
</head>
<body>
<?php include 'navbar.php'; ?>

<main class="container">
  <h1 class="h4 mb-3">Your Cart</h1>

  <?php if (!$items): ?>
    <div class="alert alert-info">Your cart is empty.</div>
    <a href="index.php" class="btn btn-outline-secondary">Continue Shopping</a>
  <?php else: ?>
    <form method="post">
      <div class="cart-list" id="cartContainer">
        <?php foreach ($items as $it): ?>
          <div class="cart-item">
            <img class="cart-thumb" src="<?= htmlspecialchars($it['image_url']) ?>" alt="<?= htmlspecialchars($it['title']) ?>">
            <div class="flex-1">
              <div class="fw-semibold"><?= htmlspecialchars($it['title']) ?></div>
              <div class="text-muted small">$<?= number_format($it['price'], 2) ?> each</div>
            </div>
            <input class="form-control qty-input" type="number" min="0" name="qty[<?= (int)$it['id'] ?>]" value="<?= (int)$it['quantity'] ?>">
            <button class="btn btn-sm btn-outline-danger" type="submit" name="remove" value="<?= (int)$it['id'] ?>">Remove</button>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="d-flex justify-content-between align-items-center gap-2 mt-3">
        <a href="index.php" class="btn btn-outline-secondary">Continue Shopping</a>
        <div class="d-flex gap-3 align-items-center">
          <div class="fw-semibold fs-5">Total: $<?= number_format($total, 2) ?></div>
          <button class="btn btn-outline-primary">Update Cart</button>
          <button class="btn btn-primary" type="button" disabled>Checkout (demo)</button>
        </div>
      </div>
    </form>
  <?php endif; ?>
</main>

<?php mysqli_close($conn); include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>