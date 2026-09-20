<?php
// One-time bootstrap script to create the first admin account.
// Only works while zero admin accounts exist — disables itself afterwards.
require __DIR__ . '/config.php';

$stmt = $pdo->query('SELECT COUNT(*) FROM users WHERE role = "admin"');
$adminExists = (int) $stmt->fetchColumn() > 0;

$error = null;
$success = false;

if ($adminExists) {
    $error = 'An admin account already exists. This setup page is now disabled.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$name || !$email || !$password) {
        $error = 'All fields are required.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, "admin")');
        $stmt->execute([$name, $email, $hash]);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>EduTrack – Admin Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card p-4">
        <h3 class="mb-3">EduTrack – Create Admin Account</h3>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?>
          <div class="alert alert-success">Admin account created. <a href="/login.php">Log in now</a>.</div>
        <?php elseif (!$adminExists): ?>
        <form method="post">
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Create Admin</button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
</body>
</html>
