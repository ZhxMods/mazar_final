<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth_check.php';

$lang      = getCurrentLang();
$dir       = getDirection();
$userId    = (int)$_SESSION[SESS_USER_ID];
$gradeId   = (int)$_SESSION[SESS_GRADE];
$userXP    = (int)$_SESSION[SESS_XP];
$userLevel = (int)$_SESSION[SESS_LEVEL];
$userName  = $_SESSION[SESS_USERNAME] ?? 'Student';

// Get student's grade level name
$db      = getDB();
$lvlStmt = $db->prepare("SELECT name_{$lang} AS name FROM levels WHERE id = ?");
$lvlStmt->execute([$gradeId]);
$gradeName = $lvlStmt->fetchColumn() ?: '—';

// Subjects for this grade
$subjects  = getSubjectsByLevel($gradeId);
$leaderboard = getLeaderboard($gradeId, 5);

// Get user rank
$rankStmt = $db->prepare(
    "SELECT COUNT(*)+1 AS rank FROM users
     WHERE grade_level_id=? AND xp_points>? AND role='student' AND status='active'"
);
$rankStmt->execute([$gradeId, $userXP]);
$userRank = $rankStmt->fetchColumn() ?: 1;

// Active subject
$activeSubjectId = (int)($_GET['subject'] ?? ($subjects[0]['id'] ?? 0));
$lessons = $activeSubjectId ? getLessonsBySubject($activeSubjectId, $userId) : [];

$progressPct  = xpProgressPercent($userXP, $userLevel);
$nextLevelXP  = xpForNextLevel($userLevel);

