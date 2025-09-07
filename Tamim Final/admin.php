<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Auth guard
if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
  header('Location: signin.php?next=' . urlencode('admin.php'));
  exit;
}

// DB connect
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = mysqli_connect('sql100.infinityfree.com', 'if0_39788671', 'TamimAlasmar04', 'if0_39788671_shoplite');
if (!$conn) { die('Database connection failed: ' . mysqli_connect_error()); }
mysqli_set_charset($conn, 'utf8mb4');

$info = '';
$error = '';

// Handle add product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
  $title = trim($_POST['title'] ?? '');
  $desc  = trim($_POST['description'] ?? '');
  $price = trim($_POST['price'] ?? '');
  $img   = trim($_POST['image_url'] ?? '');

  if ($title === '' || !is_numeric($price)) {
    $error = 'Please provide a product title and a numeric price.';
  } else {
    // cast/format price safely
    $priceVal = number_format((float)$price, 2, '.', '');
    $stmt = $conn->prepare("INSERT INTO products (title, description, price, image_url) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssds', $title, $desc, $priceVal, $img);
    $stmt->execute();
    $stmt->close();
    $info = 'Product added.';
  }
}

// Handle delete product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
  $pid = (int)($_POST['product_id'] ?? 0);
  if ($pid > 0) {
    // Clean up cart rows referencing this product (FK with ON DELETE CASCADE would also handle this)
    $stmt = $conn->prepare("DELETE FROM cart WHERE product_id = ?");
    $stmt->bind_param('i', $pid);
    $stmt->execute();
    $stmt->close();

    $stmt2 = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt2->bind_param('i', $pid);
    $stmt2->execute();
    $stmt2->close();
    $info = 'Product removed.';
  } else {
    $error = 'Invalid product id.';
  }
}

// Load products
$res = $conn->query("SELECT id, title, price, image_url FROM products ORDER BY id DESC");
$products = $res->fetch_all(MYSQLI_ASSOC);
$res->close();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ShopLite — Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="css/base.css"/>
</head>
<body>
<?php include 'navbar.php'; ?>

<main class="container">
  <h1 class="h4 mb-3">Admin Dashboard</h1>

  <?php if ($info): ?><div class="alert alert-success"><?= htmlspecialchars($info) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <!-- Add product -->
  <div class="card-flat mb-4">
    <div class="fw-semibold mb-2">Add Product</div>
    <form method="post" class="form-grid">
      <input type="hidden" name="action" value="add">
      <div class="form-half">
        <label class="form-label">Title</label>
        <input name="title" type="text" class="form-control" required>
      </div>
      <div class="form-half">
        <label class="form-label">Price (e.g., 19.99)</label>
        <input name="price" type="text" class="form-control" required>
      </div>
      <div class="full">
        <label class="form-label">Image URL</label>
        <input name="image_url" type="url" class="form-control" placeholder="https://...">
      </div>
      <div class="full">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3"></textarea>
      </div>
      <div class="full">
        <button class="btn btn-primary">Add Product</button>
      </div>
    </form>
  </div>

  <!-- List products -->
  <div class="card-flat">
    <div class="fw-semibold mb-3">Products</div>
    <?php if (!$products): ?>
      <div class="alert alert-info">No products yet.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th style="width:60px">ID</th>
              <th>Title</th>
              <th style="width:120px">Price</th>
              <th>Image</th>
              <th style="width:120px">Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($products as $p): ?>
            <tr>
              <td><?= (int)$p['id'] ?></td>
              <td><?= htmlspecialchars($p['title']) ?></td>
              <td>$<?= number_format((float)$p['price'], 2) ?></td>
              <td>
                <?php if (!empty($p['image_url'])): ?>
                  <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="" style="height:48px; width:48px; object-fit:cover; border-radius:.25rem;">
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif; ?>
              </td>
              <td>
                <form method="post" onsubmit="return confirm('Delete this product?');">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php mysqli_close($conn); include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>