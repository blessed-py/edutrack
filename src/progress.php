<?php
require __DIR__ . '/config.php';
require __DIR__ . '/includes/auth.php';

$user = require_login();

$topicsStmt = $pdo->prepare(
    'SELECT t.id, t.name, c.name AS course_name FROM topics t
     JOIN courses c ON c.id = t.course_id
     WHERE c.user_id = ? AND t.is_completed = 0 ORDER BY t.name'
);
$topicsStmt->execute([$user['id']]);
$openTopics = $topicsStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $topicId = (int) ($_POST['topic_id'] ?? 0);
    $studiedOn = $_POST['studied_on'] ?? date('Y-m-d');
    $notes = trim($_POST['notes'] ?? '');

    $check = $pdo->prepare(
        'SELECT t.id FROM topics t JOIN courses c ON c.id = t.course_id
         WHERE t.id = ? AND c.user_id = ?'
    );
    $check->execute([$topicId, $user['id']]);
    if ($check->fetch()) {
        $stmt = $pdo->prepare('INSERT INTO study_sessions (topic_id, studied_on, notes) VALUES (?, ?, ?)');
        $stmt->execute([$topicId, $studiedOn, $notes]);
    }
    header('Location: /progress.php');
    exit;
}

$historyStmt = $pdo->prepare(
    'SELECT s.studied_on, s.notes, t.name AS topic_name, c.name AS course_name
     FROM study_sessions s
     JOIN topics t ON t.id = s.topic_id
     JOIN courses c ON c.id = t.course_id
     WHERE c.user_id = ?
     ORDER BY s.studied_on DESC LIMIT 30'
);
$historyStmt->execute([$user['id']]);
$history = $historyStmt->fetchAll(PDO::FETCH_ASSOC);

$completedCountStmt = $pdo->prepare(
    'SELECT COUNT(*) FROM topics t JOIN courses c ON c.id = t.course_id WHERE c.user_id = ? AND t.is_completed = 1'
);
$completedCountStmt->execute([$user['id']]);
$completedCount = (int) $completedCountStmt->fetchColumn();

$totalCountStmt = $pdo->prepare(
    'SELECT COUNT(*) FROM topics t JOIN courses c ON c.id = t.course_id WHERE c.user_id = ?'
);
$totalCountStmt->execute([$user['id']]);
$totalCount = (int) $totalCountStmt->fetchColumn();

$percent = $totalCount > 0 ? round(($completedCount / $totalCount) * 100) : 0;

require __DIR__ . '/includes/header.php';
?>
<h2 class="mb-4">Study Progress</h2>

<div class="card p-3 mb-4">
  <div class="d-flex justify-content-between mb-1">
    <span>Overall topic completion</span>
    <span><?= $completedCount ?> / <?= $totalCount ?> (<?= $percent ?>%)</span>
  </div>
  <div class="progress" style="height: 10px;">
    <div class="progress-bar bg-success" style="width: <?= $percent ?>%"></div>
  </div>
</div>

<?php if ($openTopics): ?>
<div class="card p-3 mb-4">
  <h5>Log a study session</h5>
  <form method="post" class="row g-2 align-items-center">
    <div class="col-md-4">
      <select name="topic_id" class="form-select" required>
        <option value="">Select topic</option>
        <?php foreach ($openTopics as $t): ?>
          <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?> (<?= htmlspecialchars($t['course_name']) ?>)</option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <input type="date" name="studied_on" class="form-control" value="<?= date('Y-m-d') ?>">
    </div>
    <div class="col-md-4">
      <input type="text" name="notes" class="form-control" placeholder="Notes (optional)">
    </div>
    <div class="col-md-1">
      <button type="submit" class="btn btn-primary w-100">Log</button>
    </div>
  </form>
</div>
<?php endif; ?>

<h5>Recent Study Sessions</h5>
<?php if (!$history): ?>
  <p class="text-muted">No study sessions logged yet.</p>
<?php else: ?>
<div class="list-group">
  <?php foreach ($history as $h): ?>
    <div class="list-group-item">
      <strong><?= htmlspecialchars($h['topic_name']) ?></strong>
      <span class="text-muted"> · <?= htmlspecialchars($h['course_name']) ?> · <?= htmlspecialchars($h['studied_on']) ?></span>
      <?php if ($h['notes']): ?><div class="small text-muted"><?= htmlspecialchars($h['notes']) ?></div><?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