$tab = $_GET['tab'] ?? 'lessons';
$welcome = isset($_GET['welcome']);
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= t('dashboard') ?> — <?= t('site_name') ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <link rel="stylesheet" href="../assets/css/xp-animations.css">
  <style>
    body { font-family: <?= $lang==='ar' ? "'Cairo'" : "'Poppins'" ?>, sans-serif; background:#f1f5f9; }
    .sidebar { background: linear-gradient(180deg, #1e3a8a 0%, #1d4ed8 100%); }
    .active-tab { background: rgba(255,255,255,0.15); }
    .level-badge { background: linear-gradient(135deg, <?= levelBadgeColor($userLevel) ?>, <?= levelBadgeColor(min($userLevel+1,10)) ?>); }
    .card { background:#fff; border-radius:1rem; box-shadow:0 1px 3px rgba(0,0,0,.07); }
    .lesson-card { transition: transform .2s, box-shadow .2s; }
    .lesson-card:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(59,130,246,.15); }
    .shimmer { background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); background-size:200% 100%; animation: shimmer 1.5s infinite; }
    @keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
    .xp-bar-fill { transition: width 1s cubic-bezier(.4,0,.2,1); }
    .toast { position:fixed; top:20px; <?= $dir==='rtl'?'left':'right' ?>:20px; z-index:9999; }
    <?php if($dir==='rtl'): ?>
    .sidebar-nav a .icon { margin-left:.75rem; margin-right:0; }
    <?php endif; ?>
  </style>
</head>

<body class="flex h-screen overflow-hidden" x-data="mazarDashboard()" x-init="init()">

<!-- ────────────────────────────────────────────
     TOAST NOTIFICATIONS
──────────────────────────────────────────── -->
<div id="toast-container" class="toast space-y-2"></div>
<div id="xp-float-container" style="position:fixed;top:0;left:0;pointer-events:none;z-index:9998;"></div>

<!-- ────────────────────────────────────────────
     SIDEBAR
──────────────────────────────────────────── -->
<aside class="sidebar w-64 flex-shrink-0 flex flex-col h-full overflow-y-auto hidden md:flex">
  <!-- Logo -->
  <div class="px-6 py-6 border-b border-white/10">
    <a href="/" class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
        <span class="text-white font-black text-xl">M</span>
      </div>
      <span class="text-white font-black text-xl"><?= t('site_name') ?></span>
    </a>
  </div>

  <!-- Student Profile -->
  <div class="px-6 py-5 border-b border-white/10">
    <div class="flex items-center gap-3 mb-4">
      <div class="w-11 h-11 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
        <?= mb_strtoupper(mb_substr($userName, 0, 1)) ?>
      </div>
      <div class="overflow-hidden">
        <div class="text-white font-semibold text-sm truncate"><?= htmlspecialchars($userName) ?></div>
        <div class="text-blue-200 text-xs truncate"><?= htmlspecialchars($gradeName) ?></div>
      </div>
    </div>
    <!-- XP + Level -->
    <div class="flex items-center justify-between mb-2">
      <span class="text-blue-200 text-xs"><?= t('level') ?> <span id="sidebar-level" class="text-white font-bold"><?= $userLevel ?></span></span>
      <span class="text-yellow-300 text-xs font-bold"><span id="sidebar-xp"><?= $userXP ?></span> XP</span>
    </div>
    <div class="bg-white/20 rounded-full h-2">
      <div id="sidebar-xp-bar" class="xp-bar-fill bg-yellow-400 h-2 rounded-full" style="width:<?= $progressPct ?>%"></div>
    </div>
    <div class="text-right mt-1">
      <span class="text-blue-300 text-xs"><?= round($progressPct) ?>% → <?= t('level') ?> <?= $userLevel+1 ?></span>
    </div>
  </div>

  <!-- Navigation -->
  <nav class="px-4 py-4 flex-1 sidebar-nav">
    <a href="?tab=lessons"
       class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition mb-1 <?= $tab==='lessons'?'active-tab':'' ?>">
      <i data-lucide="book-open" class="icon w-5 h-5 flex-shrink-0"></i>
      <span class="text-sm font-medium"><?= t('my_lessons') ?></span>
    </a>
    <a href="?tab=leaderboard"
       class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition mb-1 <?= $tab==='leaderboard'?'active-tab':'' ?>">
      <i data-lucide="trophy" class="icon w-5 h-5 flex-shrink-0"></i>
      <span class="text-sm font-medium"><?= t('leaderboard') ?></span>
    </a>
    <a href="?tab=achievements"
       class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition mb-1 <?= $tab==='achievements'?'active-tab':'' ?>">
      <i data-lucide="star" class="icon w-5 h-5 flex-shrink-0"></i>
      <span class="text-sm font-medium"><?= t('achievements') ?></span>
    </a>
  </nav>

  <!-- Language + Logout -->
  <div class="px-6 py-4 border-t border-white/10 space-y-3">
    <div class="flex gap-1">
      <?php foreach(['ar','fr','en'] as $l): ?>
      <a href="?lang=<?= $l ?>&tab=<?= $tab ?>"
         class="flex-1 text-center py-1 rounded-lg text-xs font-bold transition <?= $lang===$l ? 'bg-white text-blue-700' : 'text-white/60 hover:bg-white/10' ?>">
        <?= strtoupper($l) ?>
      </a>
      <?php endforeach; ?>
    </div>
    <a href="../logout.php" class="flex items-center gap-2 text-white/60 hover:text-white text-sm transition px-2 py-2 rounded-xl hover:bg-white/10">
      <i data-lucide="log-out" class="w-4 h-4 flex-shrink-0"></i>
      <?= t('logout') ?>
    </a>
  </div>
</aside>

<!-- ────────────────────────────────────────────
     MAIN CONTENT
──────────────────────────────────────────── -->
<div class="flex-1 flex flex-col overflow-hidden">

  <!-- Top Bar -->
  <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between flex-shrink-0">
    <div>
      <h1 class="font-black text-gray-900 text-lg"><?= t('dashboard') ?></h1>
      <p class="text-gray-500 text-xs"><?= htmlspecialchars($gradeName) ?></p>
    </div>
    <div class="flex items-center gap-4">
      <!-- XP Badge -->
      <div class="flex items-center gap-2 bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-2">
        <i data-lucide="zap" class="w-4 h-4 text-yellow-500"></i>
        <span class="font-bold text-yellow-700 text-sm"><span id="header-xp"><?= $userXP ?></span> XP</span>
      </div>
      <!-- Level -->
      <div class="level-badge flex items-center gap-2 rounded-xl px-4 py-2">
        <i data-lucide="shield" class="w-4 h-4 text-white"></i>
        <span class="font-bold text-white text-sm"><?= t('level') ?> <span id="header-level"><?= $userLevel ?></span></span>
      </div>
      <!-- Rank -->
      <div class="hidden sm:flex items-center gap-2 bg-blue-50 border border-blue-200 rounded-xl px-4 py-2">
        <i data-lucide="trophy" class="w-4 h-4 text-blue-600"></i>
        <span class="font-bold text-blue-700 text-sm">#<?= $userRank ?></span>
      </div>
    </div>
  </header>

  <!-- Scrollable Content -->
  <main class="flex-1 overflow-y-auto p-6">

    <?php if ($tab === 'lessons'): ?>
    <!-- ═══ LESSONS TAB ═══ -->

    <!-- XP Progress Card -->
    <div class="card p-5 mb-6 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
      <div class="flex items-center justify-between mb-3">
        <div>
          <div class="text-white/80 text-xs mb-1"><?= t('my_progress') ?></div>
          <div class="text-2xl font-black"><?= $userXP ?> <span class="text-white/60 text-sm font-normal">XP</span></div>
        </div>
        <div class="text-right">
          <div class="text-white/80 text-xs mb-1"><?= t('next_level') ?> (<?= $userLevel+1 ?>)</div>
          <div class="text-lg font-bold"><?= $nextLevelXP ?> XP</div>
        </div>
      </div>
      <div class="bg-white/20 rounded-full h-3">
        <div class="bg-white/80 h-3 rounded-full transition-all duration-1000" style="width:<?= $progressPct ?>%"></div>
      </div>
      <div class="text-right mt-1 text-white/60 text-xs"><?= round($progressPct) ?>%</div>
    </div>

    <!-- Subject Tabs -->
    <div class="flex gap-2 mb-5 overflow-x-auto pb-2 flex-wrap">
      <?php foreach($subjects as $subj): ?>
      <a href="?tab=lessons&subject=<?= $subj['id'] ?>"
         class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition flex-shrink-0
         <?= $activeSubjectId===$subj['id'] ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'bg-white text-gray-600 hover:bg-blue-50 hover:text-blue-700 border border-gray-200' ?>">
        <i data-lucide="<?= htmlspecialchars($subj['icon']) ?>" class="w-4 h-4"></i>
        <?= htmlspecialchars($subj['name']) ?>
      </a>
      <?php endforeach; ?>
      <?php if(empty($subjects)): ?>
      <p class="text-gray-400 text-sm"><?= t('no_results') ?></p>
      <?php endif; ?>
    </div>

    <!-- Lesson Cards -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5" id="lessons-grid">
      <?php foreach($lessons as $lesson): ?>
      <?php
        $ytId   = $lesson['type']==='video' ? youtubeId($lesson['url']) : '';
        $thumb  = $ytId ? "https://img.youtube.com/vi/{$ytId}/hqdefault.jpg" : ($lesson['thumbnail'] ?: '');
        $completed = (bool)$lesson['completed'];
      ?>
      <div class="card lesson-card overflow-hidden animate__animated animate__fadeInUp"
           data-lesson-id="<?= $lesson['id'] ?>"
           data-completed="<?= $completed ? '1' : '0' ?>">

        <!-- Thumbnail -->
        <div class="relative h-40 bg-gray-100 overflow-hidden">
          <?php if($thumb): ?>
          <img src="<?= htmlspecialchars($thumb) ?>" alt="" class="w-full h-full object-cover">
          <?php else: ?>
          <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-100 to-indigo-200">
            <i data-lucide="<?= $lesson['type']==='video'?'play-circle':($lesson['type']==='pdf'?'file-text':'book-open') ?>" class="w-14 h-14 text-blue-400"></i>
          </div>
          <?php endif; ?>
          <!-- Type Badge -->
          <div class="absolute top-2 <?= $dir==='rtl'?'right-2':'left-2' ?> bg-black/60 text-white text-xs px-2 py-1 rounded-lg font-medium">
            <?= $lesson['type'] === 'video' ? '📹 Vidéo' : ($lesson['type']==='pdf' ? '📄 PDF' : '📗 Livre') ?>
          </div>
          <!-- XP Badge -->
          <div class="absolute top-2 <?= $dir==='rtl'?'left-2':'right-2' ?> bg-yellow-400 text-yellow-900 text-xs px-2 py-1 rounded-lg font-bold">
            +<?= $lesson['xp_reward'] ?> XP
          </div>
          <?php if($completed): ?>
          <div class="absolute inset-0 bg-green-900/40 flex items-center justify-center">
            <div class="bg-green-500 rounded-full p-2">
              <i data-lucide="check" class="w-8 h-8 text-white"></i>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <!-- Info -->
        <div class="p-4">
          <h3 class="font-bold text-gray-900 mb-1 line-clamp-2 text-sm"><?= htmlspecialchars($lesson['title']) ?></h3>
          <?php if($lesson['duration']): ?>
          <p class="text-gray-400 text-xs mb-3 flex items-center gap-1">
            <i data-lucide="clock" class="w-3 h-3"></i>
            <?= $lesson['duration'] ?> min
          </p>
          <?php endif; ?>

          <div class="flex gap-2">
            <!-- Open Lesson -->
            <a href="<?= htmlspecialchars($lesson['url']) ?>" target="_blank" rel="noopener"
               class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold py-2 px-3 rounded-lg transition flex items-center justify-center gap-1">
              <i data-lucide="<?= $lesson['type']==='video'?'play':'external-link' ?>" class="w-3.5 h-3.5"></i>
              <?= $lesson['type']==='video' ? t('watch_video') : ($lesson['type']==='pdf'?t('open_pdf'):t('open_book')) ?>
            </a>

            <!-- Complete Button -->
            <?php if(!$completed): ?>
            <button onclick="completeLesson(<?= $lesson['id'] ?>, this)"
                    class="complete-btn bg-gray-100 hover:bg-green-500 hover:text-white text-gray-600 text-xs font-semibold py-2 px-3 rounded-lg transition flex items-center gap-1">
              <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
              <?= t('complete_lesson') ?>
            </button>
            <?php else: ?>
            <span class="bg-green-100 text-green-700 text-xs font-semibold py-2 px-3 rounded-lg flex items-center gap-1">
              <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
              <?= t('completed') ?>
            </span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>

      <?php if(empty($lessons)): ?>
      <div class="col-span-3 text-center py-16">
        <i data-lucide="inbox" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
        <p class="text-gray-400"><?= t('no_results') ?></p>
      </div>
      <?php endif; ?>
    </div>

    <?php elseif($tab === 'leaderboard'): ?>
    <!-- ═══ LEADERBOARD TAB ═══ -->
    <div class="max-w-2xl mx-auto">
      <div class="card p-6">
        <h2 class="text-xl font-black text-gray-900 mb-1 flex items-center gap-2">
          <i data-lucide="trophy" class="w-6 h-6 text-yellow-500"></i>
          <?= t('top_students') ?>
        </h2>
        <p class="text-gray-500 text-sm mb-6"><?= htmlspecialchars($gradeName) ?></p>

        <div class="space-y-3">
          <?php foreach($leaderboard as $pos => $student): ?>
          <?php
            $medals = ['🥇','🥈','🥉'];
            $medal  = $medals[$pos] ?? '🏅';
            $isMe   = $student['id'] == $userId;
          ?>
          <div class="flex items-center gap-4 p-4 rounded-2xl transition <?= $isMe ? 'bg-blue-50 border-2 border-blue-400' : 'bg-gray-50 hover:bg-gray-100' ?>">
            <div class="text-2xl w-8 text-center"><?= $medal ?></div>
            <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center font-bold text-white text-sm"
                 style="background:<?= levelBadgeColor($student['level']) ?>">
              <?= mb_strtoupper(mb_substr($student['full_name'],0,1)) ?>
            </div>
            <div class="flex-1 min-w-0">
              <div class="font-bold text-gray-900 text-sm truncate">
                <?= htmlspecialchars($student['full_name']) ?>
                <?php if($isMe): ?><span class="text-blue-500 font-normal text-xs">(<?= t('welcome') ?>)</span><?php endif; ?>
              </div>
              <div class="text-gray-500 text-xs"><?= t('level') ?> <?= $student['level'] ?></div>
            </div>
            <div class="font-black text-yellow-600 text-sm"><?= number_format($student['xp_points']) ?> XP</div>
          </div>
          <?php endforeach; ?>

          <?php if(empty($leaderboard)): ?>
          <p class="text-center text-gray-400 py-8"><?= t('no_results') ?></p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <?php elseif($tab === 'achievements'): ?>
    <!-- ═══ ACHIEVEMENTS TAB ═══ -->
    <div class="max-w-3xl mx-auto">
      <div class="card p-6">
        <h2 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2">
          <i data-lucide="star" class="w-6 h-6 text-yellow-500"></i>
          <?= t('achievements') ?>
        </h2>
        <?php
        $completionCount = $db->prepare("SELECT COUNT(*) FROM user_lesson_completions WHERE user_id=?");
        $completionCount->execute([$userId]);
        $cc = (int)$completionCount->fetchColumn();

        $achievements = [
          ['🌱','Première Leçon',   'Terminez votre 1ère leçon',   $cc>=1,   1],
          ['📚','Élève Assidu',     'Terminez 5 leçons',           $cc>=5,   5],
          ['🔥','En Feu',          'Terminez 10 leçons',          $cc>=10, 10],
          ['⚡','Niveau 3',        'Atteignez le Niveau 3',       $userLevel>=3, null],
          ['💎','Niveau 5',        'Atteignez le Niveau 5',       $userLevel>=5, null],
          ['🏆','Maître MAZAR',    '500 XP',                     $userXP>=500, null],
          ['🌟','Expert MAZAR',   '1000 XP',                    $userXP>=1000, null],
          ['👑','Légende MAZAR',  '5000 XP',                    $userXP>=5000, null],
        ];
        ?>
        <div class="grid sm:grid-cols-2 gap-4">
          <?php foreach($achievements as [$emoji,$title,$desc,$unlocked,$_]): ?>
          <div class="flex items-center gap-4 p-4 rounded-2xl border <?= $unlocked ? 'border-yellow-300 bg-yellow-50' : 'border-gray-200 bg-gray-50 opacity-60' ?>">
            <div class="text-3xl flex-shrink-0 <?= $unlocked ? '' : 'grayscale' ?>"><?= $emoji ?></div>
            <div>
              <div class="font-bold text-gray-900 text-sm"><?= $title ?></div>
              <div class="text-gray-500 text-xs"><?= $desc ?></div>
              <?php if($unlocked): ?>
              <div class="text-green-600 text-xs font-semibold mt-1">✓ Débloqué</div>
              <?php else: ?>
              <div class="text-gray-400 text-xs mt-1">🔒 Verrouillé</div>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
<script src="../assets/js/xp-system.js"></script>
<script>
  lucide.createIcons();

  function mazarDashboard() {
    return {
      init() {
        <?php if($welcome): ?>
        showToast('🎉 Bienvenue sur MAZAR ! Bonne chance !', 'success');
        <?php endif; ?>
      }
    };
  }

  // Pre-set current XP values for XP system JS
  window.MAZAR_XP    = <?= $userXP ?>;
  window.MAZAR_LEVEL = <?= $userLevel ?>;
  window.MAZAR_CSRF  = '<?= csrfToken() ?>';
  window.MAZAR_AJAX  = '../ajax/complete_lesson.php';
</script>
</body>
</html>
