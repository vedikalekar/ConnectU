<?php
// ============================================
// post_detail.php — Single Post View
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

// ---- Get post with author info ----
$stmt = $pdo->prepare("
    SELECT p.*, u.name, u.username, u.profile_pic,
           (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
           (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = ?) AS user_liked
    FROM posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.id = ?
");
$stmt->execute([$userId, $postId]);
$post = $stmt->fetch();

if (!$post) {
    echo "<p style='padding:40px;font-family:sans-serif;'>Post not found. <a href='dashboard.php'>Go home</a></p>";
    exit();
}

// ---- Get all comments ----
$cStmt = $pdo->prepare("
    SELECT c.*, u.name, u.username, u.profile_pic
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.post_id = ?
    ORDER BY c.created_at ASC
");
$cStmt->execute([$postId]);
$comments = $cStmt->fetchAll();

// ---- Handle comment form ----
$commentError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $commentText = trim($_POST['comment'] ?? '');
    if (empty($commentText)) {
        $commentError = 'Comment cannot be empty.';
    } elseif (strlen($commentText) > 500) {
        $commentError = 'Comment is too long (max 500 chars).';
    } else {
        $ins = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
        $ins->execute([$postId, $userId, $commentText]);
        header("Location: post_detail.php?id=$postId");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post by <?= safe($post['name']) ?> — ConnectU</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="app-shell">
    <?php include 'includes/nav.php'; ?>

    <div class="main-content">

        <header class="top-header">
            <h2>📄 Post Details</h2>
            <a href="javascript:history.back()" class="btn btn-outline btn-sm">← Go Back</a>
        </header>

        <div class="page-body">

            <!-- ---- POST CARD ---- -->
            <div class="post-card" style="margin-bottom:24px;">

                <div class="post-header">
                    <?php if ($post['profile_pic'] && $post['profile_pic'] !== 'default.png'): ?>
                        <img class="avatar" src="uploads/profiles/<?= safe($post['profile_pic']) ?>" alt="">
                    <?php else: ?>
                        <div class="avatar-placeholder">
                            <?= strtoupper(substr($post['name'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>

                    <div class="post-meta">
                        <a class="author" href="profile.php?id=<?= $post['user_id'] ?>">
                            <?= safe($post['name']) ?>
                        </a>
                        <div class="time">
                            @<?= safe($post['username']) ?> · <?= formatDate($post['created_at']) ?>
                        </div>
                    </div>

                    <!-- Owner actions -->
                    <?php if ($post['user_id'] == $userId): ?>
                    <div style="display:flex;gap:8px;margin-left:auto;">
                        <a href="edit_post.php?id=<?= $postId ?>" class="btn btn-outline btn-sm">✏️ Edit</a>
                        <a href="#" class="btn btn-danger btn-sm sm-delete-post" data-post-id="<?= $postId ?>">
                           🗑️ Delete
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Full content -->
                <div class="post-content" style="padding:16px 20px 20px;">
                    <p style="font-size:1.05rem;line-height:1.75;">
                        <?= nl2br(safe($post['content'])) ?>
                    </p>
                </div>

                <!-- Image -->
                <?php if ($post['image']): ?>
                    <img class="post-image" src="uploads/posts/<?= safe($post['image']) ?>" alt="">
                <?php endif; ?>

                <!-- Like info -->
                <div class="post-footer">
                    <button class="like-btn <?= $post['user_liked'] ? 'liked' : '' ?>"
                            data-post-id="<?= $postId ?>">
                        <svg width="16" height="16" fill="<?= $post['user_liked'] ? '#e74c3c' : 'none' ?>"
                             stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
                        </svg>
                        <span class="like-count"><?= $post['like_count'] ?></span> Likes
                    </button>

                    <span style="color:var(--muted);font-size:0.85rem;margin-left:8px;">
                        💬 <?= count($comments) ?> Comments
                    </span>

                    <div class="post-actions-right">
                        <button class="share-btn" data-post-id="<?= $postId ?>" title="Share post">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                                <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                            </svg>
                            <span>Share</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ---- COMMENTS SECTION ---- -->
            <div class="card">
                <div class="card-header">
                    <h3 style="font-size:1rem;color:var(--charcoal);">
                        💬 Comments (<?= count($comments) ?>)
                    </h3>
                </div>
                <div class="card-body">

                    <?php if ($commentError): ?>
                        <div class="alert alert-error">❌ <?= safe($commentError) ?></div>
                    <?php endif; ?>

                    <!-- Existing comments -->
                    <div class="comments-list" style="margin-bottom:24px;">
                        <?php if (empty($comments)): ?>
                            <p style="color:var(--muted);text-align:center;padding:20px 0;font-size:0.9rem;">
                                No comments yet. Be the first! 👇
                            </p>
                        <?php else: ?>
                            <?php foreach ($comments as $c): ?>
                            <div class="comment-item" style="margin-bottom:16px;">
                                <div class="comment-avatar">
                                    <?= strtoupper(substr($c['name'], 0, 1)) ?>
                                </div>
                                <div class="comment-bubble" style="flex:1;">
                                    <span class="comment-author">
                                        <a href="profile.php?id=<?= $c['user_id'] ?>"
                                           style="color:inherit;">
                                            @<?= safe($c['username']) ?>
                                        </a>
                                    </span>
                                    <p class="comment-text"><?= nl2br(safe($c['content'])) ?></p>
                                    <span class="comment-time" data-timestamp="<?= $c['created_at'] ?>"><?= timeAgo($c['created_at']) ?></span>

                                    <!-- Delete own comment -->
                                    <?php if ($c['user_id'] == $userId || $post['user_id'] == $userId): ?>
                                        <a href="delete_comment.php?id=<?= $c['id'] ?>&post_id=<?= $postId ?>"
                                           style="font-size:0.75rem;color:#dc2626;margin-left:8px;"
                                           data-confirm="Delete this comment?">
                                            Delete
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Add comment form -->
                    <div class="divider"></div>
                    <h4 style="font-size:0.88rem;font-weight:600;color:var(--charcoal);margin-bottom:14px;">
                        Leave a comment
                    </h4>
                    <form method="POST" action="post_detail.php?id=<?= $postId ?>">
                        <div class="form-group">
                            <textarea name="comment"
                                      placeholder="Write your comment here..."
                                      rows="3"
                                      data-maxlength="500"
                                      required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Post Comment</button>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="js/main.js"></script>
<script src="js/jquery-features.js"></script>
</body>
</html>
