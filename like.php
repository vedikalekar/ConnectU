<?php
// ============================================
// like.php — AJAX: Toggle Like on a Post
// ============================================
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Set response type to JSON
header('Content-Type: application/json');

// Must be logged in
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

// Check if already liked
$stmt = $pdo->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
$stmt->execute([$postId, $userId]);
$existing = $stmt->fetch();

if ($existing) {
    // Already liked → unlike
    $del = $pdo->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
    $del->execute([$postId, $userId]);
    $liked = false;
} else {
    // Not liked → like
    $ins = $pdo->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
    $ins->execute([$postId, $userId]);
    $liked = true;
}

// Get updated count
$cnt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
$cnt->execute([$postId]);
$count = $cnt->fetchColumn();

echo json_encode([
    'success' => true,
    'liked'   => $liked,
    'count'   => (int)$count
]);
