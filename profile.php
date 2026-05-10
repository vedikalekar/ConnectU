<?php
// ============================================
// profile.php — User Profile Page
// ============================================
require_once 'includes/auth.php';
require_once 'includes/db.php';

requireLogin();

$currentUserId = getCurrentUserId();
$profileId     = intval($_GET['id'] ?? $currentUserId);

// ---- Get profile user ----
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$profileId]);
$user = $stmt->fetch();

if (!$user) {
    echo "<p style='padding:40px;font-family:sans-serif;'>User not found. <a href='dashboard.php'>Go home</a></p>";
    exit();
}

// ---- Get stats ----
// Posts count
$pCount = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE user_id = ?");
$pCount->execute([$profileId]);
$postsCount = $pCount->fetchColumn();

// Followers count
$fCount = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE following_id = ?");
$fCount->execute([$profileId]);
$followersCount = $fCount->fetchColumn();

// Following count
$fgCount = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = ?");
$fgCount->execute([$profileId]);
$followingCount = $fgCount->fetchColumn();

// Is current user following this profile?
$isFollowing = false;
if ($currentUserId !== $profileId) {
    $chk = $pdo->prepare("SELECT id FROM follows WHERE follower_id = ? AND following_id = ?");
    $chk->execute([$currentUserId, $profileId]);
    $isFollowing = (bool)$chk->fetch();
}

