<?php
require __DIR__ . '/config.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/priority.php';

$user = require_login();

$courseCount = $pdo->prepare('SELECT COUNT(*) FROM courses WHERE user_id = ?');
$courseCount->execute([$user['id']]);
$courseCount = (int) $courseCount->fetchColumn();

$upcomingExams = $pdo->prepare(
    'SELECT e.title, e.exam_date, c.name AS course_name
     FROM exams e JOIN courses c ON c.id = e.course_id
     WHERE c.user_id = ? AND e.exam_date >= CURDATE()
     ORDER BY e.exam_date ASC LIMIT 5'
);
$upcomingExams->execute([$user['id']]);
$upcomingExams = $upcomingExams->fetchAll(PDO::FETCH_ASSOC);

$upcomingAssignments = $pdo->prepare(
    'SELECT a.title, a.due_date, c.name AS course_name
     FROM assignments a JOIN courses c ON c.id = a.course_id
     WHERE c.user_id = ? AND a.status = "pending" AND a.due_date >= CURDATE()
     ORDER BY a.due_date ASC LIMIT 5'
);
$upcomingAssignments->execute([$user['id']]);
$upcomingAssignments = $upcomingAssignments->fetchAll(PDO::FETCH_ASSOC);

$timeline = [];
foreach ($upcomingExams as $e) {
    $timeline[] = ['type' => 'exam', 'title' => $e['title'], 'course' => $e['course_name'], 'date' => $e['exam_date']];
}
foreach ($upcomingAssignments as $a) {
    $timeline[] = ['type' => 'assignment', 'title' => $a['title'], 'course' => $a['course_name'], 'date' => $a['due_date']];
}
usort($timeline, fn($a, $b) => $a['date'] <=> $b['date']);
$timeline = array_slice($timeline, 0, 6);

$pendingAssignments = $pdo->prepare(
    'SELECT COUNT(*) FROM assignments a JOIN courses c ON c.id = a.course_id
     WHERE c.user_id = ? AND a.status = "pending"'
);
$pendingAssignments->execute([$user['id']]);
$pendingAssignments = (int) $pendingAssignments->fetchColumn();

$weakTopics = $pdo->prepare(
    'SELECT COUNT(*) FROM topics t JOIN courses c ON c.id = t.course_id
     WHERE c.user_id = ? AND t.is_weak = 1 AND t.is_completed = 0'
);
$weakTopics->execute([$user['id']]);
$weakTopics = (int) $weakTopics->fetchColumn();

$totalTopics = $pdo->prepare('SELECT COUNT(*) FROM topics t JOIN courses c ON c.id = t.course_id WHERE c.user_id = ?');
$totalTopics->execute([$user['id']]);
$totalTopics = (int) $totalTopics->fetchColumn();

$completedTopics = $pdo->prepare(
    'SELECT COUNT(*) FROM topics t JOIN courses c ON c.id = t.course_id WHERE c.user_id = ? AND t.is_completed = 1'
);
$completedTopics->execute([$user['id']]);
$completedTopics = (int) $completedTopics->fetchColumn();
$completionPct = $totalTopics > 0 ? round(($completedTopics / $totalTopics) * 100) : 0;

$hoursStmt = $pdo->prepare('SELECT available_study_hours FROM users WHERE id = ?');
$hoursStmt->execute([$user['id']]);
$availableHours = (float) $hoursStmt->fetchColumn();

$topPlan = array_slice(build_study_plan($pdo, $user['id'], $availableHours), 0, 5);
$maxScore = $topPlan ? max(array_column($topPlan, 'priority_score')) : 0;

require __DIR__ . '/includes/header.php';
?>
<h2 class="mb-4">Welcome back, <?= htmlspecialchars($user['name']) ?></h2>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-tile">
      <div class="stat-icon"><i class="fa-solid fa-book"></i></div>
      <div class="stat-value"><?= $courseCount ?></div>
      <div class="stat-label">Courses</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-tile">
      <div class="stat-icon"><i class="fa-solid fa-file-pen"></i></div>
      <div class="stat-value"><?= count($upcomingExams) ?></div>
      <div class="stat-label">Upcoming Exams</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-tile">
      <div class="stat-icon"><i class="fa-solid fa-paperclip"></i></div>
      <div class="stat-value"><?= $pendingAssignments ?></div>
      <div class="stat-label">Pending Assignments</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-tile">
      <div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
      <div class="stat-value"><?= $weakTopics ?></div>
      <div class="stat-label">Weak Topics</div>
    </div>
  </div>
</div>

<div class="row g-4 mb-1">
  <div class="col-lg-4">
    <div class="card p-3 h-100 d-flex flex-row align-items-center gap-3">
      <div class="progress-ring" style="--pct: <?= $completionPct ?>;"><span><?= $completionPct ?>%</span></div>
      <div>
        <h6 class="mb-1">Overall Completion</h6>
        <div class="text-muted small"><?= $completedTopics ?> of <?= $totalTopics ?> topics completed</div>
        <a href="/progress.php" class="small">View progress &rarr;</a>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card p-3 h-100">
      <h5>This Week</h5>
      <?php if (!$timeline): ?>
        <p class="text-muted mb-0">Nothing due soon. You're all caught up.</p>
      <?php else: ?>
        <?php foreach ($timeline as $item): ?>
          <div class="timeline-item">
            <span class="timeline-dot <?= $item['type'] ?>"></span>
            <div class="flex-grow-1">
              <span class="timeline-type-badge <?= $item['type'] ?>"><?= $item['type'] === 'exam' ? 'Exam' : 'Assignment' ?></span>
              <div><?= htmlspecialchars($item['title']) ?> <span class="text-muted">(<?= htmlspecialchars($item['course']) ?>)</span></div>
            </div>
            <div class="timeline-date"><?= htmlspecialchars(date('M j', strtotime($item['date']))) ?></div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card p-3 h-100">
      <h5>Today's Top Study Priorities</h5>
      <?php if (!$topPlan): ?>
        <p class="text-muted mb-0">Add courses and topics to see your study plan.</p>
      <?php else: ?>
        <?php foreach ($topPlan as $item):
          $pct = $maxScore > 0 ? round(($item['priority_score'] / $maxScore) * 100) : 0;
          $barClass = $item['priority_score'] >= 0.5 ? 'critical' : ($item['priority_score'] >= 0.25 ? 'warning' : 'good');
        ?>
          <div class="viz-bar-row">
            <div class="viz-bar-label">
              <span><?= htmlspecialchars($item['name']) ?></span>
              <span><?= $item['allocated_hours'] ?> hrs</span>
            </div>
            <div class="viz-bar-track"><div class="viz-bar-fill <?= $barClass ?>" style="width: <?= $pct ?>%"></div></div>
          </div>
        <?php endforeach; ?>
        <a href="/study_plan.php" class="btn btn-link ps-0 mt-2">View full study plan &rarr;</a>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
