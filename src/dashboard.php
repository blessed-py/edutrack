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

$hoursStmt = $pdo->prepare('SELECT available_study_hours FROM users WHERE id = ?');
$hoursStmt->execute([$user['id']]);
$availableHours = (float) $hoursStmt->fetchColumn();

$topPlan = array_slice(build_study_plan($pdo, $user['id'], $availableHours), 0, 3);

require __DIR__ . '/includes/header.php';
?>
<h2 class="mb-4">Welcome back, <?= htmlspecialchars($user['name']) ?></h2>

<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card p-3 text-center">
      <div class="fs-3 fw-bold"><?= $courseCount ?></div>
      <div class="text-muted">Courses</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card p-3 text-center">
      <div class="fs-3 fw-bold"><?= count($upcomingExams) ?></div>
      <div class="text-muted">Upcoming Exams</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card p-3 text-center">
      <div class="fs-3 fw-bold"><?= $pendingAssignments ?></div>
      <div class="text-muted">Pending Assignments</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card p-3 text-center">
      <div class="fs-3 fw-bold"><?= $weakTopics ?></div>
      <div class="text-muted">Weak Topics</div>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-md-6">
    <h5>Upcoming Exams</h5>
    <?php if (!$upcomingExams): ?>
      <p class="text-muted">No upcoming exams scheduled.</p>
    <?php else: ?>
      <ul class="list-group">
        <?php foreach ($upcomingExams as $exam): ?>
          <li class="list-group-item d-flex justify-content-between">
            <span><?= htmlspecialchars($exam['title']) ?> (<?= htmlspecialchars($exam['course_name']) ?>)</span>
            <span class="text-muted"><?= htmlspecialchars($exam['exam_date']) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

  <div class="col-md-6">
    <h5>Today's Top Study Priorities</h5>
    <?php if (!$topPlan): ?>
      <p class="text-muted">Add courses and topics to see your study plan.</p>
    <?php else: ?>
      <ul class="list-group">
        <?php foreach ($topPlan as $item): ?>
          <li class="list-group-item d-flex justify-content-between">
            <span><?= htmlspecialchars($item['name']) ?> (<?= htmlspecialchars($item['course_name']) ?>)</span>
            <span class="badge bg-primary"><?= $item['allocated_hours'] ?> hrs</span>
          </li>
        <?php endforeach; ?>
      </ul>
      <a href="/study_plan.php" class="btn btn-link ps-0">View full study plan &rarr;</a>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
