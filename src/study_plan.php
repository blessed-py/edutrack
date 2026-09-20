<?php
require __DIR__ . '/config.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/priority.php';

$user = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['available_hours'])) {
    $hours = max(0, (float) $_POST['available_hours']);
    $stmt = $pdo->prepare('UPDATE users SET available_study_hours = ? WHERE id = ?');
    $stmt->execute([$hours, $user['id']]);
    header('Location: /study_plan.php');
    exit;
}

$hoursStmt = $pdo->prepare('SELECT available_study_hours FROM users WHERE id = ?');
$hoursStmt->execute([$user['id']]);
$availableHours = (float) $hoursStmt->fetchColumn();

$plan = build_study_plan($pdo, $user['id'], $availableHours);

function priority_class(float $score): string
{
    if ($score >= 0.5) return 'priority-high';
    if ($score >= 0.25) return 'priority-medium';
    return 'priority-low';
}

require __DIR__ . '/includes/header.php';
?>
<h2 class="mb-2">Smart Study Plan</h2>
<p class="text-muted">Computed as: <code>priority_score = 0.4 &times; urgency + 0.4 &times; weakness + 0.2 &times; deadline</code>, then your available hours are allocated proportionally to each topic's score.</p>

<div class="card p-3 mb-4">
  <form method="post" class="row g-2 align-items-center">
    <div class="col-md-3">
      <label class="form-label mb-0">Available study hours/day</label>
    </div>
    <div class="col-md-3">
      <input type="number" step="0.5" min="0" name="available_hours" class="form-control" value="<?= htmlspecialchars($availableHours) ?>">
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn btn-primary w-100">Update</button>
    </div>
  </form>
</div>

<?php if (!$plan): ?>
  <p class="text-muted">No pending topics found. Add courses and topics to generate a study plan.</p>
<?php else: ?>
<div class="list-group">
  <?php foreach ($plan as $item): ?>
    <div class="list-group-item <?= priority_class($item['priority_score']) ?>">
      <div class="d-flex justify-content-between">
        <div>
          <strong><?= htmlspecialchars($item['name']) ?></strong>
          <span class="text-muted"> · <?= htmlspecialchars($item['course_name']) ?></span>
          <?php if ($item['is_weak']): ?><span class="badge bg-danger">Weak</span><?php endif; ?>
        </div>
        <span class="badge bg-primary"><?= $item['allocated_hours'] ?> hrs today</span>
      </div>
      <div class="small text-muted mt-1">
        Priority score: <?= round($item['priority_score'], 3) ?>
        <?php if ($item['days_to_exam'] !== null): ?> · Exam in <?= $item['days_to_exam'] ?> day(s)<?php endif; ?>
        <?php if ($item['days_to_deadline'] !== null): ?> · Assignment due in <?= $item['days_to_deadline'] ?> day(s)<?php endif; ?>
        <?php if ($item['previous_score'] !== null): ?> · Previous score: <?= $item['previous_score'] ?><?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
