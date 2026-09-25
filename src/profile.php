<?php
require __DIR__ . '/config.php';
require __DIR__ . '/includes/auth.php';

$user = require_login();

$stmt = $pdo->prepare('SELECT name, email, role, created_at FROM users WHERE id = ?');
$stmt->execute([$user['id']]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'change_password') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
    $stmt->execute([$user['id']]);
    $hash = $stmt->fetchColumn();

    if (!password_verify($current, $hash)) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($new) < 8) {
        $error = 'New password must be at least 8 characters.';
    } elseif ($new !== $confirm) {
        $error = 'New passwords do not match.';
    } else {
        $newHash = password_hash($new, PASSWORD_DEFAULT);
        $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$newHash, $user['id']]);
        $success = 'Password updated.';
    }
}

require __DIR__ . '/includes/header.php';
?>
<h2 class="mb-4">My Profile</h2>

<div class="row g-4">
  <div class="col-lg-5">
    <div class="card p-4">
      <div class="d-flex align-items-center gap-3">
        <div class="avatar avatar-lg"><?= htmlspecialchars($initials) ?></div>
        <div>
          <div class="fw-semibold"><?= htmlspecialchars($profile['name']) ?></div>
          <div class="text-muted small"><?= htmlspecialchars(ucfirst($profile['role'])) ?></div>
        </div>
      </div>
      <dl class="profile-info">
        <dt>Email</dt>
        <dd><?= htmlspecialchars($profile['email']) ?></dd>
        <dt>Member since</dt>
        <dd><?= htmlspecialchars(date('F j, Y', strtotime($profile['created_at']))) ?></dd>
      </dl>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card p-4">
      <h5 class="mb-3">Change password</h5>
      <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
      <form method="post">
        <input type="hidden" name="action" value="change_password">
        <div class="mb-3">
          <label class="form-label">Current password</label>
          <input type="password" name="current_password" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">New password</label>
          <input type="password" name="new_password" class="form-control" required minlength="8">
        </div>
        <div class="mb-3">
          <label class="form-label">Confirm new password</label>
          <input type="password" name="confirm_password" class="form-control" required minlength="8">
        </div>
        <button type="submit" class="btn btn-primary">Update password</button>
      </form>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
