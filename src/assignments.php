<?php
require __DIR__ . '/config.php';
require __DIR__ . '/includes/auth.php';

$user = require_login();

$coursesStmt = $pdo->prepare('SELECT id, name FROM courses WHERE user_id = ? ORDER BY name');
$coursesStmt->execute([$user['id']]);
$courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';
    if ($action === 'delete') {
        $stmt = $pdo->prepare(
            'DELETE a FROM assignments a JOIN courses c ON c.id = a.course_id
             WHERE a.id = ? AND c.user_id = ?'
        );
        $stmt->execute([$_POST['assignment_id'], $user['id']]);
    } elseif ($action === 'toggle') {
        $stmt = $pdo->prepare(
            'UPDATE assignments a JOIN courses c ON c.id = a.course_id
             SET a.status = IF(a.status = "pending", "submitted", "pending")
             WHERE a.id = ? AND c.user_id = ?'
        );
        $stmt->execute([$_POST['assignment_id'], $user['id']]);
    } else {
        $courseId = (int) ($_POST['course_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $dueDate = $_POST['due_date'] ?? '';
        if ($courseId && $title && $dueDate) {
            $check = $pdo->prepare('SELECT id FROM courses WHERE id = ? AND user_id = ?');
            $check->execute([$courseId, $user['id']]);
            if ($check->fetch()) {
                $stmt = $pdo->prepare('INSERT INTO assignments (course_id, title, due_date) VALUES (?, ?, ?)');
                $stmt->execute([$courseId, $title, $dueDate]);
            }
        }
    }
    header('Location: /assignments.php');
    exit;
}

$assignmentsStmt = $pdo->prepare(
    'SELECT a.*, c.name AS course_name FROM assignments a
     JOIN courses c ON c.id = a.course_id
     WHERE c.user_id = ? ORDER BY a.due_date ASC'
);
$assignmentsStmt->execute([$user['id']]);
$assignments = $assignmentsStmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/includes/header.php';
?>
<h2 class="mb-4">Assignment Deadlines</h2>

<?php if (!$courses): ?>
  <div class="alert alert-warning">Add a course first before adding assignments.</div>
<?php else: ?>
<div class="card p-3 mb-4">
  <form method="post" class="row g-2">
    <div class="col-md-4">
      <select name="course_id" class="form-select" required>
        <option value="">Select course</option>
        <?php foreach ($courses as $c): ?>
          <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <input type="text" name="title" class="form-control" placeholder="Assignment title" required>
    </div>
    <div class="col-md-3">
      <input type="date" name="due_date" class="form-control" required>
    </div>
    <div class="col-md-1">
      <button type="submit" class="btn btn-primary w-100">Add</button>
    </div>
  </form>
</div>
<?php endif; ?>

<?php if (!$assignments): ?>
  <p class="text-muted">No assignments added yet.</p>
<?php else: ?>
<div class="list-group">
  <?php foreach ($assignments as $a): ?>
    <div class="list-group-item d-flex justify-content-between align-items-center">
      <div>
        <strong class="<?= $a['status'] === 'submitted' ? 'text-decoration-line-through text-muted' : '' ?>">
          <?= htmlspecialchars($a['title']) ?>
        </strong>
        <span class="text-muted"> · <?= htmlspecialchars($a['course_name']) ?> · due <?= htmlspecialchars($a['due_date']) ?></span>
        <span class="badge <?= $a['status'] === 'submitted' ? 'bg-success' : 'bg-warning text-dark' ?>"><?= $a['status'] ?></span>
      </div>
      <div class="d-flex gap-2">
        <form method="post">
          <input type="hidden" name="action" value="toggle">
          <input type="hidden" name="assignment_id" value="<?= $a['id'] ?>">
          <button type="submit" class="btn btn-sm btn-outline-secondary">
            Mark <?= $a['status'] === 'submitted' ? 'Pending' : 'Submitted' ?>
          </button>
        </form>
        <form method="post">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="assignment_id" value="<?= $a['id'] ?>">
          <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
