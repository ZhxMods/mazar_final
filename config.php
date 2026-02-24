<?php
// ============================================================
//  MAZAR Educational Platform — config.php
//  Edit DB credentials before uploading to InfinityFree
// ============================================================

// ── Database ─────────────────────────────────────────────────
define('DB_HOST', 'sql110.hstn.me');
define('DB_NAME', 'mseet_41230639_mazar');
define('DB_USER', 'mseet_41230639');
define('DB_PASS', 'mRHv9d6yWDk2');

// ── Site ─────────────────────────────────────────────────────
define('SITE_NAME',    'MAZAR');
define('BASE_URL',     'https://mazar.zya.me');   // no trailing slash
define('DEFAULT_LANG', 'fr');

// ── Session key names ─────────────────────────────────────────
define('SESS_USER_ID',   'mazar_uid');
define('SESS_ROLE',      'mazar_role');
define('SESS_LANG',      'mazar_lang');
define('SESS_GRADE',     'mazar_grade');
define('SESS_USERNAME',  'mazar_uname');
define('SESS_XP',        'mazar_xp');
define('SESS_LEVEL',     'mazar_lvl');

// ── XP Rewards ───────────────────────────────────────────────
define('XP_LESSON',  10);
define('XP_QUIZ',    50);

// ── Level Thresholds (XP needed to REACH that level) ─────────
define('LEVEL_THRESHOLDS', serialize([
    1  => 0,
    2  => 100,
    3  => 300,
    4  => 600,
    5  => 1000,
    6  => 1500,
    7  => 2200,
    8  => 3000,
    9  => 4000,
    10 => 5500,
]));

// ── Start session ─────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_name('MAZAR_SESS');
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// ── Default language from session ────────────────────────────
if (!isset($_SESSION[SESS_LANG])) {
    $_SESSION[SESS_LANG] = DEFAULT_LANG;
}
if (isset($_GET['lang']) && in_array($_GET['lang'], ['ar','fr','en'])) {
    $_SESSION[SESS_LANG] = $_GET['lang'];
}
