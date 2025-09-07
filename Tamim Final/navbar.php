<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$pageTitle = isset($pageTitle) ? $pageTitle : 'ShopLite';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($pageTitle) ?></title>

  <!-- Bootstrap (navbar/footer + index layout) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Page CSS (no inline) -->
  <link rel="stylesheet" href="css/base.css" />
  <?php
    // Optionally include page-specific CSS files set by each page
    if (!empty($pageCss) && is_array($pageCss)) {
      foreach ($pageCss as $href) {
        echo '<link rel="stylesheet" href="' . htmlspecialchars($href) . '">' . PHP_EOL;
      }
    }
  ?>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">ShopLite</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample" aria-controls="navbarsExample" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarsExample">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <?php if (!empty($_SESSION['user_id'])): ?>
          <span class="navbar-text me-3">Hello, @<?= htmlspecialchars($_SESSION['username'] ?? 'user') ?></span>
          <?php if (!empty($_SESSION['is_admin'])): ?>
            <li class="nav-item"><a class="nav-link" href="admin.php">Admin Dashboard</a></li>
          <?php endif; ?>
          <li class="nav-item"><a class="nav-link" href="account.php">My Account</a></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a id="accountLink" class="nav-link" href="signin.php">Sign in</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<main class="container">
</main>
