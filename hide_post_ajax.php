<?php
// ============================================
// hide_post_ajax.php — AJAX: Hide a Post
// ============================================
require_once 'includes/auth.php';
require_once 'includes/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'redirect' => true]);
    exit();
}

$userId = getCurrentUserId();
$postId = intval($_POST['post_id'] ?? 0);

if (!$postId) {
    echo json_encode(['success' => false, 'message' => 'Invalid post.']);
    exit();
}

// Check if already hidden
$stmt = $pdo->prepare("SELECT id FROM hidden_posts WHERE post_id = ? AND user_id = ?");
$stmt->execute([$postId, $userId]);
if (!$stmt->fetch()) {
    $ins = $pdo->prepare("INSERT INTO hidden_posts (post_id, user_id) VALUES (?, ?)");
    $ins->execute([$postId, $userId]);
}

echo json_encode(['success' => true]);
