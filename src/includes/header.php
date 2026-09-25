<?php $user = current_user(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EduTrack – Student Study Planner</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;650;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/style.css?v=<?= @filemtime(__DIR__ . '/../assets/css/style.css') ?: time() ?>" rel="stylesheet">
</head>
<body>
<?php if ($user):
    $navItems = [
        ['href' => '/dashboard.php', 'label' => 'Dashboard', 'icon' => 'fa-house'],
        ['href' => '/courses.php', 'label' => 'Courses', 'icon' => 'fa-book'],
        ['href' => '/exams.php', 'label' => 'Exams', 'icon' => 'fa-file-pen'],
        ['href' => '/assignments.php', 'label' => 'Assignments', 'icon' => 'fa-paperclip'],
        ['href' => '/topics.php', 'label' => 'Topics & Scores', 'icon' => 'fa-bullseye'],
        ['href' => '/study_plan.php', 'label' => 'Study Plan', 'icon' => 'fa-brain'],
        ['href' => '/progress.php', 'label' => 'Progress', 'icon' => 'fa-chart-line'],
    ];
    if ($user['role'] === 'admin') {
        $navItems[] = ['href' => '/admin/index.php', 'label' => 'Admin', 'icon' => 'fa-gear'];
    }
    $currentScript = $_SERVER['SCRIPT_NAME'];
    $activeLabel = 'Dashboard';
    foreach ($navItems as $navItem) {
        if ($currentScript === $navItem['href']) {
            $activeLabel = $navItem['label'];
        }
    }
    if ($currentScript === '/profile.php') {
        $activeLabel = 'Profile';
    }
    $nameParts = preg_split('/\s+/', trim($user['name']));
    $initials = count($nameParts) >= 2
        ? mb_substr($nameParts[0], 0, 1) . mb_substr(end($nameParts), 0, 1)
        : mb_substr($nameParts[0] ?? '?', 0, 2);
    $initials = mb_strtoupper($initials);
?>
<div class="app-shell">
  <aside class="sidebar" id="sidebar">
    <a class="sidebar-brand" href="/dashboard.php">
      <span class="sidebar-brand-icon"><i class="fa-solid fa-graduation-cap"></i></span>
      <span>EduTrack</span>
    </a>
    <nav class="sidebar-nav">
      <?php foreach ($navItems as $navItem): ?>
        <a class="sidebar-link<?= $currentScript === $navItem['href'] ? ' active' : '' ?>" href="<?= $navItem['href'] ?>">
          <span class="sidebar-icon"><i class="fa-solid <?= $navItem['icon'] ?>"></i></span>
          <span><?= htmlspecialchars($navItem['label']) ?></span>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="avatar"><?= htmlspecialchars($initials) ?></div>
        <div class="sidebar-user-info">
          <div class="sidebar-user-name"><?= htmlspecialchars($user['name']) ?></div>
          <div class="sidebar-user-role"><?= htmlspecialchars(ucfirst($user['role'])) ?></div>
        </div>
      </div>
      <a href="/logout.php" class="sidebar-logout" title="Log out"><i class="fa-solid fa-right-from-bracket"></i></a>
    </div>
  </aside>
  <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

  <div class="app-content">
    <header class="topbar">
      <div class="d-flex align-items-center gap-2">
        <button class="sidebar-toggle" id="sidebar-toggle" type="button" aria-label="Toggle menu"><i class="fa-solid fa-bars"></i></button>
        <div class="topbar-breadcrumb">
          <span class="topbar-breadcrumb-root">EduTrack</span>
          <span class="topbar-breadcrumb-sep">/</span>
          <span><?= htmlspecialchars($activeLabel) ?></span>
        </div>
      </div>
      <div class="dropdown">
        <button class="avatar avatar-topbar" type="button" id="userMenuToggle" data-bs-toggle="dropdown" aria-expanded="false" aria-label="User menu">
          <?= htmlspecialchars($initials) ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end user-menu" aria-labelledby="userMenuToggle">
          <li class="user-menu-header">
            <div class="user-menu-name"><?= htmlspecialchars($user['name']) ?></div>
            <div class="user-menu-email"><?= htmlspecialchars($user['email']) ?></div>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="/profile.php"><i class="fa-solid fa-user"></i> Profile</a></li>
          <li><a class="dropdown-item" href="/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Log out</a></li>
        </ul>
      </div>
    </header>
    <main class="page-body">
<?php else: ?>
<main class="container py-4">
<?php endif; ?>
