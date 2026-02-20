<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Already logged in?
if (!empty($_SESSION[SESS_USER_ID])) redirect('student/dashboard.php');

$lang     = getCurrentLang();
$dir      = getDirection();
$levels   = getAllLevels();
$errors   = [];
$formData = ['full_name' => '', 'email' => '', 'grade_level_id' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $fullName  = trim($_POST['full_name']       ?? '');
        $email     = trim($_POST['email']            ?? '');
        $password  = $_POST['password']              ?? '';
        $password2 = $_POST['confirm_password']      ?? '';
        $gradeId   = (int)($_POST['grade_level_id']  ?? 0);

        // Preserve form data on error
        $formData = [
            'full_name'      => $fullName,
            'email'          => $email,
            'grade_level_id' => $gradeId,
        ];

        // Validation
        if (!$fullName || !$email || !$password || !$gradeId) {
            $errors[] = t('fill_all_fields');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Adresse email invalide.';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
        } elseif ($password !== $password2) {
            $errors[] = t('pass_mismatch');
        } else {
            $db       = getDB();
            $existing = $db->prepare("SELECT id FROM users WHERE email = ?");
            $existing->execute([$email]);
            if ($existing->fetch()) {
                $errors[] = t('email_taken');
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $db->prepare(
                    "INSERT INTO users (full_name, email, password, grade_level_id, role, xp_points, level)
                     VALUES (?,?,?,?,'student',0,1)"
                )->execute([$fullName, $email, $hash, $gradeId]);

                $newId = (int)$db->lastInsertId();
                logActivity($newId, 'register', 'New student registered');

                $_SESSION[SESS_USER_ID]  = $newId;
                $_SESSION[SESS_ROLE]     = 'student';
                $_SESSION[SESS_USERNAME] = $fullName;
                $_SESSION[SESS_GRADE]    = $gradeId;
                $_SESSION[SESS_XP]       = 0;
                $_SESSION[SESS_LEVEL]    = 1;

                redirect('student/dashboard.php?welcome=1');
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
  <title><?= t('register_title') ?> — <?= t('site_name') ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
  <style>
    body { font-family: <?= $lang === 'ar' ? "'Cairo'" : "'Poppins'" ?>, sans-serif; }
    .gradient-bg { background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); }
  </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center py-12 px-4">

<div class="w-full max-w-md">

  <!-- Logo -->
  <div class="text-center mb-8">
    <a href="/" class="inline-flex items-center gap-2">
      <img src="assets/images/mazar.avif" alt="MAZAR" class="w-12 h-12 rounded-2xl object-contain">
      <span class="text-white font-black text-2xl"><?= t('site_name') ?></span>
    </a>
    <p class="text-blue-200 mt-2 text-sm"><?= t('tagline') ?></p>
  </div>

  <!-- Card -->
  <div class="bg-white rounded-3xl shadow-2xl p-8">
    <h1 class="text-2xl font-black text-gray-900 mb-1"><?= t('register_title') ?></h1>
    <p class="text-gray-500 text-sm mb-6">
      <?= t('have_account') ?>
      <a href="login.php" class="text-blue-600 font-semibold hover:underline"><?= t('login') ?></a>
    </p>

    <!-- Errors -->
    <?php if ($errors): ?>
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-5">
      <?php foreach ($errors as $e): ?>
      <p class="text-red-600 text-sm flex items-center gap-2">
        <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
        <?= htmlspecialchars($e) ?>
      </p>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <?= csrfField() ?>

      <!-- Full Name -->
      <div class="mb-4">
        <label class="block text-sm font-semibold text-gray-700 mb-2">
          <i data-lucide="user" class="w-4 h-4 inline <?= $dir === 'rtl' ? 'ml-1' : 'mr-1' ?>"></i>
          <?= t('full_name') ?>
        </label>
        <input
          type="text"
          name="full_name"
          value="<?= htmlspecialchars($formData['full_name']) ?>"
          placeholder="Mohammed Al-Hassan"
          required
          class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white text-gray-800"
        >
      </div>

      <!-- Email -->
      <div class="mb-4">
        <label class="block text-sm font-semibold text-gray-700 mb-2">
          <i data-lucide="mail" class="w-4 h-4 inline <?= $dir === 'rtl' ? 'ml-1' : 'mr-1' ?>"></i>
          <?= t('email') ?>
        </label>
        <input
          type="email"
          name="email"
          value="<?= htmlspecialchars($formData['email']) ?>"
          placeholder="eleve@example.ma"
          required
          class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white text-gray-800"
        >
      </div>

      <!-- Grade Level -->
      <div class="mb-4">
        <label class="block text-sm font-semibold text-gray-700 mb-2">
          <i data-lucide="graduation-cap" class="w-4 h-4 inline <?= $dir === 'rtl' ? 'ml-1' : 'mr-1' ?>"></i>
          <?= t('grade_level') ?>
        </label>
        <select
          name="grade_level_id"
          required
          class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white text-gray-800 cursor-pointer"
        >
          <option value=""><?= t('select_grade') ?></option>
          <?php foreach ($levels as $lv): ?>
          <option
            value="<?= $lv['id'] ?>"
            <?= ((string)$formData['grade_level_id'] === (string)$lv['id']) ? 'selected' : '' ?>
          >
            <?= htmlspecialchars($lv['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Password -->
      <div class="mb-4">
        <label class="block text-sm font-semibold text-gray-700 mb-2">
          <i data-lucide="lock" class="w-4 h-4 inline <?= $dir === 'rtl' ? 'ml-1' : 'mr-1' ?>"></i>
          <?= t('password') ?>
        </label>
        <input
          type="password"
          name="password"
          placeholder="Min. 8 caractères"
          required
          class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white text-gray-800"
        >
      </div>

      <!-- Confirm Password -->
      <div class="mb-6">
        <label class="block text-sm font-semibold text-gray-700 mb-2">
          <i data-lucide="shield-check" class="w-4 h-4 inline <?= $dir === 'rtl' ? 'ml-1' : 'mr-1' ?>"></i>
          <?= t('confirm_password') ?>
        </label>
        <input
          type="password"
          name="confirm_password"
          placeholder="Répétez le mot de passe"
          required
          class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white text-gray-800"
        >
      </div>

      <!-- Submit -->
      <button
        type="submit"
        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl transition flex items-center justify-center gap-2 shadow-lg shadow-blue-200 text-base"
      >
        <i data-lucide="user-plus" class="w-5 h-5"></i>
        <?= t('register') ?>
      </button>
    </form>

    <!-- Language Switcher -->
    <div class="flex justify-center gap-3 mt-6">
      <?php foreach (['ar', 'fr', 'en'] as $l): ?>
      <a
        href="?lang=<?= $l ?>"
        class="text-xs font-bold px-3 py-1 rounded-lg transition
               <?= $lang === $l ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' ?>"
      >
        <?= strtoupper($l) ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<script>lucide.createIcons();</script>
</body>
</html>