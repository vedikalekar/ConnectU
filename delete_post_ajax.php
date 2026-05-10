<?php
// ============================================
// delete_post_ajax.php — AJAX: Delete a Post
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

// Get post (must belong to user)
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->execute([$postId, $userId]);
$post = $stmt->fetch();

if (!$post) {
    echo json_encode(['success' => false, 'message' => 'Post not found or permission denied.']);
    exit();
}

// Delete image file if exists
if ($post['image'] && file_exists('uploads/posts/' . $post['image'])) {
    unlink('uploads/posts/' . $post['image']);
}

// Delete post (comments and likes cascade via foreign keys)
$del = $pdo->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
$del->execute([$postId, $userId]);

echo json_encode(['success' => true]);
