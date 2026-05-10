<?php
// ============================================
// delete_post.php — Delete a Post
// ============================================
require_once 'includes/auth.php';
require_once 'includes/db.php';

requireLogin();

$userId = getCurrentUserId();
$postId = intval($_GET['id'] ?? 0);

if (!$postId) {
    header("Location: dashboard.php");
    exit();
}

// Get post (must belong to user)
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->execute([$postId, $userId]);
$post = $stmt->fetch();

if (!$post) {
    header("Location: dashboard.php");
    exit();
}

// Delete image file if exists
if ($post['image'] && file_exists('uploads/posts/' . $post['image'])) {
    unlink('uploads/posts/' . $post['image']);
}

// Delete post (comments and likes cascade via foreign keys)
$del = $pdo->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
$del->execute([$postId, $userId]);

// Redirect to profile or dashboard
header("Location: profile.php?id=$userId");
exit();
