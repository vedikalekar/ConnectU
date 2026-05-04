<?php
// ============================================
// delete_comment.php — Delete a Comment
// ============================================
require_once 'includes/auth.php';
require_once 'includes/db.php';

requireLogin();

$userId    = getCurrentUserId();
$commentId = intval($_GET['id']      ?? 0);
$postId    = intval($_GET['post_id'] ?? 0);

if (!$commentId) {
    header("Location: dashboard.php");
    exit();
}

// Can delete if: you wrote the comment OR you own the post
$stmt = $pdo->prepare("
    SELECT c.* FROM comments c
    JOIN posts p ON c.post_id = p.id
    WHERE c.id = ?
      AND (c.user_id = ? OR p.user_id = ?)
");
$stmt->execute([$commentId, $userId, $userId]);
$comment = $stmt->fetch();

if ($comment) {
    $del = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    $del->execute([$commentId]);
}

// Go back to post
if ($postId) {
    header("Location: post_detail.php?id=$postId");
} else {
    header("Location: dashboard.php");
}
exit();
