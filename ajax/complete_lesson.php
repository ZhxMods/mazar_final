<?php
// ============================================================
//  MAZAR — ajax/complete_lesson.php
//  POST: lesson_id, csrf_token
// ============================================================

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Must be AJAX POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Method not allowed']);
    exit;
}

// Must be logged in
if (empty($_SESSION[SESS_USER_ID])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'Unauthorized','redirect'=>'../login.php']);
    exit;
}

// CSRF
if (!verifyCsrf()) {
    http_response_code(403);
    echo json_encode(['success'=>false,'message'=>'Invalid CSRF token']);
    exit;
}

$lessonId = (int)($_POST['lesson_id'] ?? 0);
$userId   = (int)$_SESSION[SESS_USER_ID];

if (!$lessonId) {
    echo json_encode(['success'=>false,'message'=>'Invalid lesson ID']);
    exit;
}

// Verify lesson exists and is published
$db   = getDB();
$stmt = $db->prepare("SELECT id, xp_reward, title_fr FROM lessons WHERE id=? AND published=1");
$stmt->execute([$lessonId]);
$lesson = $stmt->fetch();

if (!$lesson) {
    echo json_encode(['success'=>false,'message'=>'Lesson not found']);
    exit;
}

// Complete (handles deduplication + XP award internally)
$result = completeLesson($userId, $lessonId);

echo json_encode($result);
exit;
