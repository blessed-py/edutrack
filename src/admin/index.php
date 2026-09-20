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

require __DIR__ . '/../includes/header.php';
?>
<h2 class="mb-4">Admin – Student Accounts</h2>

<?php if (!$students): ?>
  <p class="text-muted">No students registered yet.</p>
<?php else: ?>
<table class="table bg-white">
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
            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
