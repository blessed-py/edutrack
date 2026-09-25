<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../includes/auth.php';

$admin = require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_student') {
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ? AND role = "student"');
    $stmt->execute([$_POST['user_id']]);
    header('Location: /admin/index.php');
    exit;
}

$students = $pdo->query(
    "SELECT u.id, u.name, u.email, u.created_at,
            (SELECT COUNT(*) FROM courses c WHERE c.user_id = u.id) AS course_count
     FROM users u WHERE u.role = 'student' ORDER BY u.created_at DESC"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin – EduTrack</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="/assets/css/landing.css?v=<?= @filemtime(__DIR__ . '/../assets/css/landing.css') ?: time() ?>" rel="stylesheet">
</head>
<body>

<header class="l-nav">
  <div class="l-nav-inner">
    <a class="l-brand" href="/dashboard.php"><span class="l-brand-mark"></span>EduTrack <span class="l-accent-text">Admin</span></a>
    <div class="l-nav-actions">
      <a href="/dashboard.php" class="l-btn l-btn-ghost l-btn-sm">DASHBOARD</a>
      <a href="/logout.php" class="l-btn l-btn-accent l-btn-sm">LOG OUT</a>
    </div>
  </div>
</header>

<main class="l-page">
  <div class="l-page-header">
    <h2 class="l-h2">Student accounts</h2>
    <p class="l-section-lead">Every student registered on EduTrack, and how many courses they've set up.</p>
  </div>

  <?php if (!$students): ?>
    <div class="l-table-card">
      <p class="l-empty-state">No students registered yet.</p>
    </div>
  <?php else: ?>
    <div class="l-table-card">
      <table class="l-table">
        <thead>
          <tr><th>Name</th><th>Email</th><th>Courses</th><th>Joined</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($students as $s): ?>
            <tr>
              <td><?= htmlspecialchars($s['name']) ?></td>
              <td><?= htmlspecialchars($s['email']) ?></td>
              <td><?= $s['course_count'] ?></td>
              <td><?= htmlspecialchars($s['created_at']) ?></td>
              <td>
                <form method="post" onsubmit="return confirm('Delete this student account and all their data?');">
                  <input type="hidden" name="action" value="delete_student">
                  <input type="hidden" name="user_id" value="<?= $s['id'] ?>">
                  <button type="submit" class="l-btn l-btn-danger l-btn-sm">DELETE</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</main>

</body>
</html>
