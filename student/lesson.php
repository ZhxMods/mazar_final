<?php
// ============================================================
//  MAZAR — student/lesson.php
//  Anti-cheat: progress bar linked to duration before XP
// ============================================================
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth_check.php';

$lang   = getCurrentLang();
$dir    = getDirection();
$userId = (int)$_SESSION[SESS_USER_ID];

$lessonId = (int)($_GET['id'] ?? 0);
if (!$lessonId) redirect('dashboard.php');

$db = getDB();

// Fetch lesson with full details
$stmt = $db->prepare(
    "SELECT l.*,
            l.title_{$lang}  AS title_loc,
            l.desc_{$lang}   AS desc_loc,
            lv.name_{$lang}  AS level_name,
            s.name_{$lang}   AS subject_name,
            s.icon           AS subject_icon,
            s.color          AS subject_color,
            s.id             AS sid,
            s.level_id       AS slevel_id,
            IF(ulc.id IS NOT NULL, 1, 0) AS completed
     FROM lessons l
     JOIN levels   lv ON lv.id = l.level_id
     JOIN subjects s   ON s.id  = l.subject_id
     LEFT JOIN user_lesson_completions ulc
           ON ulc.lesson_id = l.id AND ulc.user_id = ?
     WHERE l.id = ? AND l.published = 1"
);
$stmt->execute([$userId, $lessonId]);
$lesson = $stmt->fetch();

if (!$lesson) redirect('dashboard.php');

$title     = $lesson['title_loc'] ?: $lesson['title_fr'];
$desc      = $lesson['desc_loc']  ?: $lesson['desc_fr'];
$completed = (bool)$lesson['completed'];

// ── Anti-cheat: record session start time ─────────────────────
$sessionKey = 'ls_start_' . $userId . '_' . $lessonId;
if (empty($_SESSION[$sessionKey])) {
    $_SESSION[$sessionKey] = time();
}

// ── Required seconds to unlock XP ────────────────────────────
// Duration stored in minutes. Require 80% of that, minimum 45s.
$durationMins = (int)$lesson['duration'];
$durationSecs = $durationMins * 60;
// For videos with no duration, YouTube API will provide actual duration
$requiredSecs = $durationSecs > 0 ? max(45, (int)($durationSecs * 0.80)) : 45;
// Encode for JS
$requiredSecsJs = $requiredSecs;
$durationMinsFmt = $durationMins;

// ── Media detection ───────────────────────────────────────────
$ytId        = youtubeId($lesson['url']);
$isYoutube   = (bool)$ytId;
$isMediaFire = (strpos($lesson['url'], 'mediafire.com') !== false);
$_urlLower   = strtolower($lesson['url']);
$isDirectPdf = !$isMediaFire && (
    substr($_urlLower, -4) === '.pdf' ||
    strpos($_urlLower, '.pdf?') !== false
);

$embedUrl = '';
if ($isDirectPdf) {
    $embedUrl = "https://docs.google.com/viewer?url=" . urlencode($lesson['url']) . "&embedded=true";
}

// Thumbnail
$thumb = $lesson['thumbnail'];
if (!$thumb && $isYoutube) {
    $thumb = "https://img.youtube.com/vi/{$ytId}/maxresdefault.jpg";
}

// ── Quiz for this lesson ──────────────────────────────────────
$quizStmt = $db->prepare(
    "SELECT id, title_{$lang} AS qtitle, title_fr AS qtitle_fr,
            (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = quizzes.id) AS q_count
     FROM quizzes WHERE lesson_id = ? LIMIT 1"
);
$quizStmt->execute([$lessonId]);
$quiz = $quizStmt->fetch();

// ── Related lessons (same subject) ───────────────────────────
$relStmt = $db->prepare(
    "SELECT l.id, l.title_{$lang} AS title, l.title_fr, l.type, l.thumbnail, l.url, l.duration,
            IF(ulc.id IS NOT NULL, 1, 0) AS completed
     FROM lessons l
     LEFT JOIN user_lesson_completions ulc ON ulc.lesson_id = l.id AND ulc.user_id = ?
     WHERE l.subject_id = ? AND l.id != ? AND l.published = 1
     ORDER BY l.order_num ASC
     LIMIT 6"
);
$relStmt->execute([$userId, $lesson['sid'], $lessonId]);
$related = $relStmt->fetchAll();

// ── User state ────────────────────────────────────────────────
$userXP      = (int)$_SESSION[SESS_XP];
$userLevel   = (int)$_SESSION[SESS_LEVEL];
$progressPct = xpProgressPercent($userXP, $userLevel);

