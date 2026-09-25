<?php
require __DIR__ . '/config.php';
require __DIR__ . '/includes/auth.php';

$user = current_user();
if ($user) {
    header('Location: ' . ($user['role'] === 'admin' ? '/admin/index.php' : '/dashboard.php'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EduTrack – Student Study Planner</title>
    <script>document.documentElement.classList.add('js');</script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="/assets/css/landing.css?v=<?= @filemtime(__DIR__ . '/assets/css/landing.css') ?: time() ?>" rel="stylesheet">
</head>
<body>

<header class="l-nav">
  <div class="l-nav-inner">
    <a class="l-brand" href="/"><span class="l-brand-mark"></span>EduTrack</a>
    <nav class="l-nav-links">
      <a href="#tracks">FEATURES</a>
      <a href="#formula">THE FORMULA</a>
    </nav>
    <div class="l-nav-actions">
      <a href="/login.php" class="l-btn l-btn-ghost l-btn-sm">LOG IN</a>
      <a href="/register.php" class="l-btn l-btn-accent l-btn-sm">GET STARTED</a>
    </div>
  </div>
</header>

<main>

  <section class="l-hero">
    <span class="l-blob l-blob-1" aria-hidden="true"></span>
    <span class="l-blob l-blob-2" aria-hidden="true"></span>
    <span class="l-blob l-blob-3" aria-hidden="true"></span>

    <div class="l-hero-copy reveal">
      <span class="l-badge">BUILT FOR CSE56D — WEB DEV USING PHP</span>
      <h1 class="l-h1">Study planner,<br>cut to <span class="l-accent-text">priority</span>.</h1>
      <p class="l-lead">Add your courses, exam dates, and assignment deadlines. Flag the topics you're weak on. EduTrack ranks what's urgent and splits your study hours across it. Automatically.</p>
      <div class="l-hero-actions">
        <a href="/register.php" class="l-btn l-btn-accent l-btn-lg">GET STARTED</a>
        <a href="/login.php" class="l-btn l-btn-ghost l-btn-lg">LOG IN</a>
      </div>
      <div class="l-trust-row">
        <span>&check; FREE FOR STUDENTS</span>
        <span>&check; NO SETUP</span>
      </div>
    </div>

    <div class="l-hero-visual reveal">
      <div class="l-mockup" id="mockup">
        <div class="l-mockup-bar">
          <span class="l-dot l-dot-r"></span><span class="l-dot l-dot-y"></span><span class="l-dot l-dot-g"></span>
          <span class="l-mockup-title">EDUTRACK DASHBOARD</span>
        </div>
        <div class="l-mockup-body">
          <div class="l-mockup-stats">
            <div class="l-stat">
              <div class="l-stat-value" data-count="6">6</div>
              <div class="l-stat-label">COURSES</div>
            </div>
            <div class="l-stat">
              <div class="l-stat-value" data-count="2">2</div>
              <div class="l-stat-label">EXAMS</div>
            </div>
            <div class="l-stat">
              <div class="l-stat-value" data-count="3">3</div>
              <div class="l-stat-label">WEAK TOPICS</div>
            </div>
          </div>
          <div class="l-bar-row">
            <div class="l-bar-label"><span>Binary Search Trees</span><span>0.6 hrs</span></div>
            <div class="l-bar-track"><div class="l-bar-fill l-bar-critical" style="width: 92%"></div></div>
          </div>
          <div class="l-bar-row">
            <div class="l-bar-label"><span>Normalization (DBMS)</span><span>0.5 hrs</span></div>
            <div class="l-bar-track"><div class="l-bar-fill l-bar-warning" style="width: 71%"></div></div>
          </div>
        </div>
      </div>
      <div class="l-float-card l-float-1">
        <span class="l-float-label">TOPICS DONE</span>
        <strong>16 / 25 &middot; 64%</strong>
      </div>
      <div class="l-float-card l-float-2">
        <span class="l-float-label">NEXT EXAM</span>
        <strong>DBMS &middot; 3 DAYS</strong>
      </div>
    </div>
  </section>

  <div class="l-ticker" aria-hidden="true">
    <div class="l-ticker-inner">
      <div class="l-ticker-track">
        <span class="l-ticker-item"><b>PLAN</b> COURSES &rarr; EXAMS &rarr; ASSIGNMENTS</span>
        <span class="l-ticker-item"><b>SCORE</b> URGENCY + WEAKNESS + DEADLINE</span>
        <span class="l-ticker-item"><b>SPLIT</b> HOURS ALLOCATED BY PRIORITY</span>
        <span class="l-ticker-item"><b>TRACK</b> SESSIONS LOGGED, TOPICS DONE</span>
        <span class="l-ticker-item"><b>PLAN</b> COURSES &rarr; EXAMS &rarr; ASSIGNMENTS</span>
        <span class="l-ticker-item"><b>SCORE</b> URGENCY + WEAKNESS + DEADLINE</span>
        <span class="l-ticker-item"><b>SPLIT</b> HOURS ALLOCATED BY PRIORITY</span>
        <span class="l-ticker-item"><b>TRACK</b> SESSIONS LOGGED, TOPICS DONE</span>
      </div>
    </div>
  </div>

  <section class="l-section" id="tracks">
    <span class="l-blob l-blob-4" aria-hidden="true"></span>
    <div class="l-eyebrow reveal"><b>( 01 )</b> CAPABILITIES<span class="l-eyebrow-line"></span></div>
    <h2 class="l-h2 reveal">Everything a study plan needs.<br><span class="l-accent-text">Nothing it doesn't.</span></h2>
    <p class="l-section-lead reveal">Six things EduTrack tracks so you don't have to keep it all in your head.</p>

    <div class="l-grid">
      <div class="l-card reveal">
        <div class="l-card-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v18H6.5A2.5 2.5 0 0 0 4 22.5v-18Z"/><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/></svg>
        </div>
        <h3>Courses</h3>
        <p>Every subject you're taking this term, with course codes.</p>
      </div>
      <div class="l-card reveal">
        <div class="l-card-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3" y="5" width="18" height="16" rx="1"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
        </div>
        <h3>Examination schedule</h3>
        <p>Exam dates per course &mdash; the clock the urgency score counts down from.</p>
      </div>
      <div class="l-card reveal">
        <div class="l-card-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M9 12.5l2 2 4-4.5"/><rect x="3" y="4" width="18" height="17" rx="1"/></svg>
        </div>
        <h3>Assignment deadlines</h3>
        <p>Due dates with a pending/submitted status you toggle yourself.</p>
      </div>
      <div class="l-card reveal">
        <div class="l-card-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/></svg>
        </div>
        <h3>Weak topics &amp; previous scores</h3>
        <p>Flag a topic as weak and log the score you last got on it.</p>
      </div>
      <div class="l-card reveal">
        <div class="l-card-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M4 20V10M11 20V4M18 20v-7"/></svg>
        </div>
        <h3>Smart study planner</h3>
        <p>A priority score per topic, and your daily hours split across them accordingly.</p>
      </div>
      <div class="l-card reveal">
        <div class="l-card-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M3 17l5-6 4 4 8-9"/><path d="M14 6h6v6"/></svg>
        </div>
        <h3>Progress tracking</h3>
        <p>Log study sessions, mark topics done, and watch a completion ring fill in.</p>
      </div>
    </div>
  </section>

  <section class="l-section" id="formula">
    <div class="l-eyebrow reveal"><b>( 02 )</b> THE FORMULA<span class="l-eyebrow-line"></span></div>
    <h2 class="l-h2 reveal">The plan is a <span class="l-accent-text">formula</span>, not a guess.</h2>

    <div class="l-formula-grid">
      <div class="l-formula-copy reveal">
        <p>Every open topic gets scored on three things: how soon its exam is, how weak you've marked it, and how soon a related assignment is due. Topics are ranked by that score, highest first, and your available study hours for the day are split across them proportionally.</p>
        <p class="l-formula-example">A topic with an exam in 3 days, marked weak, with a previous score of 40, will out-rank a topic due in 3 weeks that you've never struggled with &mdash; even if both have assignments this week.</p>
      </div>
      <div class="l-formula-card reveal">
priority_score = 0.4 &times; <span class="l-accent-text">urgency</span> + 0.4 &times; <span class="l-accent-text">weakness</span> + 0.2 &times; <span class="l-accent-text">deadline</span><br><br>
<span class="l-accent-text">urgency</span>&nbsp; &nbsp;= 1 / (days to nearest exam + 1)<br>
<span class="l-accent-text">weakness</span> = (100 &minus; previous score) / 100<br>
<span class="l-accent-text">deadline</span>&nbsp; = 1 / (days to nearest assignment + 1)
      </div>
    </div>
  </section>

  <section class="l-cta-wrap">
    <span class="l-blob l-blob-5" aria-hidden="true"></span>
    <div class="l-cta-card reveal">
      <h2 class="l-h2">Ready to make every <span class="l-accent-text">hour count</span>?</h2>
      <p>Add your courses, flag your weak topics, and let EduTrack build today's plan.</p>
      <a href="/register.php" class="l-btn l-btn-accent l-btn-lg">GET STARTED &mdash; IT'S FREE</a>
    </div>
  </section>

</main>

<footer class="l-footer">
  <div class="l-footer-flourish" aria-hidden="true">FOCUS.</div>
  <div class="l-footer-inner">
    <span>EDUTRACK &mdash; STUDENT STUDY PLANNER</span>
    <div class="l-footer-links">
      <a href="/register.php">REGISTER</a>
      <a href="/login.php">LOG IN</a>
      <a href="/login.php">ADMIN</a>
    </div>
  </div>
</footer>

<script src="/assets/js/landing.js?v=<?= @filemtime(__DIR__ . '/assets/js/landing.js') ?: time() ?>"></script>
</body>
</html>
