<?php
// ============================================
// follow.php — AJAX: Follow / Unfollow User
// ============================================
require_once 'includes/auth.php';
require_once 'includes/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'redirect' => true]);
    exit();
}

$currentUserId = getCurrentUserId();
$targetId      = intval($_POST['user_id'] ?? 0);

// Can't follow yourself
if (!$targetId || $targetId === $currentUserId) {
    echo json_encode(['success' => false, 'message' => 'Invalid user.']);
    exit();
}

// Check if already following
$chk = $pdo->prepare("SELECT id FROM follows WHERE follower_id = ? AND following_id = ?");
$chk->execute([$currentUserId, $targetId]);
$existing = $chk->fetch();

if ($existing) {
    // Unfollow
    $del = $pdo->prepare("DELETE FROM follows WHERE follower_id = ? AND following_id = ?");
    $del->execute([$currentUserId, $targetId]);
    $following = false;
} else {
    // Follow
    $ins = $pdo->prepare("INSERT INTO follows (follower_id, following_id) VALUES (?, ?)");
    $ins->execute([$currentUserId, $targetId]);
    $following = true;
}

// Get updated follower count
$cnt = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE following_id = ?");
$cnt->execute([$targetId]);
$followerCount = $cnt->fetchColumn();

echo json_encode([
    'success'        => true,
    'following'      => $following,
    'follower_count' => (int)$followerCount
]);