$_typeLabels = ['video' => 'Vidéo', 'pdf' => 'PDF',    'book' => 'Livre'];
$_typeIcons  = ['video' => 'play-circle', 'pdf' => 'file-text', 'book' => 'book-open'];
$_typeColors = ['video' => '#3B82F6', 'pdf' => '#10B981', 'book' => '#8B5CF6'];
$typeLabel   = isset($_typeLabels[$lesson['type']]) ? $_typeLabels[$lesson['type']] : 'Cours';
$typeIcon    = isset($_typeIcons[$lesson['type']])  ? $_typeIcons[$lesson['type']]  : 'book';
$typeColor   = isset($_typeColors[$lesson['type']]) ? $_typeColors[$lesson['type']] : '#6B7280';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title) ?> — <?= t('site_name') ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
  <link rel="stylesheet" href="../assets/css/xp-animations.css">
  <style>
    * { box-sizing: border-box; }
    body {
      font-family: <?= $lang === 'ar' ? "'Cairo'" : "'Poppins'" ?>, sans-serif;
      background: #f1f5f9;
      color: #1e293b;
    }

    /* ── Top Navigation ── */
    .top-nav {
      background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%);
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 2px 20px rgba(30,58,138,.35);
    }

    /* ── Media Container ── */
    .media-wrapper {
      position: relative;
      width: 100%;
      background: #000;
      border-radius: 1.25rem;
      overflow: hidden;
      box-shadow: 0 20px 60px rgba(0,0,0,.25);
    }
    .media-wrapper.video-ratio {
      padding-bottom: 56.25%;
      height: 0;
    }
    .media-wrapper iframe,
    .media-wrapper #yt-player {
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      border: none;
    }
    .media-thumb {
      width: 100%;
      aspect-ratio: 16/9;
      object-fit: cover;
      display: block;
    }
    .media-placeholder {
      aspect-ratio: 16/9;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #1e3a8a, #1d4ed8);
      border-radius: 1.25rem;
    }

    /* ── Cards ── */
    .card { background: #fff; border-radius: 1rem; box-shadow: 0 1px 4px rgba(0,0,0,.07); }

    /* ── PROGRESS BAR SYSTEM ── */
    .progress-section {
      background: linear-gradient(135deg, #f8fafc, #eff6ff);
      border: 1.5px solid #dbeafe;
      border-radius: .875rem;
      padding: 1rem;
      margin-bottom: 1rem;
    }
    .progress-section.completed-state {
      background: linear-gradient(135deg, #f0fdf4, #dcfce7);
      border-color: #86efac;
    }

    .progress-track {
      width: 100%;
      height: 10px;
      background: #e2e8f0;
      border-radius: 99px;
      overflow: hidden;
      position: relative;
      box-shadow: inset 0 1px 3px rgba(0,0,0,.1);
    }
    .progress-fill {
      height: 100%;
      border-radius: 99px;
      transition: width .5s cubic-bezier(.4,0,.2,1);
      position: relative;
      overflow: hidden;
    }
    .progress-fill::after {
      content: '';
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,.4) 50%, transparent 100%);
      animation: progressShimmer 1.5s ease-in-out infinite;
    }
    @keyframes progressShimmer {
      0%   { transform: translateX(-100%); }
      100% { transform: translateX(100%); }
    }
    .progress-fill.active {
      background: linear-gradient(90deg, #3b82f6, #1d4ed8);
      box-shadow: 0 0 8px rgba(59,130,246,.5);
    }
    .progress-fill.done {
      background: linear-gradient(90deg, #10b981, #059669);
      box-shadow: 0 0 8px rgba(16,185,129,.5);
    }
    .progress-fill.done::after { animation: none; }

    /* ── Complete Button ── */
    .complete-btn-main {
      width: 100%;
      padding: .9rem 1.5rem;
      border-radius: .875rem;
      border: none;
      cursor: pointer;
      font-family: inherit;
      font-size: .9rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: .5rem;
      transition: all .25s cubic-bezier(.34,1.56,.64,1);
      position: relative;
      overflow: hidden;
    }
    .complete-btn-main.locked {
      background: #f1f5f9;
      color: #94a3b8;
      cursor: not-allowed;
      border: 2px dashed #cbd5e1;
    }
    .complete-btn-main.pending {
      background: linear-gradient(135deg, #f59e0b, #d97706);
      color: #fff;
      box-shadow: 0 4px 20px rgba(245,158,11,.35);
    }
    .complete-btn-main.pending:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 8px 28px rgba(245,158,11,.5);
    }
    .complete-btn-main.pending::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(255,255,255,.15), transparent);
      border-radius: inherit;
    }
    .complete-btn-main.done {
      background: linear-gradient(135deg, #10b981, #059669);
      color: #fff;
      cursor: default;
      box-shadow: 0 4px 16px rgba(16,185,129,.35);
    }

    /* Lock icon pulse */
    @keyframes lockPulse {
      0%,100% { opacity: .5; }
      50%      { opacity: 1; }
    }
    .lock-pulse { animation: lockPulse 2s ease-in-out infinite; }

    /* ── XP Bars ── */
    .xp-bar { height: 6px; border-radius: 99px; background: #e2e8f0; overflow: hidden; }
    .xp-bar-inner { height: 100%; border-radius: 99px; background: linear-gradient(90deg, #f59e0b, #fbbf24); transition: width .8s ease; }

    /* ── Time display ── */
    .time-pill {
      display: inline-flex;
      align-items: center;
      gap: .3rem;
      font-size: .72rem;
      font-weight: 700;
      font-variant-numeric: tabular-nums;
      padding: .2rem .6rem;
      border-radius: 999px;
      letter-spacing: .02em;
    }
    .time-pill.waiting {
      background: #dbeafe;
      color: #1e40af;
    }
    .time-pill.running {
      background: #fef3c7;
      color: #92400e;
    }
    .time-pill.ready {
      background: #d1fae5;
      color: #065f46;
    }

    /* ── Related lesson cards ── */
    .related-card {
      display: flex;
      align-items: center;
      gap: .75rem;
      padding: .75rem;
      border-radius: .875rem;
      border: 1.5px solid #e5e7eb;
      background: #fff;
      text-decoration: none;
      color: inherit;
      transition: all .18s;
    }
    .related-card:hover {
      border-color: #bfdbfe;
      background: #eff6ff;
      transform: translateX(<?= $dir === 'rtl' ? '-3px' : '3px' ?>);
    }
    .related-thumb {
      width: 72px; height: 48px;
      border-radius: .5rem;
      object-fit: cover;
      flex-shrink: 0;
      background: #e5e7eb;
    }
    .related-thumb-placeholder {
      width: 72px; height: 48px;
      border-radius: .5rem;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #dbeafe, #ede9fe);
    }

    /* ── Type badge ── */
    .type-badge {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      font-size: .75rem;
      font-weight: 700;
      padding: .3rem .75rem;
      border-radius: 999px;
    }

    /* ── Open external ── */
    .open-external {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      padding: .75rem 1.5rem;
      border-radius: .875rem;
      font-weight: 700;
      font-size: .9rem;
      transition: all .2s;
      text-decoration: none;
      box-shadow: 0 4px 16px rgba(59,130,246,.25);
    }
    .open-external:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 28px rgba(59,130,246,.35);
    }

    /* ── Description ── */
    .desc-content {
      font-size: .9rem;
      line-height: 1.8;
      color: #475569;
    }

    /* ── FAB ── */
    #fab-btn {
      position: fixed;
      bottom: 1.5rem;
      <?= $dir === 'rtl' ? 'left' : 'right' ?>: 1.5rem;
      z-index: 9000;
      width: 54px; height: 54px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
      box-shadow: 0 0 0 4px rgba(29,78,216,.18), 0 8px 28px rgba(30,58,138,.45);
      text-decoration: none;
      transition: transform .22s cubic-bezier(.34,1.56,.64,1), box-shadow .22s;
    }
    #fab-btn:hover {
      transform: scale(1.12) translateY(-2px);
      box-shadow: 0 0 0 6px rgba(29,78,216,.22), 0 16px 40px rgba(30,58,138,.55);
    }
    #fab-btn svg { width: 22px; height: 22px; color: #fff; }
    #fab-btn::before {
      content: '';
      position: absolute;
      inset: -4px;
      border-radius: 50%;
      border: 2px solid rgba(29,78,216,.4);
      animation: fabPulse 2.6s ease-out infinite;
    }
    @keyframes fabPulse {
      0%  { transform: scale(1);    opacity: .8; }
      70% { transform: scale(1.55); opacity: 0;  }
      100%{ transform: scale(1.55); opacity: 0;  }
    }
    .fab-online {
      position: absolute; top: 2px; right: 2px;
      width: 12px; height: 12px;
      background: #10b981; border-radius: 50%;
      border: 2px solid #fff;
    }

    /* ── Animate in ── */
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(16px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .fade-up    { animation: fadeUp .4s ease both; }
    .fade-up-d1 { animation-delay: .1s; }
    .fade-up-d2 { animation-delay: .2s; }
    .fade-up-d3 { animation-delay: .3s; }

    /* ── Toast ── */
    #toast-container { position: fixed; top: 20px; <?= $dir === 'rtl' ? 'left' : 'right' ?>: 20px; z-index: 9999; }

    @media (max-width: 640px) {
      .top-nav { padding: .75rem 1rem; }
    }
  </style>
</head>
<body>

<div id="toast-container" class="space-y-2"></div>

<!-- ══ TOP NAV ══ -->
<nav class="top-nav px-4 sm:px-6 py-3 flex items-center gap-3">
  <a href="dashboard.php?tab=lessons&subject=<?= $lesson['sid'] ?>"
     class="flex items-center gap-1.5 text-white/80 hover:text-white text-sm font-semibold transition bg-white/10 hover:bg-white/20 px-3 py-2 rounded-xl flex-shrink-0">
    <i data-lucide="arrow-<?= $dir === 'rtl' ? 'right' : 'left' ?>" class="w-4 h-4"></i>
    <span class="hidden sm:inline">Retour</span>
  </a>

  <div class="flex items-center gap-2 text-white/70 text-xs sm:text-sm flex-1 min-w-0 overflow-hidden">
    <span class="text-white/90 font-semibold truncate hidden sm:inline"><?= htmlspecialchars($lesson['subject_name']) ?></span>
    <i data-lucide="chevron-right" class="w-3.5 h-3.5 flex-shrink-0 hidden sm:inline"></i>
    <span class="text-white font-bold truncate"><?= htmlspecialchars($title) ?></span>
  </div>

  <div class="flex items-center gap-2 bg-yellow-400/20 border border-yellow-400/30 rounded-xl px-3 py-1.5 flex-shrink-0">
    <i data-lucide="zap" class="w-3.5 h-3.5 text-yellow-300"></i>
    <span class="text-yellow-200 font-bold text-xs sm:text-sm"><span id="header-xp"><?= $userXP ?></span> XP</span>
  </div>
</nav>

<!-- ══ MAIN LAYOUT ══ -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
  <div class="flex flex-col lg:flex-row gap-6">

    <!-- ── LEFT COLUMN — Media + Info ── -->
    <div class="flex-1 min-w-0">

      <!-- MEDIA PLAYER -->
      <div class="fade-up">
        <?php if ($isYoutube): ?>
        <!-- YouTube IFrame API Player (tracks actual watch time) -->
        <div class="media-wrapper video-ratio">
          <div id="yt-player"></div>
        </div>

        <?php elseif ($isDirectPdf): ?>
        <div class="media-wrapper video-ratio">
          <iframe
            src="<?= htmlspecialchars($embedUrl) ?>"
            title="<?= htmlspecialchars($title) ?>"
            loading="lazy">
          </iframe>
        </div>

        <?php elseif ($lesson['type'] === 'pdf' || $lesson['type'] === 'book'): ?>
        <div class="media-placeholder" style="border-radius:1.25rem; box-shadow:0 20px 60px rgba(0,0,0,.2);">
          <?php if ($thumb): ?>
            <img src="<?= htmlspecialchars($thumb) ?>" alt="" class="media-thumb" style="border-radius:1.25rem;">
          <?php else: ?>
            <div style="text-align:center; padding:3rem 2rem;">
              <div style="width:80px;height:80px;border-radius:50%;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;">
                <i data-lucide="<?= $typeIcon ?>" style="width:40px;height:40px;color:#fff;"></i>
              </div>
              <div style="color:#fff;font-size:1.1rem;font-weight:700;margin-bottom:.5rem;"><?= htmlspecialchars($title) ?></div>
              <div style="color:rgba(255,255,255,.7);font-size:.85rem;"><?= $typeLabel ?></div>
            </div>
          <?php endif; ?>
        </div>
        <div class="mt-4 flex gap-3 flex-wrap">
          <a href="<?= htmlspecialchars($lesson['url']) ?>" target="_blank" rel="noopener"
             class="open-external bg-blue-600 hover:bg-blue-700 text-white">
            <i data-lucide="external-link" class="w-5 h-5"></i>
            Ouvrir le <?= $typeLabel ?>
          </a>
        </div>

        <?php else: ?>
        <div class="media-placeholder" style="border-radius:1.25rem;box-shadow:0 20px 60px rgba(0,0,0,.2);">
          <?php if ($thumb): ?>
            <img src="<?= htmlspecialchars($thumb) ?>" alt="" class="media-thumb" style="border-radius:1.25rem;">
          <?php else: ?>
            <div style="text-align:center;padding:3rem;">
              <i data-lucide="play-circle" style="width:64px;height:64px;color:rgba(255,255,255,.6);"></i>
            </div>
          <?php endif; ?>
        </div>
        <div class="mt-4">
          <a href="<?= htmlspecialchars($lesson['url']) ?>" target="_blank" rel="noopener"
             class="open-external bg-blue-600 hover:bg-blue-700 text-white">
            <i data-lucide="external-link" class="w-5 h-5"></i>
            Ouvrir le contenu
          </a>
        </div>
        <?php endif; ?>
      </div>

      <!-- ── LESSON INFO CARD ── -->
      <div class="card mt-5 p-5 sm:p-6 fade-up fade-up-d1">
        <div class="flex items-center gap-2 mb-3 flex-wrap">
          <span class="type-badge text-white" style="background:<?= $typeColor ?>20; color:<?= $typeColor ?>; border:1px solid <?= $typeColor ?>30;">
            <i data-lucide="<?= $typeIcon ?>" style="width:12px;height:12px;"></i>
            <?= $typeLabel ?>
          </span>
          <span class="type-badge bg-blue-50 text-blue-700" style="border:1px solid #dbeafe;">
            <i data-lucide="layers" style="width:12px;height:12px;"></i>
            <?= htmlspecialchars($lesson['subject_name']) ?>
          </span>
          <span class="type-badge bg-gray-50 text-gray-600" style="border:1px solid #e5e7eb;">
            <i data-lucide="graduation-cap" style="width:12px;height:12px;"></i>
            <?= htmlspecialchars($lesson['level_name']) ?>
          </span>
          <?php if ($lesson['duration']): ?>
          <span class="type-badge bg-gray-50 text-gray-600" style="border:1px solid #e5e7eb;">
            <i data-lucide="clock" style="width:12px;height:12px;"></i>
            <?= $lesson['duration'] ?> min
          </span>
          <?php endif; ?>
          <span class="type-badge bg-yellow-50 text-yellow-700" style="border:1px solid #fde68a;">
            <i data-lucide="zap" style="width:12px;height:12px;"></i>
            +<?= $lesson['xp_reward'] ?> XP
          </span>
        </div>

        <h1 class="text-xl sm:text-2xl font-black text-gray-900 mb-1 leading-tight">
          <?= htmlspecialchars($title) ?>
        </h1>
        <?php if ($lang !== 'fr' && $lesson['title_fr'] && $lesson['title_fr'] !== $title): ?>
        <p class="text-gray-400 text-sm mb-4"><?= htmlspecialchars($lesson['title_fr']) ?></p>
        <?php endif; ?>

        <?php if ($desc): ?>
        <div class="mt-4 pt-4 border-t border-gray-100">
          <h2 class="text-sm font-bold text-gray-700 mb-2 flex items-center gap-2">
            <i data-lucide="align-left" class="w-4 h-4 text-blue-500"></i>
            Description
          </h2>
          <div class="desc-content"><?= nl2br(htmlspecialchars($desc)) ?></div>
        </div>
        <?php endif; ?>

        <div class="mt-4 pt-4 border-t border-gray-100 flex items-center gap-2">
          <a href="<?= htmlspecialchars($lesson['url']) ?>" target="_blank" rel="noopener"
             class="text-blue-600 hover:text-blue-700 text-sm font-semibold flex items-center gap-1.5 transition">
            <i data-lucide="external-link" class="w-4 h-4"></i>
            Ouvrir sur <?= $isYoutube ? 'YouTube' : ($isMediaFire ? 'MediaFire' : 'le site externe') ?>
          </a>
        </div>
      </div>

    </div><!-- /left column -->

    <!-- ── RIGHT COLUMN — Sidebar ── -->
    <div class="lg:w-80 xl:w-96 flex-shrink-0 space-y-4">

      <!-- ════════════════════════════════════
           COMPLETE LESSON CARD with Progress
      ════════════════════════════════════ -->
      <div class="card p-5 fade-up fade-up-d1" id="complete-card">
        <h3 class="font-bold text-gray-800 text-sm mb-4 flex items-center gap-2">
          <i data-lucide="shield-check" class="w-4 h-4 text-blue-500"></i>
          Progression de la leçon
        </h3>

        <?php if ($completed): ?>
        <!-- ── Already completed ── -->
        <div class="progress-section completed-state">
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-green-700">Leçon terminée ✓</span>
            <span class="time-pill ready">
              <i data-lucide="check-circle" style="width:10px;height:10px;"></i>
              Complet
            </span>
          </div>
          <div class="progress-track">
            <div class="progress-fill done" style="width:100%;"></div>
          </div>
          <p class="text-green-600 text-xs mt-2 font-medium">
            ✅ +<?= $lesson['xp_reward'] ?> XP déjà gagnés
          </p>
        </div>
        <button class="complete-btn-main done" disabled>
          <i data-lucide="check-circle-2" class="w-5 h-5"></i>
          Leçon terminée !
        </button>

        <?php else: ?>
        <!-- ── Progress tracking ── -->
        <div class="progress-section" id="progress-section">
          <!-- Header row -->
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-gray-600" id="progress-label">
              <?= $isYoutube ? 'Temps de visionnage' : ($durationMins > 0 ? 'Temps de lecture' : 'Présence sur la page') ?>
            </span>
            <span class="time-pill waiting" id="time-pill">
              <i data-lucide="clock" style="width:10px;height:10px;" id="time-pill-icon"></i>
              <span id="time-pill-text">En attente...</span>
            </span>
          </div>

          <!-- Progress track -->
          <div class="progress-track" style="margin-bottom:.6rem;">
            <div class="progress-fill" id="lesson-progress-bar" style="width:0%"></div>
          </div>

          <!-- Time counters -->
          <div class="flex justify-between items-center text-xs text-gray-400">
            <span>
              <span id="elapsed-display">0:00</span>
              <span class="text-gray-300"> / </span>
              <span id="required-display"><?= gmdate('G:i', $requiredSecs) ?></span>
            </span>
            <span id="remaining-hint" class="font-medium text-blue-500">
              <?= $durationMins > 0 ? 'Requis : ' . $durationMins . ' min' : 'Requis : 45 sec' ?>
            </span>
          </div>

          <!-- Hint text -->
          <p class="text-xs text-gray-400 mt-2 leading-relaxed" id="progress-hint-text">
            <?php if ($isYoutube): ?>
              Regardez la vidéo pour débloquer les <strong class="text-yellow-600">+<?= $lesson['xp_reward'] ?> XP</strong>
            <?php else: ?>
              Restez sur cette page pour débloquer les <strong class="text-yellow-600">+<?= $lesson['xp_reward'] ?> XP</strong>
            <?php endif; ?>
          </p>
        </div>

        <!-- Complete Button (locked until progress done) -->
        <button id="complete-btn-main"
                class="complete-btn-main locked"
                disabled
                onclick="completeLessonPage(<?= $lessonId ?>)">
          <i data-lucide="lock" class="w-5 h-5 lock-pulse" id="btn-icon"></i>
          <span id="btn-text">Terminer la leçon</span>
        </button>
        <p class="text-gray-400 text-xs text-center mt-2" id="btn-subtext">
          Regardez la leçon pour débloquer ce bouton
        </p>
        <?php endif; ?>

        <!-- XP Progress Bar -->
        <div class="mt-4 pt-4 border-t border-gray-100">
          <div class="flex justify-between text-xs text-gray-500 mb-1.5">
            <span>Niveau <?= $userLevel ?></span>
            <span id="xp-display" class="font-semibold text-gray-700"><?= $userXP ?> XP</span>
            <span>Niveau <?= $userLevel + 1 ?></span>
          </div>
          <div class="xp-bar">
            <div class="xp-bar-inner" id="xp-bar-fill" style="width:<?= $progressPct ?>%"></div>
          </div>
          <p class="text-right text-xs text-gray-400 mt-1"><?= round($progressPct) ?>%</p>
        </div>
      </div>

      <!-- Quiz card -->
      <?php if ($quiz): ?>
      <div class="card p-5 fade-up fade-up-d2">
        <div class="flex items-center gap-2 mb-3">
          <div class="w-9 h-9 rounded-xl bg-purple-100 flex items-center justify-center flex-shrink-0">
            <i data-lucide="help-circle" class="w-5 h-5 text-purple-600"></i>
          </div>
          <div>
            <h3 class="font-bold text-gray-800 text-sm">Quiz associé</h3>
            <p class="text-gray-400 text-xs"><?= $quiz['q_count'] ?> question<?= $quiz['q_count'] != 1 ? 's' : '' ?></p>
          </div>
        </div>
        <p class="text-gray-700 text-sm font-semibold mb-3"><?= htmlspecialchars($quiz['qtitle'] ?: $quiz['qtitle_fr']) ?></p>
        <a href="quiz.php?id=<?= $quiz['id'] ?>"
           class="flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-bold py-2.5 px-4 rounded-xl transition">
          <i data-lucide="play-circle" class="w-4 h-4"></i>
          Commencer le quiz
          <span class="bg-yellow-400 text-yellow-900 text-xs px-1.5 py-0.5 rounded-md font-black">+50 XP</span>
        </a>
      </div>
      <?php endif; ?>

      <!-- Related lessons -->
      <?php if (!empty($related)): ?>
      <div class="card p-5 fade-up fade-up-d3">
        <h3 class="font-bold text-gray-800 text-sm mb-3 flex items-center gap-2">
          <i data-lucide="list" class="w-4 h-4 text-blue-500"></i>
          Autres leçons — <?= htmlspecialchars($lesson['subject_name']) ?>
        </h3>
        <div class="space-y-2">
          <?php foreach ($related as $rel):
            $relTitle = $rel['title'] ?: $rel['title_fr'];
            $relYt    = youtubeId($rel['url']);
            $relThumb = $rel['thumbnail'] ?: ($relYt ? "https://img.youtube.com/vi/{$relYt}/hqdefault.jpg" : '');
            $_ri = ['video'=>'play','pdf'=>'file-text','book'=>'book-open'];
            $_rc = ['video'=>'#3B82F6','pdf'=>'#10B981','book'=>'#8B5CF6'];
            $relIcon  = isset($_ri[$rel['type']]) ? $_ri[$rel['type']] : 'book';
            $relColor = isset($_rc[$rel['type']]) ? $_rc[$rel['type']] : '#6B7280';
          ?>
          <a href="lesson.php?id=<?= $rel['id'] ?>" class="related-card">
            <?php if ($relThumb): ?>
            <img src="<?= htmlspecialchars($relThumb) ?>" alt="" class="related-thumb">
            <?php else: ?>
            <div class="related-thumb-placeholder">
              <i data-lucide="<?= $relIcon ?>" style="width:18px;height:18px;color:<?= $relColor ?>"></i>
            </div>
            <?php endif; ?>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-gray-800 leading-tight truncate"><?= htmlspecialchars($relTitle) ?></p>
              <div class="flex items-center gap-2 mt-1">
                <?php if ($rel['duration']): ?>
                <span class="text-gray-400 text-xs"><?= $rel['duration'] ?>min</span>
                <?php endif; ?>
                <?php if ($rel['completed']): ?>
                <span class="text-green-600 text-xs font-semibold flex items-center gap-0.5">
                  <i data-lucide="check-circle-2" style="width:11px;height:11px;"></i> Fait
                </span>
                <?php endif; ?>
              </div>
            </div>
            <i data-lucide="chevron-<?= $dir === 'rtl' ? 'left' : 'right' ?>" class="w-4 h-4 text-gray-300 flex-shrink-0"></i>
          </a>
          <?php endforeach; ?>
        </div>
        <a href="dashboard.php?tab=lessons&subject=<?= $lesson['sid'] ?>"
           class="mt-3 flex items-center justify-center gap-1 text-blue-600 hover:text-blue-700 text-xs font-semibold transition">
          Voir toutes les leçons
          <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
        </a>
      </div>
      <?php endif; ?>

    </div><!-- /right column -->
  </div>
</div>

<!-- MAZAR AI FAB -->
<a href="mazar-ai.php" id="fab-btn" aria-label="MAZAR AI" style="position:relative;">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
    <path d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/>
    <path d="M18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z"/>
  </svg>
  <span class="fab-online"></span>
</a>

<!-- ============================================================
     SCRIPTS
============================================================ -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
<script src="../assets/js/xp-system.js"></script>

<script>
/* ── Config injected from PHP ─────────────────────────── */
window.MAZAR_XP        = <?= $userXP ?>;
window.MAZAR_LEVEL     = <?= $userLevel ?>;
window.MAZAR_CSRF      = '<?= csrfToken() ?>';
window.MAZAR_AJAX      = '../ajax/complete_lesson.php';

var IS_YOUTUBE    = <?= $isYoutube ? 'true' : 'false' ?>;
var YT_VIDEO_ID   = '<?= $ytId ?>';
var REQUIRED_SECS = <?= $requiredSecsJs ?>;
var ALREADY_DONE  = <?= $completed ? 'true' : 'false' ?>;
var LESSON_TYPE   = '<?= $lesson['type'] ?>';
var LESSON_MINS   = <?= $durationMins ?>;

/* ── State ─────────────────────────────────────────────── */
var elapsedSecs   = 0;       // total qualifying seconds accumulated
var progressReady = false;   // unlocked?
var trackInterval = null;    // setInterval handle
var ytPlayer      = null;    // YouTube player object
var ytDuration    = 0;       // actual video duration in seconds

/* ── DOM helpers ──────────────────────────────────────── */
function qs(id) { return document.getElementById(id); }

/* ── Format seconds as M:SS ──────────────────────────── */
function fmtTime(s) {
  s = Math.floor(s);
  var m = Math.floor(s / 60);
  var sec = s % 60;
  return m + ':' + (sec < 10 ? '0' : '') + sec;
}

/* ── Update progress bar UI ──────────────────────────── */
function updateProgressUI() {
  if (ALREADY_DONE) return;

  var pct = Math.min(100, (elapsedSecs / REQUIRED_SECS) * 100);
  var bar = qs('lesson-progress-bar');
  var pill = qs('time-pill');
  var pillTxt = qs('time-pill-text');
  var elapsedEl = qs('elapsed-display');
  var btnIcon = qs('btn-icon');
  var btnText = qs('btn-text');
  var btnSub  = qs('btn-subtext');
  var btn     = qs('complete-btn-main');

  if (bar)       bar.style.width = pct + '%';
  if (elapsedEl) elapsedEl.textContent = fmtTime(elapsedSecs);

  if (pct >= 100 && !progressReady) {
    /* ── UNLOCK ── */
    progressReady = true;
    if (trackInterval) clearInterval(trackInterval);

    if (bar)      { bar.classList.remove('active'); bar.classList.add('done'); }
    if (pill)     { pill.className = 'time-pill ready'; }
    if (pillTxt)  pillTxt.textContent = 'Prêt !';

    // Update progress section background
    var section = qs('progress-section');
    if (section) {
      section.style.background = 'linear-gradient(135deg, #f0fdf4, #dcfce7)';
      section.style.borderColor = '#86efac';
    }

    if (qs('progress-hint-text')) {
      qs('progress-hint-text').innerHTML = '🎉 Excellent ! Vous pouvez maintenant gagner <strong class="text-green-600">+<?= $lesson['xp_reward'] ?> XP</strong>';
    }

    // Unlock button
    if (btn) {
      btn.classList.remove('locked');
      btn.classList.add('pending');
      btn.disabled = false;
    }
    if (btnIcon) {
      btnIcon.setAttribute('data-lucide', 'check-circle');
      btnIcon.classList.remove('lock-pulse');
      lucide.createIcons();
    }
    if (btnText)  btnText.textContent = 'Marquer comme terminé';
    if (btnSub)   btnSub.textContent  = 'Cliquez pour gagner +<?= $lesson['xp_reward'] ?> XP !';

    // Gentle ping toast
    showToast('🎓 Leçon complète ! Cliquez pour gagner vos XP.', 'info', 3000);

  } else if (!progressReady) {
    /* ── Still in progress ── */
    if (bar && !bar.classList.contains('active')) bar.classList.add('active');
    if (pill)    { pill.className = 'time-pill running'; }
    if (pillTxt) pillTxt.textContent = fmtTime(REQUIRED_SECS - elapsedSecs) + ' restant';
  }
}

/* ══════════════════════════════════════════════════════
   YOUTUBE TRACKING
══════════════════════════════════════════════════════ */
<?php if ($isYoutube): ?>

// Load YouTube IFrame API
(function() {
  var tag = document.createElement('script');
  tag.src = 'https://www.youtube.com/iframe_api';
  var first = document.getElementsByTagName('script')[0];
  first.parentNode.insertBefore(tag, first);
})();

// Called by YouTube API when ready
function onYouTubeIframeAPIReady() {
  ytPlayer = new YT.Player('yt-player', {
    videoId: YT_VIDEO_ID,
    playerVars: {
      rel: 0,
      modestbranding: 1,
      color: 'white',
      enablejsapi: 1
    },
    events: {
      onReady: function(e) {
        ytDuration = e.target.getDuration() || 0;

        // If no duration in DB, derive required secs from actual video length (80%)
        if (LESSON_MINS === 0 && ytDuration > 0) {
          REQUIRED_SECS = Math.max(45, Math.floor(ytDuration * 0.80));
          if (qs('required-display')) qs('required-display').textContent = fmtTime(REQUIRED_SECS);
          if (qs('remaining-hint'))   qs('remaining-hint').textContent   = 'Requis : ' + Math.ceil(REQUIRED_SECS / 60) + ' min';
        }
      },
      onStateChange: function(e) {
        if (e.data === YT.PlayerState.PLAYING) {
          startYTTracking();
        } else {
          stopYTTracking();
        }
      }
    }
  });
}

var ytTrackHandle = null;

function startYTTracking() {
  if (ALREADY_DONE || progressReady) return;
  if (ytTrackHandle) return; // already running
  ytTrackHandle = setInterval(function() {
    elapsedSecs++;
    updateProgressUI();
    if (progressReady) {
      clearInterval(ytTrackHandle);
      ytTrackHandle = null;
    }
  }, 1000);
}

function stopYTTracking() {
  if (ytTrackHandle) {
    clearInterval(ytTrackHandle);
    ytTrackHandle = null;
  }
}

<?php else: ?>
/* ══════════════════════════════════════════════════════
   TIMER-BASED TRACKING (PDF / Book / other)
══════════════════════════════════════════════════════ */

function startPageTimer() {
  if (ALREADY_DONE || progressReady) return;
  if (trackInterval) return;
  trackInterval = setInterval(function() {
    elapsedSecs++;
    updateProgressUI();
    if (progressReady) {
      clearInterval(trackInterval);
      trackInterval = null;
    }
  }, 1000);
}

// Start timer when page is loaded and visible
document.addEventListener('DOMContentLoaded', function() {
  startPageTimer();

  // Pause timer if tab is hidden (anti-cheat)
  document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
      if (trackInterval) {
        clearInterval(trackInterval);
        trackInterval = null;
      }
    } else {
      startPageTimer();
    }
  });
});

