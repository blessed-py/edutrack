<?php
require __DIR__ . '/config.php';
require __DIR__ . '/includes/auth.php';

$user = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM courses WHERE id = ? AND user_id = ?');
        $stmt->execute([$_POST['course_id'], $user['id']]);
    } else {
        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');
        if ($name) {
            $stmt = $pdo->prepare('INSERT INTO courses (user_id, name, code) VALUES (?, ?, ?)');
            $stmt->execute([$user['id'], $name, $code]);
        }
    }
    header('Location: /courses.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM courses WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user['id']]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/includes/header.php';
?>
<h2 class="mb-4">Courses</h2>

<div class="card p-3 mb-4">
  <form method="post" class="row g-2">
    <div class="col-md-5">
      <input type="text" name="name" class="form-control" placeholder="Course/Subject name" required>
    </div>
    <div class="col-md-4">
      <input type="text" name="code" class="form-control" placeholder="Course code (optional)">
    </div>
    <div class="col-md-3">
      <button type="submit" class="btn btn-primary w-100">Add Course</button>
    </div>
  </form>
</div>

<?php if (!$courses): ?>
  <p class="text-muted">No courses added yet.</p>
<?php else: ?>
<div class="list-group">
  <?php foreach ($courses as $course): ?>
    <div class="list-group-item d-flex justify-content-between align-items-center">
      <div>
        <strong><?= htmlspecialchars($course['name']) ?></strong>
        <?php if ($course['code']): ?><span class="text-muted"> · <?= htmlspecialchars($course['code']) ?></span><?php endif; ?>
      </div>
      <form method="post" onsubmit="return confirm('Delete this course and all its data?');">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
      </form>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
