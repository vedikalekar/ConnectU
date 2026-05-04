<?php
// ============================================
// dashboard.php — Main News Feed
// ============================================
require_once 'includes/auth.php';
require_once 'includes/db.php';

requireLogin(); // Must be logged in

$userId = getCurrentUserId();

// ---- Get posts from people the user follows + own posts ----
// We join with users table to get author info and likes count
$stmt = $pdo->prepare("
    SELECT
        p.*,
        u.name,
        u.username,
        u.profile_pic,
        (SELECT COUNT(*) FROM likes    WHERE post_id = p.id) AS like_count,
        (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count,
        (SELECT COUNT(*) FROM likes    WHERE post_id = p.id AND user_id = :uid) AS user_liked
    FROM posts p
    JOIN users u ON p.user_id = u.id
    WHERE (p.user_id = :uid2
       OR p.user_id IN (
           SELECT following_id FROM follows WHERE follower_id = :uid3
       ))
       AND p.id NOT IN (SELECT post_id FROM hidden_posts WHERE user_id = :uid4)
    ORDER BY p.created_at DESC
    LIMIT 30
");
$stmt->execute(['uid' => $userId, 'uid2' => $userId, 'uid3' => $userId, 'uid4' => $userId]);
$posts = $stmt->fetchAll();

// ---- Get suggested users to follow (people not yet followed) ----
$stmtSuggest = $pdo->prepare("
    SELECT u.id, u.name, u.username, u.profile_pic,
           (SELECT COUNT(*) FROM follows WHERE following_id = u.id) AS follower_count
    FROM users u
    WHERE u.id != ?
      AND u.id NOT IN (
          SELECT following_id FROM follows WHERE follower_id = ?
      )
    ORDER BY follower_count DESC
    LIMIT 5
");
$stmtSuggest->execute([$userId, $userId]);
$suggested = $stmtSuggest->fetchAll();

// ---- Get current user ----
$stmtMe = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtMe->execute([$userId]);
$me = $stmtMe->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Feed — ConnectU</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- <link rel="stylesheet" href="css/stories.css"> -->
    <link rel="icon" type="image/png" href="new_logo.png">

</head>
<body>

<div class="app-shell">

    <!-- ---- SIDEBAR NAV ---- -->
    <?php include 'includes/nav.php'; ?>

    <!-- ---- MAIN CONTENT ---- -->
    <div class="main-content">

        <!-- ---- TOP HEADER ---- -->
        <header class="top-header">
            <h2>🏠 Home Feed</h2>
            <div class="header-actions">
                <a href="create_post.php" class="btn btn-primary btn-sm">
                    + New Post
                </a>
                <a href="search.php" class="btn btn-outline btn-sm">
                    🔍 Search
                </a>
            </div>
        </header>

        <!-- ---- PAGE BODY ---- -->
        <div style="display:flex;gap:28px;padding:28px 32px;align-items:flex-start;">

            <!-- ---- POSTS FEED ---- -->
            <div style="flex:1;max-width:640px;">

                <!-- ---- STORIES TRAY ---- -->
                <!--
                <div class="stories-tray" id="stories-tray">
                    Populated by JS
                </div>
                -->

                <?php if (empty($posts)): ?>
                    <!-- Empty state -->
                    <div class="empty-state card" style="padding:60px 20px;">
                        <div class="empty-icon">📭</div>
                        <h3>Your feed is empty</h3>
                        <p>Follow some people or create your first post to get started!</p>
                        <div style="margin-top:20px;display:flex;gap:10px;justify-content:center;">
                            <a href="search.php"      class="btn btn-primary">Find People</a>
                            <a href="create_post.php" class="btn btn-outline">Create Post</a>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Posts loop -->
                    <?php foreach ($posts as $post): ?>
                    <div class="post-card">

                        <!-- Post author header -->
                        <div class="post-header">
                            <!-- Profile picture or initial -->
                            <?php if ($post['profile_pic'] && $post['profile_pic'] !== 'default.png'): ?>
                                <img class="avatar"
                                     src="uploads/profiles/<?= safe($post['profile_pic']) ?>"
                                     alt="<?= safe($post['name']) ?>">
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
                                    @<?= safe($post['username']) ?> · <span data-timestamp="<?= $post['created_at'] ?>"><?= timeAgo($post['created_at']) ?></span>
                                </div>
                            </div>

                            <!-- Options dropdown (only for own posts) -->
                            <?php if ($post['user_id'] == $userId): ?>
                            <div class="dropdown">
                                <button class="btn btn-ghost btn-sm"
                                        data-dropdown-toggle="menu-<?= $post['id'] ?>"
                                        style="padding:6px 10px;font-size:1rem;">
                                    ···
                                </button>
                                <div class="dropdown-menu" id="menu-<?= $post['id'] ?>">
                                    <a href="edit_post.php?id=<?= $post['id'] ?>">✏️ Edit</a>
                                    <a href="#" class="danger sm-delete-post" data-post-id="<?= $post['id'] ?>">
                                        🗑️ Delete
                                    </a>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="dropdown">
                                <button class="btn btn-ghost btn-sm"
                                        data-dropdown-toggle="menu-<?= $post['id'] ?>"
                                        style="padding:6px 10px;font-size:1rem;">
                                    ···
                                </button>
                                <div class="dropdown-menu" id="menu-<?= $post['id'] ?>">
                                    <a href="#" class="sm-hide-post" data-post-id="<?= $post['id'] ?>">
                                        👁️‍🗨️ Hide
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Post content -->
                        <div class="post-content">
                            <p><?= nl2br(safe($post['content'])) ?></p>
                        </div>

                        <!-- Post image (if any) -->
                        <?php if ($post['image']): ?>
                            <a href="post_detail.php?id=<?= $post['id'] ?>">
                                <img class="post-image"
                                     src="uploads/posts/<?= safe($post['image']) ?>"
                                     alt="Post image">
                            </a>
                        <?php endif; ?>

                        <!-- Like / Comment / View buttons -->
                        <div class="post-footer">
                            <button class="like-btn <?= $post['user_liked'] ? 'liked' : '' ?>"
                                    data-post-id="<?= $post['id'] ?>">
                                <svg width="16" height="16" fill="<?= $post['user_liked'] ? '#e74c3c' : 'none' ?>"
                                     stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
                                </svg>
                                <span class="like-count"><?= $post['like_count'] ?></span>
                            </button>

                            <button class="comment-btn"
                                    onclick="toggleComments(<?= $post['id'] ?>)">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                                </svg>
                                <?= $post['comment_count'] ?>
                            </button>

                            <div class="post-actions-right">
                                <a href="post_detail.php?id=<?= $post['id'] ?>"
                                   class="btn btn-ghost btn-sm">
                                   View →
                                </a>
                            </div>
                        </div>

                        <!-- Comments section (hidden by default) -->
                        <div id="comments-<?= $post['id'] ?>" style="display:none;border-top:1px solid var(--border);">
                            <?php
                            // Get comments for this post
                            $cStmt = $pdo->prepare("
                                SELECT c.*, u.username, u.name
                                FROM comments c
                                JOIN users u ON c.user_id = u.id
                                WHERE c.post_id = ?
                                ORDER BY c.created_at ASC
                                LIMIT 10
                            ");
                            $cStmt->execute([$post['id']]);
                            $comments = $cStmt->fetchAll();
                            ?>

                            <div class="comments-section">
                                <?php if (!empty($comments)): ?>
                                    <h4><?= count($comments) ?> Comment<?= count($comments) !== 1 ? 's' : '' ?></h4>
                                    <div class="comments-list">
                                        <?php foreach ($comments as $c): ?>
                                        <div class="comment-item">
                                            <div class="comment-avatar">
                                                <?= strtoupper(substr($c['name'], 0, 1)) ?>
                                            </div>
                                            <div class="comment-bubble">
                                                <span class="comment-author">@<?= safe($c['username']) ?></span>
                                                <p class="comment-text"><?= safe($c['content']) ?></p>
                                                <span class="comment-time" data-timestamp="<?= $c['created_at'] ?>"><?= timeAgo($c['created_at']) ?></span>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Add comment form -->
                                <form class="comment-form" data-post-id="<?= $post['id'] ?>">
                                    <input type="text" name="comment" placeholder="Write a comment..." maxlength="500">
                                    <button type="submit" class="btn btn-primary btn-sm">Post</button>
                                </form>
                            </div>
                        </div>

                    </div>
                    <?php endforeach; ?>

                <?php endif; ?>

            </div>

            <!-- ---- SIDEBAR: Suggested Users ---- -->
            <div style="width:280px;flex-shrink:0;position:sticky;top:90px;">

                <!-- Suggested users to follow -->
                <?php if (!empty($suggested)): ?>
                <div class="card" style="margin-bottom:20px;">
                    <div class="card-header">
                        <h4 style="font-size:0.88rem;font-weight:700;color:var(--charcoal);">Who to follow</h4>
                    </div>
                    <div style="padding:8px 0;">
                        <?php foreach ($suggested as $u): ?>
                        <div style="display:flex;align-items:center;gap:12px;padding:10px 18px;">
                            <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--accent) 0%,#e84393 100%);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:0.88rem;flex-shrink:0;">
                                <?= strtoupper(substr($u['name'], 0, 1)) ?>
                            </div>
                            <div style="flex:1;min-width:0;">
                                <a href="profile.php?id=<?= $u['id'] ?>"
                                   style="color:var(--charcoal);font-weight:600;font-size:0.85rem;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    <?= safe($u['name']) ?>
                                </a>
                                <span style="color:var(--muted);font-size:0.77rem;">@<?= safe($u['username']) ?></span>
                            </div>
                            <button class="btn btn-primary btn-sm follow-btn"
                                    data-user-id="<?= $u['id'] ?>"
                                    style="padding:5px 12px;font-size:0.78rem;">
                                Follow
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Quick links -->
                <div class="card">
                    <div class="card-body" style="padding:16px 20px;">
                        <h4 style="font-size:0.82rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:12px;">Quick Links</h4>
                        <div style="display:flex;flex-direction:column;gap:8px;">
                            <a href="followers.php?id=<?= $userId ?>" style="font-size:0.85rem;color:var(--ink);">👥 Your Followers</a>
                            <a href="following.php?id=<?= $userId ?>" style="font-size:0.85rem;color:var(--ink);">➡️ Following</a>
                            <a href="notifications.php" style="font-size:0.85rem;color:var(--ink);">🔔 Notifications</a>
                            <a href="edit_profile.php" style="font-size:0.85rem;color:var(--ink);">⚙️ Edit Profile</a>
                        </div>
                    </div>
                </div>

            </div>

        </div><!-- end flex -->

    </div><!-- end main-content -->
</div><!-- end app-shell -->

<!-- ---- STORIES MODALS ---- -->
<!--
<div class="sm-modal-overlay" id="create-story-modal">
    <div class="sm-modal-content">
        <button class="sm-modal-close" onclick="closeModal('create-story-modal')">&times;</button>
        <h3 style="margin-bottom:16px;">Create Story</h3>
        <form id="create-story-form" enctype="multipart/form-data">
            <div class="form-group">
                <textarea name="text_content" placeholder="Type something..." class="form-control" style="width:100%; border-radius:8px; padding:10px; border:1px solid var(--border); background:var(--cream);"></textarea>
            </div>
            <div class="form-group" style="margin-top:12px;">
                <label style="display:block; margin-bottom:6px; font-size:0.85rem; color:var(--muted);">Add a Photo (Optional)</label>
                <input type="file" name="image" accept="image/*" class="form-control" style="width:100%;">
            </div>
            <button type="submit" class="btn btn-primary btn-block" style="margin-top:16px;">Post Story</button>
        </form>
    </div>
</div>

<div class="sm-modal-overlay" id="view-story-modal">
    <div class="story-viewer-container">
        <button class="sm-modal-close" onclick="closeModal('view-story-modal')" style="color:#fff; z-index:20;">&times;</button>
        
        <div class="story-progress-bar-container" id="story-progress-container">
        </div>

        <div class="story-header">
            <img src="" id="story-viewer-avatar" alt="Avatar">
            <div class="name" id="story-viewer-name"></div>
            <div class="time" id="story-viewer-time"></div>
        </div>

        <div class="story-content-area" id="story-content-area">
            <img src="" id="story-viewer-img" style="display:none;">
            <p id="story-viewer-text"></p>
        </div>

        <div class="story-nav-area">
            <div class="story-nav-left" onclick="prevStory()"></div>
            <div class="story-nav-right" onclick="nextStory()"></div>
        </div>
        
        <div class="story-footer" id="story-footer-actions" style="display:none;">
            <button class="viewers-btn" onclick="openViewersModal()">
                👁 <span id="story-views-count">0</span> Viewers
            </button>
        </div>
    </div>
</div>

<div class="sm-modal-overlay" id="story-viewers-modal">
    <div class="sm-modal-content">
        <button class="sm-modal-close" onclick="closeModal('story-viewers-modal')">&times;</button>
        <h3 style="margin-bottom:8px;">Story Viewers</h3>
        <div class="viewers-list" id="viewers-list-container">
        </div>
    </div>
</div>
-->

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="js/main.js"></script>
<script src="js/jquery-features.js"></script>
<!-- <script src="js/stories.js"></script> -->
</body>
</html>
