<?php $user = current_user(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EduTrack – Student Study Planner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php if ($user): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="/dashboard.php">EduTrack</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="/dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="/courses.php">Courses</a></li>
        <li class="nav-item"><a class="nav-link" href="/exams.php">Exams</a></li>
        <li class="nav-item"><a class="nav-link" href="/assignments.php">Assignments</a></li>
        <li class="nav-item"><a class="nav-link" href="/topics.php">Topics & Scores</a></li>
        <li class="nav-item"><a class="nav-link" href="/study_plan.php">Study Plan</a></li>
        <li class="nav-item"><a class="nav-link" href="/progress.php">Progress</a></li>
        <?php if ($user['role'] === 'admin'): ?>
        <li class="nav-item"><a class="nav-link" href="/admin/index.php">Admin</a></li>
        <?php endif; ?>
      </ul>
      <span class="navbar-text me-3">Hi, <?= htmlspecialchars($user['name']) ?></span>
      <a href="/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>
<?php endif; ?>
<main class="container py-4">
