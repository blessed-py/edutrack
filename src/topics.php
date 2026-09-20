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
            'DELETE t FROM topics t JOIN courses c ON c.id = t.course_id
             WHERE t.id = ? AND c.user_id = ?'
        );
        $stmt->execute([$_POST['topic_id'], $user['id']]);
    } elseif ($action === 'toggle_complete') {
        $stmt = $pdo->prepare(
            'UPDATE topics t JOIN courses c ON c.id = t.course_id
             SET t.is_completed = NOT t.is_completed
             WHERE t.id = ? AND c.user_id = ?'
        );
        $stmt->execute([$_POST['topic_id'], $user['id']]);
    } else {
        $courseId = (int) ($_POST['course_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $score = $_POST['previous_score'] !== '' ? (int) $_POST['previous_score'] : null;
        $isWeak = isset($_POST['is_weak']) ? 1 : 0;
        if ($courseId && $name) {
            $check = $pdo->prepare('SELECT id FROM courses WHERE id = ? AND user_id = ?');
            $check->execute([$courseId, $user['id']]);
            if ($check->fetch()) {
                $stmt = $pdo->prepare(
                    'INSERT INTO topics (course_id, name, previous_score, is_weak) VALUES (?, ?, ?, ?)'
                );
                $stmt->execute([$courseId, $name, $score, $isWeak]);
            }
        }
    }
    header('Location: /topics.php');
    exit;
}

$topicsStmt = $pdo->prepare(
    'SELECT t.*, c.name AS course_name FROM topics t
     JOIN courses c ON c.id = t.course_id
     WHERE c.user_id = ? ORDER BY t.is_completed ASC, t.is_weak DESC, t.name ASC'
);
$topicsStmt->execute([$user['id']]);
$topics = $topicsStmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/includes/header.php';
?>
<h2 class="mb-4">Topics & Previous Scores</h2>

<?php if (!$courses): ?>
  <div class="alert alert-warning">Add a course first before adding topics.</div>
<?php else: ?>
<div class="card p-3 mb-4">
  <form method="post" class="row g-2 align-items-center">
    <div class="col-md-3">
      <select name="course_id" class="form-select" required>
        <option value="">Select course</option>
        <?php foreach ($courses as $c): ?>
          <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <input type="text" name="name" class="form-control" placeholder="Topic name" required>
    </div>
    <div class="col-md-2">
      <input type="number" name="previous_score" class="form-control" placeholder="Score (0-100)" min="0" max="100">
    </div>
    <div class="col-md-2 form-check">
      <input type="checkbox" name="is_weak" class="form-check-input" id="isWeak">
      <label class="form-check-label" for="isWeak">Weak topic</label>
    </div>
    <div class="col-md-1">
      <button type="submit" class="btn btn-primary w-100">Add</button>
    </div>
  </form>
</div>
<?php endif; ?>

<?php if (!$topics): ?>
  <p class="text-muted">No topics added yet.</p>
<?php else: ?>
<div class="list-group">
  <?php foreach ($topics as $t): ?>
    <div class="list-group-item d-flex justify-content-between align-items-center <?= $t['is_completed'] ? 'opacity-50' : '' ?>">
      <div>
        <strong class="<?= $t['is_completed'] ? 'text-decoration-line-through' : '' ?>"><?= htmlspecialchars($t['name']) ?></strong>
        <span class="text-muted"> · <?= htmlspecialchars($t['course_name']) ?></span>
        <?php if ($t['previous_score'] !== null): ?>
          <span class="badge bg-secondary">Score: <?= $t['previous_score'] ?></span>
        <?php endif; ?>
        <?php if ($t['is_weak']): ?>
          <span class="badge bg-danger">Weak</span>
        <?php endif; ?>
      </div>
      <div class="d-flex gap-2">
        <form method="post">
          <input type="hidden" name="action" value="toggle_complete">
          <input type="hidden" name="topic_id" value="<?= $t['id'] ?>">
          <button type="submit" class="btn btn-sm btn-outline-secondary">
            Mark <?= $t['is_completed'] ? 'Incomplete' : 'Completed' ?>
          </button>
        </form>
        <form method="post">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="topic_id" value="<?= $t['id'] ?>">
          <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
