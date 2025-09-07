<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// --- DB connect ---
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // dev: show SQL errors
$conn = mysqli_connect('sql100.infinityfree.com', 'if0_39788671', 'TamimAlasmar04', 'if0_39788671_shoplite');
if (!$conn) { die('Database connection failed: ' . mysqli_connect_error()); }
mysqli_set_charset($conn, 'utf8mb4');

$err  = '';
$next = isset($_GET['next']) ? $_GET['next'] : '';

// optional: prevent open-redirects (only allow local relative paths)
if ($next && (stripos($next, '://') !== false || str_starts_with($next, '//'))) {
  $next = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';
  $next  = $_POST['next'] ?? '';

  // same open-redirect guard on POST
  if ($next && (stripos($next, '://') !== false || str_starts_with($next, '//'))) {
    $next = '';
  }

  // schema: users(id, username, email, password, is_admin)
  $stmt = $conn->prepare("SELECT id, username, password, is_admin FROM users WHERE email = ?");
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $res = $stmt->get_result();
  $user = $res->fetch_assoc();
  $stmt->close();

  if ($user && password_verify($pass, $user['password'])) {
    session_regenerate_id(true);
    $_SESSION['user_id']  = (int)$user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['is_admin'] = (int)($user['is_admin'] ?? 0);
    mysqli_close($conn);
    header('Location: ' . ($next ? $next : 'index.php'));
    exit;
  } else {
    $err = 'Invalid email or password.';
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ShopLite — Sign in</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="css/base.css"/>
  <!-- optional: create css/signin.css or remove this line if you don't have it -->
  <link rel="stylesheet" href="css/signin.css"/>
</head>
<body>
<?php include 'navbar.php'; ?>

<main class="d-flex align-items-center justify-content-center vh-100">
  <div class="card shadow-sm p-4" style="width:400px; max-width:90%;">
    <h1 class="h5 mb-3 text-center">Sign in</h1>
    <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>
    <form method="post" class="form-stack" novalidate>
      <input type="hidden" name="next" value="<?= htmlspecialchars($next) ?>">
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input name="email" type="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input name="password" type="password" class="form-control" required>
      </div>
      <button class="btn btn-primary w-100" type="submit">Sign in</button>
    </form>
    <hr class="my-4" />
    <div class="text-center">
      New here? <a href="signup.php">Create an account</a>
    </div>
  </div>
</main>

<?php mysqli_close($conn); include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>