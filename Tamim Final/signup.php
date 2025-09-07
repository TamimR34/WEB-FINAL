<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// --- DB connect ---
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = mysqli_connect('sql100.infinityfree.com', 'if0_39788671', 'TamimAlasmar04', 'if0_39788671_shoplite');
if (!$conn) { die('Database connection failed: ' . mysqli_connect_error()); }
mysqli_set_charset($conn, 'utf8mb4');

$err = '';
$ok  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $email    = trim($_POST['email'] ?? '');
  $pass     = $_POST['password'] ?? '';

  if ($username === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6) {
    $err = 'Please enter a valid username, email, and a password with at least 6 characters.';
  } else {
    // unique email/username check
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param('ss', $email, $username);
    $stmt->execute(); $stmt->store_result();
    if ($stmt->num_rows > 0) {
      $err = 'This email or username is already registered.';
    } else {
      $hash = password_hash($pass, PASSWORD_BCRYPT);
      $stmtIns = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
      $stmtIns->bind_param('sss', $username, $email, $hash);
      $stmtIns->execute(); $stmtIns->close();
      $ok = 'Account created. You can now sign in.';
    }
    $stmt->close();
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ShopLite — Sign up</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="css/base.css"/>
  <link rel="stylesheet" href="css/signup.css"/>
</head>
<body>
<?php include 'navbar.php'; ?>

<main class="d-flex align-items-center justify-content-center vh-100">
  <div class="card shadow-sm p-4" style="width:400px; max-width:90%;">
    <h1 class="h5 mb-3 text-center">Create your account</h1>
    <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>
    <?php if ($ok):  ?><div class="alert alert-success"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
    <form method="post" class="form-stack" novalidate>
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input name="username" type="text" class="form-control" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input name="email" type="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input name="password" type="password" class="form-control" minlength="6" required>
        <div class="form-text">At least 6 characters.</div>
      </div>
      <button class="btn btn-primary w-100" type="submit">Sign up</button>
    </form>
    <hr class="my-4" />
    <div class="text-center">
      Already have an account? <a href="signin.php">Sign in</a>
    </div>
  </div>
</main>

<?php mysqli_close($conn); include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>