<?php
require __DIR__ . '/config.php';
require __DIR__ . '/includes/auth.php';

$user = require_login();

$coursesStmt = $pdo->prepare('SELECT id, name FROM courses WHERE user_id = ? ORDER BY name');
$coursesStmt->execute([$user['id']]);
$courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $stmt = $pdo->prepare(
            'DELETE e FROM exams e JOIN courses c ON c.id = e.course_id
             WHERE e.id = ? AND c.user_id = ?'
        );
        $stmt->execute([$_POST['exam_id'], $user['id']]);
    } else {
        $courseId = (int) ($_POST['course_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $examDate = $_POST['exam_date'] ?? '';
        if ($courseId && $title && $examDate) {
            $check = $pdo->prepare('SELECT id FROM courses WHERE id = ? AND user_id = ?');
            $check->execute([$courseId, $user['id']]);
            if ($check->fetch()) {
                $stmt = $pdo->prepare('INSERT INTO exams (course_id, title, exam_date) VALUES (?, ?, ?)');
                $stmt->execute([$courseId, $title, $examDate]);
            }
        }
    }
    header('Location: /exams.php');
    exit;
}

$examsStmt = $pdo->prepare(
    'SELECT e.*, c.name AS course_name FROM exams e
     JOIN courses c ON c.id = e.course_id
     WHERE c.user_id = ? ORDER BY e.exam_date ASC'
);
$examsStmt->execute([$user['id']]);
$exams = $examsStmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/includes/header.php';
?>
<h2 class="mb-4">Examination Schedule</h2>

<?php if (!$courses): ?>
  <div class="alert alert-warning">Add a course first before scheduling exams.</div>
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
      <input type="text" name="title" class="form-control" placeholder="Exam title" required>
    </div>
    <div class="col-md-3">
      <input type="date" name="exam_date" class="form-control" required>
    </div>
    <div class="col-md-1">
      <button type="submit" class="btn btn-primary w-100">Add</button>
    </div>
  </form>
</div>
<?php endif; ?>

<?php if (!$exams): ?>
  <p class="text-muted">No exams scheduled yet.</p>
<?php else: ?>
<div class="list-group">
  <?php foreach ($exams as $exam): ?>
    <div class="list-group-item d-flex justify-content-between align-items-center">
      <div>
        <strong><?= htmlspecialchars($exam['title']) ?></strong>
        <span class="text-muted"> · <?= htmlspecialchars($exam['course_name']) ?> · <?= htmlspecialchars($exam['exam_date']) ?></span>
      </div>
      <form method="post">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="exam_id" value="<?= $exam['id'] ?>">
        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
      </form>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
