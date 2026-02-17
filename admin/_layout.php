<?php
// admin/_layout.php — Admin Panel Header
// Include at top of every admin page, after auth check
// $pageTitle must be set before including this file

$lang = getCurrentLang();
$dir  = getDirection();
$adminName = $_SESSION[SESS_USERNAME] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> — <?= t('site_name') ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

  <!-- DataTables -->
  <link  rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap5.min.css">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>

  <!-- Alpine.js -->
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <style>
    body { font-family: <?= $lang==='ar' ? "'Cairo'" : "'Poppins'" ?>, sans-serif; background:#0f172a; }
    .admin-sidebar { background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%); border-right: 1px solid rgba(255,255,255,.06); }
    .admin-content { background: #f1f5f9; }
    .active-nav { background: rgba(59,130,246,.15); color: #60a5fa; border-<?= $dir==='rtl'?'right':'left' ?>: 3px solid #3b82f6; }
    .nav-item { transition: all .15s; }
    .nav-item:hover { background: rgba(255,255,255,.05); color: #93c5fd; }
    .stat-card { background: #fff; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.07); padding: 1.5rem; }
    .admin-card { background: #fff; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.07); }
    .badge-admin { background: linear-gradient(135deg, #1d4ed8, #7c3aed); }
    /* Modal */
    .modal-overlay { position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:1000;display:flex;align-items:center;justify-content:center;padding:1rem; }
    .modal-box { background:#fff;border-radius:1.5rem;width:100%;max-width:680px;max-height:90vh;overflow-y:auto;box-shadow:0 25px 60px rgba(0,0,0,.35); }
    #toast-admin { position:fixed;top:20px;<?= $dir==='rtl'?'left':'right' ?>:20px;z-index:9999;space-y:.5rem; }
  </style>
</head>
<body class="flex h-screen overflow-hidden">

<!-- ── Toast ── -->
<div id="toast-admin" class="space-y-2"></div>

<!-- ══════════════════════════════════════
     ADMIN SIDEBAR
══════════════════════════════════════ -->
<aside class="admin-sidebar w-60 flex-shrink-0 flex flex-col h-full overflow-y-auto hidden md:flex">
  <!-- Logo -->
  <div class="px-5 py-5 border-b border-white/5">
    <a href="/<?= basename(dirname($_SERVER['SCRIPT_NAME'])) !== 'admin' ? '../' : '' ?>" class="flex items-center gap-2">
      <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center">
        <span class="text-white font-black text-lg">M</span>
      </div>
      <span class="text-white font-black text-lg"><?= t('site_name') ?></span>
    </a>
    <div class="mt-1 text-xs text-slate-500">Admin Panel</div>
  </div>

  <!-- Admin Profile -->
  <div class="px-5 py-4 border-b border-white/5">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 badge-admin rounded-full flex items-center justify-center text-white font-bold flex-shrink-0">
        <?= mb_strtoupper(mb_substr($adminName,0,1)) ?>
      </div>
      <div>
        <div class="text-white font-semibold text-sm"><?= htmlspecialchars($adminName) ?></div>
        <div class="text-slate-400 text-xs"><?= $_SESSION[SESS_ROLE] ?></div>
      </div>
    </div>
  </div>

  <!-- Navigation -->
  <nav class="px-3 py-4 flex-1 space-y-1">
    <?php
    $navItems = [
      ['dashboard.php',      'layout-dashboard', t('admin_dashboard')],
      ['manage_lessons.php', 'book-open',         t('manage_lessons')],
      ['manage_users.php',   'users',             t('manage_users')],
    ];
    $current = basename($_SERVER['PHP_SELF']);
    foreach($navItems as [$href,$icon,$label]):
    ?>
    <a href="<?= $href ?>"
       class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-400 text-sm font-medium cursor-pointer
       <?= $current===$href ? 'active-nav text-blue-400' : '' ?>">
      <i data-lucide="<?= $icon ?>" class="w-4 h-4 flex-shrink-0"></i>
      <?= $label ?>
    </a>
    <?php endforeach; ?>
  </nav>

  <!-- Lang + Logout -->
  <div class="px-4 py-4 border-t border-white/5 space-y-3">
    <div class="flex gap-1">
      <?php foreach(['ar','fr','en'] as $l): ?>
      <a href="?lang=<?= $l ?>"
         class="flex-1 text-center py-1 rounded-lg text-xs font-bold transition <?= $lang===$l ? 'bg-blue-600 text-white' : 'text-slate-500 hover:bg-white/5' ?>">
        <?= strtoupper($l) ?>
      </a>
      <?php endforeach; ?>
    </div>
    <a href="../logout.php" class="flex items-center gap-2 text-slate-500 hover:text-white text-xs px-2 py-2 rounded-xl hover:bg-white/5 transition">
      <i data-lucide="log-out" class="w-4 h-4"></i>
      <?= t('logout') ?>
    </a>
  </div>
</aside>

<!-- ══════════════════════════════════════
     MAIN ADMIN CONTENT WRAPPER
══════════════════════════════════════ -->
<div class="flex-1 admin-content flex flex-col overflow-hidden">
  <!-- Top Bar -->
  <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between flex-shrink-0">
    <h1 class="font-black text-gray-900 text-lg"><?= htmlspecialchars($pageTitle ?? '') ?></h1>
    <div class="flex items-center gap-3">
      <a href="../index.php" target="_blank" class="text-gray-400 hover:text-blue-600 text-sm flex items-center gap-1 transition">
        <i data-lucide="external-link" class="w-4 h-4"></i>
        <?= t('home') ?>
      </a>
      <div class="badge-admin text-white text-xs font-bold px-3 py-1 rounded-full"><?= t('admin_dashboard') ?></div>
    </div>
  </header>

  <!-- Page Content -->
  <main class="flex-1 overflow-y-auto p-6">
