<?php
// ============================================================
//  MAZAR — student/lesson.php
//  Individual lesson detail page with embedded media
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
if ($isYoutube) {
    $embedUrl = "https://www.youtube.com/embed/{$ytId}?rel=0&modestbranding=1&color=white";
} elseif ($isDirectPdf) {
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
$userXP    = (int)$_SESSION[SESS_XP];
$userLevel = (int)$_SESSION[SESS_LEVEL];
$progressPct = xpProgressPercent($userXP, $userLevel);

$_typeLabels = ['video'=>'Vidéo','pdf'=>'PDF','book'=>'Livre'];
$_typeIcons  = ['video'=>'play-circle','pdf'=>'file-text','book'=>'book-open'];
$_typeColors = ['video'=>'#3B82F6','pdf'=>'#10B981','book'=>'#8B5CF6'];
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
      padding-bottom: 56.25%; /* 16:9 */
      height: 0;
    }
    .media-wrapper iframe {
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
      transition: all .2s;
    }
    .complete-btn-main.pending {
      background: linear-gradient(135deg, #f59e0b, #d97706);
      color: #fff;
      box-shadow: 0 4px 20px rgba(245,158,11,.35);
    }
    .complete-btn-main.pending:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 28px rgba(245,158,11,.45);
    }
    .complete-btn-main.done {
      background: #dcfce7;
      color: #166534;
      cursor: default;
      border: 2px solid #bbf7d0;
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
      width: 72px;
      height: 48px;
      border-radius: .5rem;
      object-fit: cover;
      flex-shrink: 0;
      background: #e5e7eb;
    }
    .related-thumb-placeholder {
      width: 72px;
      height: 48px;
      border-radius: .5rem;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #dbeafe, #ede9fe);
    }

    /* ── Open external button ── */
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

    /* ── XP Progress ── */
    .xp-bar { height: 6px; border-radius: 99px; background: #e2e8f0; overflow: hidden; }
    .xp-bar-inner { height: 100%; border-radius: 99px; background: linear-gradient(90deg, #f59e0b, #fbbf24); transition: width .8s ease; }

    /* ── Description ── */
    .desc-content {
      font-size: .9rem;
      line-height: 1.8;
      color: #475569;
    }
    .desc-content p { margin-bottom: .75rem; }

    /* ── Badge ── */
    .type-badge {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      font-size: .75rem;
      font-weight: 700;
      padding: .3rem .75rem;
      border-radius: 999px;
    }

    /* ── FAB ── */
    #mazar-fab {
      position: fixed;
      bottom: 1.5rem;
      <?= $dir === 'rtl' ? 'left' : 'right' ?>: 1.5rem;
      z-index: 9000;
    }
    #fab-btn {
      width: 54px; height: 54px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
      box-shadow: 0 0 0 4px rgba(29,78,216,.18), 0 8px 28px rgba(30,58,138,.45);
      text-decoration: none;
      transition: transform .22s cubic-bezier(.34,1.56,.64,1), box-shadow .22s;
      position: relative;
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
    .fade-up { animation: fadeUp .4s ease both; }
    .fade-up-d1 { animation-delay: .1s; }
    .fade-up-d2 { animation-delay: .2s; }
    .fade-up-d3 { animation-delay: .3s; }

    /* ── Toast container ── */
    #toast-container { position: fixed; top: 20px; <?= $dir === 'rtl' ? 'left' : 'right' ?>: 20px; z-index: 9999; }

    @media (max-width: 640px) {
      .top-nav { padding: .75rem 1rem; }
    }
  </style>
</head>
<body>

<div id="toast-container" class="space-y-2"></div>

<!-- ══════════════════════════════════════
     TOP NAV
══════════════════════════════════════ -->
<nav class="top-nav px-4 sm:px-6 py-3 flex items-center gap-3">
  <!-- Back -->
  <a href="dashboard.php?tab=lessons&subject=<?= $lesson['sid'] ?>"
     class="flex items-center gap-1.5 text-white/80 hover:text-white text-sm font-semibold transition bg-white/10 hover:bg-white/20 px-3 py-2 rounded-xl flex-shrink-0">
    <i data-lucide="arrow-<?= $dir === 'rtl' ? 'right' : 'left' ?>" class="w-4 h-4"></i>
    <span class="hidden sm:inline">Retour</span>
  </a>

  <!-- Breadcrumb -->
  <div class="flex items-center gap-2 text-white/70 text-xs sm:text-sm flex-1 min-w-0 overflow-hidden">
    <span class="truncate hidden sm:inline"><?= htmlspecialchars($lesson['level_name']) ?></span>
    <i data-lucide="chevron-right" class="w-3.5 h-3.5 flex-shrink-0 hidden sm:inline"></i>
    <span class="text-white/90 font-semibold truncate"><?= htmlspecialchars($lesson['subject_name']) ?></span>
    <i data-lucide="chevron-right" class="w-3.5 h-3.5 flex-shrink-0"></i>
    <span class="text-white font-bold truncate"><?= htmlspecialchars($title) ?></span>
  </div>

  <!-- XP badge -->
  <div class="flex items-center gap-2 bg-yellow-400/20 border border-yellow-400/30 rounded-xl px-3 py-1.5 flex-shrink-0">
    <i data-lucide="zap" class="w-3.5 h-3.5 text-yellow-300"></i>
    <span class="text-yellow-200 font-bold text-xs sm:text-sm"><span id="header-xp"><?= $userXP ?></span> XP</span>
  </div>
</nav>

<!-- ══════════════════════════════════════
     MAIN LAYOUT
══════════════════════════════════════ -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
  <div class="flex flex-col lg:flex-row gap-6">

    <!-- ── LEFT COLUMN — Media + Info ── -->
    <div class="flex-1 min-w-0">

      <!-- MEDIA PLAYER -->
      <div class="fade-up">
        <?php if ($isYoutube): ?>
        <!-- YouTube Embed -->
        <div class="media-wrapper video-ratio">
          <iframe
            src="<?= htmlspecialchars($embedUrl) ?>"
            title="<?= htmlspecialchars($title) ?>"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowfullscreen
            loading="lazy">
          </iframe>
        </div>

        <?php elseif ($isDirectPdf): ?>
        <!-- Google Docs PDF Viewer -->
        <div class="media-wrapper video-ratio">
          <iframe
            src="<?= htmlspecialchars($embedUrl) ?>"
            title="<?= htmlspecialchars($title) ?>"
            loading="lazy">
          </iframe>
        </div>

        <?php elseif ($lesson['type'] === 'pdf' || $lesson['type'] === 'book'): ?>
        <!-- MediaFire / External Document — show styled preview -->
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
        <!-- Open button below -->
        <div class="mt-4 flex gap-3 flex-wrap">
          <a href="<?= htmlspecialchars($lesson['url']) ?>" target="_blank" rel="noopener"
             class="open-external bg-blue-600 hover:bg-blue-700 text-white">
            <i data-lucide="external-link" class="w-5 h-5"></i>
            Ouvrir le <?= $typeLabel ?>
          </a>
        </div>

        <?php else: ?>
        <!-- Unknown type — show thumbnail or placeholder -->
        <div class="media-placeholder" style="border-radius:1.25rem; box-shadow:0 20px 60px rgba(0,0,0,.2);">
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
        <!-- Type badge + duration -->
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

        <!-- Title -->
        <h1 class="text-xl sm:text-2xl font-black text-gray-900 mb-1 leading-tight">
          <?= htmlspecialchars($title) ?>
        </h1>
        <?php if ($lang !== 'fr' && $lesson['title_fr'] && $lesson['title_fr'] !== $title): ?>
        <p class="text-gray-400 text-sm mb-4"><?= htmlspecialchars($lesson['title_fr']) ?></p>
        <?php endif; ?>

        <!-- Description -->
        <?php if ($desc): ?>
        <div class="mt-4 pt-4 border-t border-gray-100">
          <h2 class="text-sm font-bold text-gray-700 mb-2 flex items-center gap-2">
            <i data-lucide="align-left" class="w-4 h-4 text-blue-500"></i>
            Description
          </h2>
          <div class="desc-content"><?= nl2br(htmlspecialchars($desc)) ?></div>
        </div>
        <?php endif; ?>

        <!-- External link (always available for YouTube too) -->
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

      <!-- Complete lesson card -->
      <div class="card p-5 fade-up fade-up-d1">
        <h3 class="font-bold text-gray-800 text-sm mb-3 flex items-center gap-2">
          <i data-lucide="check-circle" class="w-4 h-4 text-green-500"></i>
          Progression de la leçon
        </h3>

        <?php if ($completed): ?>
        <button class="complete-btn-main done" disabled>
          <i data-lucide="check-circle-2" class="w-5 h-5"></i>
          Leçon terminée !
        </button>
        <p class="text-green-600 text-xs text-center mt-2 font-medium">✅ +<?= $lesson['xp_reward'] ?> XP déjà gagnés</p>
        <?php else: ?>
        <button id="complete-btn-main" onclick="completeLessonPage(<?= $lessonId ?>)"
                class="complete-btn-main pending">
          <i data-lucide="check-circle" class="w-5 h-5"></i>
          Marquer comme terminé
        </button>
        <p class="text-gray-400 text-xs text-center mt-2">Gagnez +<?= $lesson['xp_reward'] ?> XP en complétant cette leçon</p>
        <?php endif; ?>

        <!-- XP Progress -->
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
            $relTitle  = $rel['title'] ?: $rel['title_fr'];
            $relYt     = youtubeId($rel['url']);
            $relThumb  = $rel['thumbnail'] ?: ($relYt ? "https://img.youtube.com/vi/{$relYt}/hqdefault.jpg" : '');
            $_ri = ['video'=>'play','pdf'=>'file-text','book'=>'book-open'];
            $_rc = ['video'=>'#3B82F6','pdf'=>'#10B981','book'=>'#8B5CF6'];
            $relIcon   = isset($_ri[$rel['type']]) ? $_ri[$rel['type']] : 'book';
            $relColor  = isset($_rc[$rel['type']]) ? $_rc[$rel['type']] : '#6B7280';
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
<a href="mazar-ai.php" id="fab-btn" aria-label="MAZAR AI">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
    <path d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/>
    <path d="M18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z"/>
  </svg>
  <span class="fab-online"></span>
</a>
<div id="mazar-fab"></div>

<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
<script src="../assets/js/xp-system.js"></script>
<script>
  lucide.createIcons();

  window.MAZAR_XP    = <?= $userXP ?>;
  window.MAZAR_LEVEL = <?= $userLevel ?>;
  window.MAZAR_CSRF  = '<?= csrfToken() ?>';
  window.MAZAR_AJAX  = '../ajax/complete_lesson.php';

  async function completeLessonPage(lessonId) {
    const btn = document.getElementById('complete-btn-main');
    if (!btn || btn.classList.contains('loading')) return;

    btn.classList.add('loading');
    const orig = btn.innerHTML;
    btn.innerHTML = '<span style="display:inline-block;animation:spin .7s linear infinite">⟳</span> Traitement...';
    btn.disabled = true;

    try {
      const fd = new FormData();
      fd.append('lesson_id',  lessonId);
      fd.append('csrf_token', window.MAZAR_CSRF);

      const res  = await fetch(window.MAZAR_AJAX, { method:'POST', body:fd, credentials:'same-origin' });
      const data = await res.json();

      if (data.success) {
        // Animate XP
        floatXP(<?= $lesson['xp_reward'] ?>, btn);
        showToast('+<?= $lesson['xp_reward'] ?> XP ! Leçon terminée avec succès ! 🎓', 'xp');

        // Update header XP
        const hXP = document.getElementById('header-xp');
        if (hXP) countUp(hXP, window.MAZAR_XP, data.new_xp);

        // Update XP display
        const xpDisp = document.getElementById('xp-display');
        if (xpDisp) countUp(xpDisp, window.MAZAR_XP, data.new_xp);

        // Update bar
        setTimeout(() => {
          const bar = document.getElementById('xp-bar-fill');
          if (bar) bar.style.width = data.percent + '%';
        }, 300);

        window.MAZAR_XP    = data.new_xp;
        window.MAZAR_LEVEL = data.new_level;

        // Replace button
        btn.className = 'complete-btn-main done';
        btn.innerHTML = '<i data-lucide="check-circle-2" class="w-5 h-5"></i> Leçon terminée !';
        btn.disabled = true;
        lucide.createIcons();

        // Level up?
        if (data.level_up) {
          setTimeout(() => showLevelUp(data.new_level), 800);
        }

      } else if (data.message === 'Already completed') {
        showToast('Cette leçon est déjà marquée comme terminée.', 'info');
        btn.className = 'complete-btn-main done';
        btn.innerHTML = '<i data-lucide="check-circle-2" class="w-5 h-5"></i> Leçon terminée !';
        btn.disabled = true;
        lucide.createIcons();
      } else {
        showToast('Erreur : ' + (data.message || 'Réessayez.'), 'error');
        btn.innerHTML = orig;
        btn.disabled = false;
        btn.classList.remove('loading');
      }
    } catch (err) {
      showToast('Erreur de connexion.', 'error');
      btn.innerHTML = orig;
      btn.disabled = false;
      btn.classList.remove('loading');
    }
  }
</script>
</body>
</html>