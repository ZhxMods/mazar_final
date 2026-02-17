<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

if (!empty($_SESSION[SESS_USER_ID])) {
    $role = $_SESSION[SESS_ROLE] ?? 'student';
    redirect($role === 'student' ? 'student/dashboard.php' : 'admin/dashboard.php');
}

$lang   = getCurrentLang();
$dir    = getDirection();
$errors = [];
$info   = '';

if (isset($_GET['msg'])) {
    $msgs = ['unauthorized'=>'Accès non autorisé.','banned'=>t('account_banned'),'logout'=>t('logout_success')];
    $info = $msgs[$_GET['msg']] ?? '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) { $errors[] = 'Invalid request.'; }
    else {
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password']       ?? '';

        if (!$email || !$password) {
            $errors[] = t('fill_all_fields');
        } else {
            $db   = getDB();
            $stmt = $db->prepare("SELECT id, full_name, password, role, grade_level_id, xp_points, level, status FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                $errors[] = t('invalid_credentials');
            } elseif ($user['status'] === 'banned') {
                $errors[] = t('account_banned');
            } else {
                // Regenerate session ID to prevent fixation
                session_regenerate_id(true);

                $_SESSION[SESS_USER_ID]  = $user['id'];
                $_SESSION[SESS_ROLE]     = $user['role'];
                $_SESSION[SESS_USERNAME] = $user['full_name'];
                $_SESSION[SESS_GRADE]    = $user['grade_level_id'];
                $_SESSION[SESS_XP]       = $user['xp_points'];
                $_SESSION[SESS_LEVEL]    = $user['level'];

                logActivity($user['id'], 'login', 'User logged in');

                $redirect = $_GET['redirect'] ?? '';
                if ($redirect && strpos($redirect, '/') === 0) redirect($redirect);
                redirect(in_array($user['role'], ['admin','super_admin']) ? 'admin/dashboard.php' : 'student/dashboard.php');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= t('login_title') ?> — <?= t('site_name') ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
  <style>
    body { font-family: <?= $lang==='ar' ? "'Cairo'" : "'Poppins'" ?>, sans-serif; }
    .gradient-bg { background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); }
  </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center py-12 px-4">

<div class="w-full max-w-md">
  <!-- Logo -->
  <div class="text-center mb-8">
    <a href="/" class="inline-flex items-center gap-2">
      <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center">
        <span class="text-white font-black text-2xl">M</span>
      </div>
      <span class="text-white font-black text-2xl"><?= t('site_name') ?></span>
    </a>
    <p class="text-blue-200 mt-2 text-sm"><?= t('tagline') ?></p>
  </div>

  <div class="bg-white rounded-3xl shadow-2xl p-8">
    <h1 class="text-2xl font-black text-gray-900 mb-1"><?= t('login_title') ?></h1>
    <p class="text-gray-500 text-sm mb-6">
      <?= t('no_account') ?> <a href="register.php" class="text-blue-600 font-semibold hover:underline"><?= t('register') ?></a>
    </p>

    <!-- Info message -->
    <?php if ($info): ?>
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 mb-4 text-blue-700 text-sm flex items-center gap-2">
      <i data-lucide="info" class="w-4 h-4 flex-shrink-0"></i>
      <?= htmlspecialchars($info) ?>
    </div>
    <?php endif; ?>

    <!-- Errors -->
    <?php if ($errors): ?>
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-5">
      <?php foreach($errors as $e): ?>
      <p class="text-red-600 text-sm flex items-center gap-2">
        <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
        <?= htmlspecialchars($e) ?>
      </p>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <?= csrfField() ?>

      <div class="mb-4">
        <label class="block text-sm font-semibold text-gray-700 mb-2">
          <i data-lucide="mail" class="w-4 h-4 inline <?= $dir==='rtl'?'ml-1':'mr-1' ?>"></i>
          <?= t('email') ?>
        </label>
        <input type="email" name="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white"
               placeholder="eleve@example.ma" required autofocus>
      </div>

      <div class="mb-6">
        <label class="block text-sm font-semibold text-gray-700 mb-2">
          <i data-lucide="lock" class="w-4 h-4 inline <?= $dir==='rtl'?'ml-1':'mr-1' ?>"></i>
          <?= t('password') ?>
        </label>
        <input type="password" name="password"
               class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white"
               placeholder="Votre mot de passe" required>
      </div>

      <button type="submit"
              class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl transition flex items-center justify-center gap-2 shadow-lg shadow-blue-200 text-base">
        <i data-lucide="log-in" class="w-5 h-5"></i>
        <?= t('login') ?>
      </button>
    </form>

    <!-- Demo credentials hint -->
    <div class="mt-5 p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-700">
      <strong>Admin demo:</strong> admin@mazar.ma / Admin@1234
    </div>

    <div class="flex justify-center gap-3 mt-5">
      <?php foreach(['ar','fr','en'] as $l): ?>
      <a href="?lang=<?= $l ?>"
         class="text-xs font-bold px-3 py-1 rounded-lg transition <?= $lang===$l ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' ?>">
        <?= strtoupper($l) ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
