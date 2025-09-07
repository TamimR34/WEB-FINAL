<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// --- DB connect (page-local) ---
$conn = mysqli_connect('sql100.infinityfree.com', 'if0_39788671', 'TamimAlasmar04', 'if0_39788671_shoplite');
if (!$conn) { die('Database connection failed: ' . mysqli_connect_error()); }
mysqli_set_charset($conn, 'utf8mb4');

// Fetch products
$stmt = $conn->prepare("SELECT id, title, price, image_url FROM products ORDER BY id DESC");
$stmt->execute();
$result = $stmt->get_result();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ShopLite — Home</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="css/base.css"/>
  <link rel="stylesheet" href="css/index.css"/>
</head>
<body>
<?php include 'navbar.php'; ?>

<main class="container">
  <div class="p-4 p-md-5 mb-4 bg-light rounded-3">
    <div class="container-fluid py-4">
      <h1 class="display-6 fw-semibold">Welcome to ShopLite</h1>
      <p class="lead mb-0">Browse products, add to cart, and manage your account.</p>
    </div>
  </div>

  <h2 class="h4 mb-3">Products</h2>
  <div class="row g-3" id="productGrid">
    <?php while ($p = $result->fetch_assoc()): ?>
      <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="card h-100">
          <img class="card-img-top" src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
          <div class="card-body d-flex flex-column">
            <h3 class="h6 card-title mb-1"><?= htmlspecialchars($p['title']) ?></h3>
            <div class="mb-2 fw-semibold">$<?= number_format((float)$p['price'], 2) ?></div>
            <a class="btn btn-primary mt-auto" href="product.php?id=<?= (int)$p['id'] ?>">View</a>
          </div>
        </div>
      </div>
    <?php endwhile; $stmt->close(); $conn->close(); ?>
  </div>
</main>

<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
