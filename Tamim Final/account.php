<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['user_id'])) {
  header('Location: signin.php?next=' . urlencode('account.php'));
  exit;
}
$userId = (int)$_SESSION['user_id'];

// --- DB connect ---
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = mysqli_connect('sql100.infinityfree.com', 'if0_39788671', 'TamimAlasmar04', 'if0_39788671_shoplite');
if (!$conn) { die('Database connection failed: ' . mysqli_connect_error()); }
mysqli_set_charset($conn, 'utf8mb4');

$info = '';
$error = '';

// Update profile username
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
  $username = trim($_POST['username'] ?? '');
  if ($username === '') { $error = 'Username cannot be empty.'; }
  else {
    // Ensure username is unique (except self)
    $stmt = $conn->prepare("SELECT id FROM users WHERE username=? AND id<>?");
    $stmt->bind_param('si', $username, $userId);
    $stmt->execute(); $stmt->store_result();
    if ($stmt->num_rows > 0) {
      $error = 'That username is taken.';
    } else {
      $stmtUp = $conn->prepare("UPDATE users SET username=? WHERE id=?");
      $stmtUp->bind_param('si', $username, $userId);
      $stmtUp->execute(); $stmtUp->close();
      $_SESSION['username'] = $username;
      $info = 'Profile updated.';
    }
    $stmt->close();
  }
}

// Change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
  $new = $_POST['new_password'] ?? '';
  $con = $_POST['confirm_password'] ?? '';
  if (strlen($new) < 6) { $error = 'Password must be at least 6 characters.'; }
  elseif ($new !== $con) { $error = 'Passwords do not match.'; }
  else {
    $hash = password_hash($new, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
    $stmt->bind_param('si', $hash, $userId);
    $stmt->execute(); $stmt->close();
    $info = 'Password changed.';
  }
}

// Delete account
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
  $stmt = $conn->prepare("DELETE FROM cart WHERE user_id=?");
  $stmt->bind_param('i', $userId);
  $stmt->execute(); $stmt->close();

  $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
  $stmt->bind_param('i', $userId);
  $stmt->execute(); $stmt->close();

  mysqli_close($conn);
  session_unset(); session_destroy();
  header('Location: signin.php'); exit;
}

// Load user
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id=?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ShopLite — My Account</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="css/base.css"/>
  <link rel="stylesheet" href="css/account.css"/>
</head>
<body>
<?php include 'navbar.php'; ?>

<main class="container">
  <h1 class="h4 mb-3">My Account</h1>

  <?php if ($info): ?><div class="alert alert-success"><?= htmlspecialchars($info) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <section class="account-wrap">
    <div class="card-flat account-panel">
      <div class="fw-semibold mb-2">Profile</div>
      <form method="post" class="form-stack" novalidate>
        <div>
          <label class="form-label">Username</label>
          <input name="username" type="text" class="form-control" required value="<?= htmlspecialchars($user['username'] ?? '') ?>">
        </div>
        <div>
          <label class="form-label">Email (read-only)</label>
          <input type="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled>
        </div>
        <button class="btn btn-primary" name="update_profile" value="1">Save changes</button>
      </form>
    </div>

    <div class="card-flat account-panel">
      <div class="fw-semibold mb-2">Change password</div>
      <form method="post" class="form-stack" novalidate>
        <div>
          <label class="form-label">New password</label>
          <input name="new_password" type="password" class="form-control" minlength="6" required>
        </div>
        <div>
          <label class="form-label">Confirm new password</label>
          <input name="confirm_password" type="password" class="form-control" minlength="6" required>
        </div>
        <button class="btn btn-primary" name="change_password" value="1">Update password</button>
      </form>
    </div>
  </section>

  <div class="card-flat mt-3 danger-outline">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <div class="fw-semibold text-danger">Delete my account</div>
        <div class="text-muted small">This permanently removes your account and cart.</div>
      </div>
      <form method="post" onsubmit="return confirm('Are you sure? This cannot be undone.');">
        <button class="btn btn-outline-danger" name="delete_account" value="1">Delete account</button>
      </form>
    </div>
  </div>
</main>

<?php mysqli_close($conn); include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>