// ---- Get user's posts ----
$posts = $pdo->prepare("
    SELECT p.*,
           (SELECT COUNT(*) FROM likes    WHERE post_id = p.id) AS like_count,
           (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count
    FROM posts p
    WHERE p.user_id = ? AND p.id NOT IN (SELECT post_id FROM hidden_posts WHERE user_id = ?)
    ORDER BY p.created_at DESC
");
$posts->execute([$profileId, $currentUserId]);
$posts = $posts->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= safe($user['name']) ?> — ConnectU</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="app-shell">
    <?php include 'includes/nav.php'; ?>

    <div class="main-content">

        <header class="top-header">
            <h2>👤 Profile</h2>
            <?php if ($currentUserId == $profileId): ?>
                <a href="edit_profile.php" class="btn btn-outline btn-sm">⚙️ Edit Profile</a>
            <?php endif; ?>
        </header>

        <div class="page-body" style="max-width:760px;padding:28px 32px;">

            <!-- ---- PROFILE HEADER ---- -->
            <div class="profile-header">
                <!-- Cover background -->
                <div class="profile-cover"></div>

                <div class="profile-header-body">
                    <!-- Profile picture -->
                    <div class="profile-pic-wrap">
                        <?php if ($user['profile_pic'] && $user['profile_pic'] !== 'default.png' && file_exists('uploads/profiles/' . $user['profile_pic'])): ?>
                            <img src="uploads/profiles/<?= safe($user['profile_pic']) ?>" alt="">
                        <?php else: ?>
                            <div class="avatar-lg">
                                <?= strtoupper(substr($user['name'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Name / username / bio -->
                    <div class="profile-info">
                        <h2><?= safe($user['name']) ?></h2>
                        <p class="username">@<?= safe($user['username']) ?></p>
                        <?php if ($user['bio']): ?>
                            <p class="bio"><?= nl2br(safe($user['bio'])) ?></p>
                        <?php else: ?>
                            <p class="bio" style="color:var(--muted);font-style:italic;">No bio yet.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Follow / Edit buttons -->
                    <div class="profile-actions">
                        <?php if ($currentUserId == $profileId): ?>
                            <a href="edit_profile.php" class="btn btn-outline btn-sm">Edit Profile</a>
                        <?php else: ?>
                            <button class="btn follow-btn btn-sm <?= $isFollowing ? 'btn-outline' : 'btn-primary' ?>"
                                    data-user-id="<?= $profileId ?>">
                                <?= $isFollowing ? 'Unfollow' : 'Follow' ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Stats bar -->
                <div class="profile-stats">
                    <div class="stat-item">
                        <span class="num"><?= $postsCount ?></span>
                        <span class="label">Posts</span>
                    </div>
                    <div class="stat-item">
                        <a href="followers.php?id=<?= $profileId ?>" style="text-decoration:none;">
                            <span class="num" id="followers-count"><?= $followersCount ?></span>
                            <span class="label">Followers</span>
                        </a>
                    </div>
                    <div class="stat-item">
                        <a href="following.php?id=<?= $profileId ?>" style="text-decoration:none;">
                            <span class="num"><?= $followingCount ?></span>
                            <span class="label">Following</span>
                        </a>
                    </div>
                    <div class="stat-item">
                        <span class="num"><?= date('Y') - date('Y', strtotime($user['created_at'])) ?> yr</span>
                        <span class="label">Member</span>
                    </div>
                </div>
            </div>

            <!-- ---- POSTS SECTION ---- -->
            <h3 style="font-size:1rem;font-weight:700;color:var(--charcoal);margin-bottom:16px;">
                📝 Posts
            </h3>

            <?php if (empty($posts)): ?>
                <div class="empty-state card">
                    <div class="empty-icon">📭</div>
                    <h3>No posts yet</h3>
                    <?php if ($currentUserId == $profileId): ?>
                        <p>Share your first post with your followers!</p>
                        <a href="create_post.php" class="btn btn-primary" style="margin-top:16px;">Create Post</a>
                    <?php else: ?>
                        <p><?= safe($user['name']) ?> hasn't posted anything yet.</p>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                <div class="post-card">

                    <div class="post-header">
                        <?php if ($user['profile_pic'] && $user['profile_pic'] !== 'default.png'): ?>
                            <img class="avatar" src="uploads/profiles/<?= safe($user['profile_pic']) ?>" alt="">
                        <?php else: ?>
                            <div class="avatar-placeholder">
                                <?= strtoupper(substr($user['name'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>

                        <div class="post-meta">
                            <span class="author"><?= safe($user['name']) ?></span>
                            <div class="time"><?= timeAgo($post['created_at']) ?></div>
                        </div>

                        <?php if ($currentUserId == $profileId): ?>
                        <div class="dropdown">
                            <button class="btn btn-ghost btn-sm" data-dropdown-toggle="pm-<?= $post['id'] ?>" style="padding:6px 10px;">···</button>
                            <div class="dropdown-menu" id="pm-<?= $post['id'] ?>">
                                <a href="edit_post.php?id=<?= $post['id'] ?>">✏️ Edit</a>
                                <a href="#" class="danger sm-delete-post" data-post-id="<?= $post['id'] ?>">🗑️ Delete</a>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="dropdown">
                            <button class="btn btn-ghost btn-sm" data-dropdown-toggle="pm-<?= $post['id'] ?>" style="padding:6px 10px;">···</button>
                            <div class="dropdown-menu" id="pm-<?= $post['id'] ?>">
                                <a href="#" class="sm-hide-post" data-post-id="<?= $post['id'] ?>">👁️‍🗨️ Hide</a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="post-content">
                        <p><?= nl2br(safe($post['content'])) ?></p>
                    </div>

                    <?php if ($post['image']): ?>
                        <a href="post_detail.php?id=<?= $post['id'] ?>">
                            <img class="post-image" src="uploads/posts/<?= safe($post['image']) ?>" alt="">
                        </a>
                    <?php endif; ?>

                    <div class="post-footer">
                        <span style="display:flex;align-items:center;gap:5px;color:var(--muted);font-size:0.85rem;">
                            ❤️ <?= $post['like_count'] ?>
                        </span>
                        <span style="display:flex;align-items:center;gap:5px;color:var(--muted);font-size:0.85rem;margin-left:12px;">
                            💬 <?= $post['comment_count'] ?>
                        </span>
                        <div class="post-actions-right">
                            <a href="post_detail.php?id=<?= $post['id'] ?>" class="btn btn-ghost btn-sm">View →</a>
                        </div>
                    </div>

                </div>
                <?php endforeach; ?>

            <?php endif; ?>

        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="js/main.js"></script>
<script src="js/jquery-features.js"></script>
</body>
</html>
