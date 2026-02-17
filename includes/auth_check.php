<?php
// ============================================================
//  MAZAR — includes/auth_check.php
//  Include at top of every protected student page
// ============================================================

if (!defined('SESS_USER_ID')) require_once dirname(__DIR__) . '/config.php';
if (!function_exists('redirect'))  require_once __DIR__ . '/functions.php';

if (empty($_SESSION[SESS_USER_ID])) {
    redirect(dirname($_SERVER['SCRIPT_NAME'], 2) . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

// Refresh XP/level from DB each time (prevents stale session data)
if (function_exists('getDB')) {
    $__stmt = getDB()->prepare("SELECT xp_points, level, status FROM users WHERE id = ?");
    $__stmt->execute([$_SESSION[SESS_USER_ID]]);
    $__user = $__stmt->fetch();
    if (!$__user || $__user['status'] !== 'active') {
        session_destroy();
        redirect(dirname($_SERVER['SCRIPT_NAME'], 2) . '/login.php?msg=banned');
    }
    $_SESSION[SESS_XP]    = $__user['xp_points'];
    $_SESSION[SESS_LEVEL] = $__user['level'];
}
