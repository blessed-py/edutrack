<?php
require __DIR__ . '/config.php';
require __DIR__ . '/includes/auth.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$name || !$email || !$password) {
        $error = 'All fields are required.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with that email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, "student")');
            $stmt->execute([$name, $email, $hash]);

            $userId = (int) $pdo->lastInsertId();
            login_user(['id' => $userId, 'name' => $name, 'email' => $email, 'role' => 'student']);
            header('Location: /dashboard.php');
            exit;
        }
    }
}

require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-5">
    <p class="mt-4 mb-2"><a href="/">&larr; EduTrack</a></p>
    <div class="card p-4">
      <h3 class="mb-3">Create your EduTrack account</h3>
      <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
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
        <button type="submit" class="btn btn-primary w-100">Register</button>
      </form>
      <p class="mt-3 mb-0">Already have an account? <a href="/login.php">Log in</a></p>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
