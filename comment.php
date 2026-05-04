<?php
// ============================================
// comment.php — AJAX: Post a Comment
// ============================================
require_once 'includes/auth.php';
require_once 'includes/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'redirect' => true]);
    exit();
}

$userId  = getCurrentUserId();
$postId  = intval($_POST['post_id'] ?? 0);
$content = trim($_POST['content'] ?? '');

if (!$postId || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Missing post ID or content.']);
    exit();
}

if (strlen($content) > 500) {
    echo json_encode(['success' => false, 'message' => 'Comment too long.']);
    exit();
}

// Insert comment
$ins = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
$ins->execute([$postId, $userId, $content]);

// Get username
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

echo json_encode([
    'success' => true,
    'comment' => [
        'username' => $user['username'],
        'content'  => $content
    ]
]);
