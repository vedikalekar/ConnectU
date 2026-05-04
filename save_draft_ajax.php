<?php
// ============================================
// save_draft_ajax.php — AJAX: Auto-save Post Draft
// ============================================
require_once 'includes/auth.php';
require_once 'includes/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'redirect' => true]);
    exit();
}

$userId = getCurrentUserId();
$content = $_POST['content'] ?? '';

// We only save text drafts. If it's empty, we delete the draft.
if (empty(trim($content))) {
    $stmt = $pdo->prepare("DELETE FROM post_drafts WHERE user_id = ?");
    $stmt->execute([$userId]);
    echo json_encode(['success' => true, 'action' => 'deleted']);
    exit();
}

// Insert or update the draft for the user
$stmt = $pdo->prepare("
    INSERT INTO post_drafts (user_id, content) 
    VALUES (?, ?) 
    ON DUPLICATE KEY UPDATE content = VALUES(content)
");
$stmt->execute([$userId, $content]);

echo json_encode([
    'success' => true, 
    'action' => 'saved',
    'time' => date('h:i A')
]);
