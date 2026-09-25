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

$sessionsByTopic = $pdo->prepare(
    'SELECT t.name AS topic_name, c.name AS course_name, COUNT(s.id) AS session_count
     FROM study_sessions s
     JOIN topics t ON t.id = s.topic_id
     JOIN courses c ON c.id = t.course_id
     WHERE c.user_id = ?
     GROUP BY t.id, t.name, c.name
     ORDER BY session_count DESC
     LIMIT 5'
);
$sessionsByTopic->execute([$user['id']]);
$sessionsByTopic = $sessionsByTopic->fetchAll(PDO::FETCH_ASSOC);
$maxSessions = $sessionsByTopic ? max(array_column($sessionsByTopic, 'session_count')) : 0;

require __DIR__ . '/includes/header.php';
?>
<h2 class="mb-4">Study Progress</h2>

<div class="row g-4 mb-4">
  <div class="col-lg-6">
    <div class="card p-3 h-100 d-flex flex-row align-items-center gap-3">
      <div class="progress-ring" style="--pct: <?= $percent ?>;"><span><?= $percent ?>%</span></div>
      <div>
        <h6 class="mb-1">Overall Topic Completion</h6>
        <div class="text-muted small"><?= $completedCount ?> of <?= $totalCount ?> topics completed</div>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card p-3 h-100">
      <h6 class="mb-2">Most Studied Topics</h6>
      <?php if (!$sessionsByTopic): ?>
        <p class="text-muted mb-0 small">Log a study session below to see this chart fill in.</p>
      <?php else: ?>
        <?php foreach ($sessionsByTopic as $s): $pct = $maxSessions > 0 ? round(($s['session_count'] / $maxSessions) * 100) : 0; ?>
          <div class="viz-bar-row">
            <div class="viz-bar-label">
              <span><?= htmlspecialchars($s['topic_name']) ?> <span class="text-muted">(<?= htmlspecialchars($s['course_name']) ?>)</span></span>
              <span><?= $s['session_count'] ?> session<?= $s['session_count'] == 1 ? '' : 's' ?></span>
            </div>
            <div class="viz-bar-track"><div class="viz-bar-fill" style="width: <?= $pct ?>%"></div></div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
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
