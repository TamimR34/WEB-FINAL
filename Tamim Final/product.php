<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// --- DB connect ---
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // dev: show SQL errors
$conn = mysqli_connect('sql100.infinityfree.com', 'if0_39788671', 'TamimAlasmar04', 'if0_39788671_shoplite');
if (!$conn) { die('Database connection failed: ' . mysqli_connect_error()); }
mysqli_set_charset($conn, 'utf8mb4');

// Validate id
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($productId <= 0) { http_response_code(404); $product = null; }
else {
  // Handle add-to-cart
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (empty($_SESSION['user_id'])) {
      mysqli_close($conn);
      $next = 'product.php?id=' . $productId;
      header('Location: signin.php?next=' . urlencode($next));
      exit;
    }
    $qty = max(1, (int)($_POST['qty'] ?? 1));
    $userId = (int)$_SESSION['user_id'];

    // Requires UNIQUE (user_id, product_id) on cart
    $sql = "INSERT INTO cart (user_id, product_id, quantity)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iii', $userId, $productId, $qty);
    $stmt->execute();
    $stmt->close();

    mysqli_close($conn);
    header('Location: cart.php');
    exit;
  }

  // Fetch product (match schema)
  $stmt = $conn->prepare("SELECT id, title, description, price, image_url FROM products WHERE id = ?");
  $stmt->bind_param('i', $productId);
  $stmt->execute();
  $product = $stmt->get_result()->fetch_assoc();
  $stmt->close();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= $product ? 'ShopLite — ' . htmlspecialchars($product['title']) : 'Product not found' ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="css/base.css"/>
  <link rel="stylesheet" href="css/product.css"/>
</head>
<body>
<?php include 'navbar.php'; ?>

<main class="container">
  <?php if (!$product): ?>
    <div class="alert alert-danger">Product not found.</div>
  <?php else: ?>
    <div class="product-flex">
      <div class="product-media card-flat">
        <img class="product-img" src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['title']) ?>">
      </div>
      <div class="product-details card-flat d-flex flex-column gap-2">
        <h1 class="h5 m-0"><?= htmlspecialchars($product['title']) ?></h1>
        <div class="fs-5 fw-semibold">$<?= number_format((float)$product['price'], 2) ?></div>
        <p class="text-muted"><?= nl2br(htmlspecialchars($product['description'])) ?></p>

        <form method="post" class="d-flex gap-2 align-items-center" action="product.php?id=<?= (int)$product['id'] ?>">
          <input type="hidden" name="add_to_cart" value="1">
          <input name="qty" type="number" min="1" value="1" class="form-control qty-input">
          <button class="btn btn-primary">Add to Cart</button>
        </form>
      </div>
    </div>
  <?php endif; ?>
</main>

<?php mysqli_close($conn); include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>