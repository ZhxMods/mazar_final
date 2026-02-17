<?php
// ============================================================
//  MAZAR — includes/functions.php
// ============================================================

if (!defined('SESS_LANG')) require_once dirname(__DIR__) . '/config.php';
if (!function_exists('getDB')) require_once __DIR__ . '/db.php';

// ── Translation ───────────────────────────────────────────────
function getCurrentLang(): string {
    return $_SESSION[SESS_LANG] ?? DEFAULT_LANG;
}

function getDirection(): string {
    return getCurrentLang() === 'ar' ? 'rtl' : 'ltr';
}

function loadTranslations(): array {
    static $trans = null;
    if ($trans === null) {
        $lang = getCurrentLang();
        $file = dirname(__DIR__) . '/lang/' . $lang . '.php';
        if (!file_exists($file)) {
            $file = dirname(__DIR__) . '/lang/fr.php';
        }
        $trans = include $file;
    }
    return $trans;
}

function t(string $key, array $replace = []): string {
    $translations = loadTranslations();
    $text = $translations[$key] ?? $key;
    foreach ($replace as $k => $v) {
        $text = str_replace(':' . $k, $v, $text);
    }
    return $text;
}

// ── XP & Level ───────────────────────────────────────────────
function getLevelThresholds(): array {
    return unserialize(LEVEL_THRESHOLDS);
}

function calculateLevel(int $xp): int {
    $thresholds = getLevelThresholds();
    $level = 1;
    foreach ($thresholds as $lvl => $required) {
        if ($xp >= $required) $level = $lvl;
    }
    return $level;
}

function xpForNextLevel(int $currentLevel): int {
    $thresholds = getLevelThresholds();
    return $thresholds[$currentLevel + 1] ?? end($thresholds);
}

function xpProgressPercent(int $xp, int $level): float {
    $thresholds = getLevelThresholds();
    $current = $thresholds[$level] ?? 0;
    $next    = $thresholds[$level + 1] ?? $thresholds[10];
    if ($next <= $current) return 100;
    return min(100, round(($xp - $current) / ($next - $current) * 100, 1));
}

function levelBadgeColor(int $level): string {
    $colors = [
        1 => '#6B7280', 2 => '#3B82F6', 3 => '#10B981',
        4 => '#F59E0B', 5 => '#EF4444', 6 => '#8B5CF6',
        7 => '#EC4899', 8 => '#14B8A6', 9 => '#F97316', 10 => '#FACC15',
    ];
    return $colors[$level] ?? '#3B82F6';
}

// ── Award XP ──────────────────────────────────────────────────
function awardXP(int $userId, int $amount, string $reason = ''): array {
    $db = getDB();

    // Get current XP
    $stmt = $db->prepare("SELECT xp_points, level FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if (!$user) return ['success' => false, 'message' => 'User not found'];

    $newXP    = $user['xp_points'] + $amount;
    $newLevel = calculateLevel($newXP);

    $db->prepare("UPDATE users SET xp_points = ?, level = ? WHERE id = ?")
       ->execute([$newXP, $newLevel, $userId]);

    // Update session
    $_SESSION[SESS_XP]    = $newXP;
    $_SESSION[SESS_LEVEL] = $newLevel;

    // Log activity
    logActivity($userId, 'xp_earned', "Earned {$amount} XP — {$reason}");

    return [
        'success'    => true,
        'new_xp'     => $newXP,
        'new_level'  => $newLevel,
        'level_up'   => $newLevel > $user['level'],
        'percent'    => xpProgressPercent($newXP, $newLevel),
        'next_level_xp' => xpForNextLevel($newLevel),
    ];
}

// ── Lesson Completion ─────────────────────────────────────────
function hasCompletedLesson(int $userId, int $lessonId): bool {
    $db   = getDB();
    $stmt = $db->prepare("SELECT id FROM user_lesson_completions WHERE user_id=? AND lesson_id=?");
    $stmt->execute([$userId, $lessonId]);
    return (bool)$stmt->fetch();
}

function completeLesson(int $userId, int $lessonId): array {
    if (hasCompletedLesson($userId, $lessonId)) {
        return ['success' => false, 'message' => 'Already completed'];
    }

    $db = getDB();
    $db->prepare("INSERT INTO user_lesson_completions (user_id, lesson_id) VALUES (?,?)")
       ->execute([$userId, $lessonId]);

    $result = awardXP($userId, XP_LESSON, 'Lesson completed');

    // Log activity
    logActivity($userId, 'lesson_complete', "Completed lesson #{$lessonId}");

    return array_merge($result, ['message' => '+' . XP_LESSON . ' XP!']);
}

// ── Activity Log ──────────────────────────────────────────────
function logActivity(int $userId, string $action, string $details = ''): void {
    try {
        $db = getDB();
        $db->prepare("INSERT INTO activity_log (user_id, action, details) VALUES (?,?,?)")
           ->execute([$userId, $action, $details]);
    } catch (Exception $e) {
        // Non-critical — just log
        error_log('[MAZAR Activity] ' . $e->getMessage());
    }
}

// ── CSRF Helpers ──────────────────────────────────────────────
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">';
}

function verifyCsrf(): bool {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

// ── Sanitize ──────────────────────────────────────────────────
function clean(string $str): string {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

// ── Redirect ──────────────────────────────────────────────────
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

// ── Get levels from DB ────────────────────────────────────────
function getAllLevels(): array {
    static $levels = null;
    if ($levels === null) {
        $lang  = getCurrentLang();
        $col   = "name_{$lang}";
        $db    = getDB();
        $stmt  = $db->query("SELECT id, {$col} AS name, slug, order_num FROM levels ORDER BY order_num ASC");
        $levels = $stmt->fetchAll();
    }
    return $levels;
}

// ── Get subjects for a level ──────────────────────────────────
function getSubjectsByLevel(int $levelId): array {
    $lang = getCurrentLang();
    $col  = "name_{$lang}";
    $db   = getDB();
    $stmt = $db->prepare("SELECT id, {$col} AS name, icon, color FROM subjects WHERE level_id = ? ORDER BY order_num ASC");
    $stmt->execute([$levelId]);
    return $stmt->fetchAll();
}

// ── Get lessons for subject ───────────────────────────────────
function getLessonsBySubject(int $subjectId, int $userId = 0): array {
    $lang = getCurrentLang();
    $col  = "title_{$lang}";
    $db   = getDB();
    $sql  = "SELECT l.id, l.{$col} AS title, l.type, l.url, l.thumbnail, l.xp_reward, l.duration,
                    " . ($userId ? "IF(ulc.id IS NOT NULL, 1, 0)" : "0") . " AS completed
             FROM lessons l
             " . ($userId ? "LEFT JOIN user_lesson_completions ulc ON ulc.lesson_id=l.id AND ulc.user_id=?" : "") . "
             WHERE l.subject_id = ? AND l.published = 1
             ORDER BY l.order_num ASC";
    $params = $userId ? [$userId, $subjectId] : [$subjectId];
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// ── Leaderboard for grade ─────────────────────────────────────
function getLeaderboard(int $levelId, int $limit = 5): array {
    $db   = getDB();
    $stmt = $db->prepare(
        "SELECT id, full_name, xp_points, level
         FROM users
         WHERE grade_level_id = ? AND role = 'student' AND status = 'active'
         ORDER BY xp_points DESC LIMIT ?"
    );
    $stmt->execute([$levelId, $limit]);
    return $stmt->fetchAll();
}

// ── YouTube ID from URL ───────────────────────────────────────
function youtubeId(string $url): string {
    preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m);
    return $m[1] ?? '';
}