<?php endif; ?>

/* ══════════════════════════════════════════════════════
   COMPLETE LESSON (with server-side validation)
══════════════════════════════════════════════════════ */
async function completeLessonPage(lessonId) {
  if (!progressReady) {
    showToast('⏳ Regardez/lisez la leçon entièrement d\'abord !', 'error', 3000);
    return;
  }

  var btn = qs('complete-btn-main');
  if (!btn || btn.disabled || btn.classList.contains('done')) return;
  if (btn.classList.contains('loading')) return;

  btn.classList.add('loading');
  var orig = btn.innerHTML;
  btn.innerHTML = '<span style="display:inline-block;animation:spin .7s linear infinite">⟳</span> Traitement...';
  btn.disabled = true;

  try {
    var fd = new FormData();
    fd.append('lesson_id',    lessonId);
    fd.append('csrf_token',   window.MAZAR_CSRF);
    fd.append('elapsed_secs', Math.floor(elapsedSecs)); // pass elapsed for server check

    var res  = await fetch(window.MAZAR_AJAX, { method: 'POST', body: fd, credentials: 'same-origin' });
    var data = await res.json();

    if (data.success) {
      floatXP(<?= $lesson['xp_reward'] ?>, btn);
      showToast('+<?= $lesson['xp_reward'] ?> XP ! Leçon terminée avec succès ! 🎓', 'xp');

      // Update XP counters
      var hXP   = qs('header-xp');
      var xpDisp = qs('xp-display');
      if (hXP)    countUp(hXP,    window.MAZAR_XP, data.new_xp);
      if (xpDisp) countUp(xpDisp, window.MAZAR_XP, data.new_xp);

      setTimeout(function() {
        var bar = qs('xp-bar-fill');
        if (bar) bar.style.width = data.percent + '%';
      }, 300);

      window.MAZAR_XP    = data.new_xp;
      window.MAZAR_LEVEL = data.new_level;

      // Final button state
      btn.className = 'complete-btn-main done';
      btn.innerHTML = '<i data-lucide="check-circle-2" class="w-5 h-5"></i> Leçon terminée !';
      btn.disabled  = true;
      lucide.createIcons();

      var sub = qs('btn-subtext');
      if (sub) sub.textContent = '✅ +<?= $lesson['xp_reward'] ?> XP gagnés';

      ALREADY_DONE = true;

      if (data.level_up) {
        setTimeout(function() { showLevelUp(data.new_level); }, 800);
      }

    } else if (data.message === 'Already completed') {
      showToast('Cette leçon est déjà marquée comme terminée.', 'info');
      btn.className = 'complete-btn-main done';
      btn.innerHTML = '<i data-lucide="check-circle-2" class="w-5 h-5"></i> Leçon terminée !';
      btn.disabled  = true;
      lucide.createIcons();

    } else if (data.message === 'too_early') {
      showToast('⏳ Passez plus de temps sur la leçon ! (' + data.hint + ')', 'error', 4000);
      btn.innerHTML = orig;
      btn.disabled  = false;
      btn.classList.remove('loading');

    } else {
      showToast('Erreur : ' + (data.message || 'Réessayez.'), 'error');
      btn.innerHTML = orig;
      btn.disabled  = false;
      btn.classList.remove('loading');
    }

  } catch (err) {
    showToast('Erreur de connexion. Vérifiez votre réseau.', 'error');
    btn.innerHTML = orig;
    btn.disabled  = false;
    btn.classList.remove('loading');
  }
}

/* ── Init ── */
document.addEventListener('DOMContentLoaded', function() {
  lucide.createIcons();
});

/* ── Spin keyframe ── */
if (!document.getElementById('mazar-spin-style')) {
  var s = document.createElement('style');
  s.id = 'mazar-spin-style';
  s.textContent = '@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}';
  document.head.appendChild(s);
}
</script>

</body>
</html>