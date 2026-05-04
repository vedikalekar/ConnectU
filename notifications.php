<?php
// ============================================
// notifications.php — Notifications Page
// ============================================
require_once 'includes/auth.php';
require_once 'includes/db.php';

requireLogin();

$userId = getCurrentUserId();

// ---- Gather notifications from 3 sources ----

// 1. New followers (people who followed the user)
$stmt = $pdo->prepare("
    SELECT 'follow' AS type, f.created_at, u.id AS actor_id, u.name, u.username, u.profile_pic,
           NULL AS post_id, NULL AS post_content
    FROM follows f
    JOIN users u ON f.follower_id = u.id
    WHERE f.following_id = ?
      AND f.follower_id  != ?
");
$stmt->execute([$userId, $userId]);
$followNotifs = $stmt->fetchAll();

// 2. New likes on my posts
$stmt = $pdo->prepare("
    SELECT 'like' AS type, l.created_at, u.id AS actor_id, u.name, u.username, u.profile_pic,
           p.id AS post_id, LEFT(p.content, 60) AS post_content
    FROM likes l
    JOIN users u ON l.user_id = u.id
    JOIN posts p ON l.post_id = p.id
    WHERE p.user_id = ?
      AND l.user_id != ?
");
$stmt->execute([$userId, $userId]);
$likeNotifs = $stmt->fetchAll();

// 3. New comments on my posts
$stmt = $pdo->prepare("
    SELECT 'comment' AS type, c.created_at, u.id AS actor_id, u.name, u.username, u.profile_pic,
           p.id AS post_id, LEFT(c.content, 60) AS post_content
    FROM comments c
    JOIN users u ON c.user_id = u.id
    JOIN posts p ON c.post_id = p.id
    WHERE p.user_id = ?
      AND c.user_id != ?
");
$stmt->execute([$userId, $userId]);
$commentNotifs = $stmt->fetchAll();

// ---- Merge and sort by date (newest first) ----
$allNotifs = array_merge($followNotifs, $likeNotifs, $commentNotifs);
usort($allNotifs, function ($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications — ConnectU</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="app-shell">
    <?php include 'includes/nav.php'; ?>

    <div class="main-content">

        <header class="top-header">
            <h2>🔔 Notifications</h2>
            <span style="color:var(--muted);font-size:0.88rem;">
                <?= count($allNotifs) ?> notification<?= count($allNotifs) !== 1 ? 's' : '' ?>
            </span>
        </header>

        <div class="page-body" style="max-width:680px;padding:28px 32px;">

            <!-- Filter tabs (visual only) -->
            <div style="display:flex;gap:8px;margin-bottom:24px;background:var(--card-bg);padding:6px;border-radius:var(--radius);border:1px solid var(--border);">
                <button class="btn btn-primary btn-sm" style="flex:1;" id="tab-all"
                        onclick="filterNotifs('all')">
                    All (<?= count($allNotifs) ?>)
                </button>
                <button class="btn btn-ghost btn-sm" style="flex:1;" id="tab-follow"
                        onclick="filterNotifs('follow')">
                    👥 Follows (<?= count($followNotifs) ?>)
                </button>
                <button class="btn btn-ghost btn-sm" style="flex:1;" id="tab-like"
                        onclick="filterNotifs('like')">
                    ❤️ Likes (<?= count($likeNotifs) ?>)
                </button>
                <button class="btn btn-ghost btn-sm" style="flex:1;" id="tab-comment"
                        onclick="filterNotifs('comment')">
                    💬 Comments (<?= count($commentNotifs) ?>)
                </button>
            </div>

            <?php if (empty($allNotifs)): ?>
                <div class="empty-state card">
                    <div class="empty-icon">🔕</div>
                    <h3>No notifications yet</h3>
                    <p>When people follow you, like your posts, or comment — you'll see it here.</p>
                    <a href="search.php" class="btn btn-primary" style="margin-top:16px;">
                        Find People to Follow
                    </a>
                </div>

            <?php else: ?>
                <div class="notif-list" id="notif-list">
                    <?php foreach ($allNotifs as $notif): ?>

                    <?php
                    // Set icon and message based on type
                    if ($notif['type'] === 'follow') {
                        $icon    = '👤';
                        $iconCls = 'follow';
                        $msg     = '<strong>' . safe($notif['name']) . '</strong> started following you.';
                        $link    = 'profile.php?id=' . $notif['actor_id'];
                    } elseif ($notif['type'] === 'like') {
                        $icon    = '❤️';
                        $iconCls = 'like';
                        $msg     = '<strong>' . safe($notif['name']) . '</strong> liked your post: <em>"' . safe($notif['post_content']) . '..."</em>';
                        $link    = 'post_detail.php?id=' . $notif['post_id'];
                    } else {
                        $icon    = '💬';
                        $iconCls = 'comment';
                        $msg     = '<strong>' . safe($notif['name']) . '</strong> commented: <em>"' . safe($notif['post_content']) . '..."</em>';
                        $link    = 'post_detail.php?id=' . $notif['post_id'];
                    }

                    // Mark recent (last 24h) as "unread"
                    $isRecent = (time() - strtotime($notif['created_at'])) < 86400;
                    ?>

                    <div class="notif-item <?= $isRecent ? 'unread' : '' ?>"
                         data-type="<?= $notif['type'] ?>"
                         onclick="window.location='<?= $link ?>'"
                         style="cursor:pointer;">

                        <div class="notif-icon <?= $iconCls ?>"><?= $icon ?></div>

                        <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:0;">
                            <!-- Actor avatar -->
                            <?php if ($notif['profile_pic'] && $notif['profile_pic'] !== 'default.png' && file_exists('uploads/profiles/' . $notif['profile_pic'])): ?>
                                <img src="uploads/profiles/<?= safe($notif['profile_pic']) ?>"
                                     style="width:36px;height:36px;border-radius:50%;object-fit:cover;flex-shrink:0;" alt="">
                            <?php else: ?>
                                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--accent) 0%,#e84393 100%);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:0.85rem;flex-shrink:0;">
                                    <?= strtoupper(substr($notif['name'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>

                            <p class="notif-text"><?= $msg ?></p>
                        </div>

                        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
                            <span class="notif-time" data-timestamp="<?= $notif['created_at'] ?>"><?= timeAgo($notif['created_at']) ?></span>
                            <?php if ($isRecent): ?>
                                <span style="width:8px;height:8px;border-radius:50%;background:var(--accent);display:block;"></span>
                            <?php endif; ?>
                        </div>

                    </div>

                    <?php endforeach; ?>
                </div>

            <?php endif; ?>

        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="js/main.js"></script>
<script src="js/jquery-features.js"></script>
<script>
// ---- Filter notifications by type ----
function filterNotifs(type) {
    // Update tab styles
    ['all','follow','like','comment'].forEach(function(t) {
        var btn = document.getElementById('tab-' + t);
        if (btn) {
            btn.className = 'btn btn-sm';
            btn.className += (t === type) ? ' btn-primary' : ' btn-ghost';
            btn.style.flex = '1';
        }
    });

    // Show/hide notification items
    document.querySelectorAll('.notif-item').forEach(function(item) {
        if (type === 'all' || item.getAttribute('data-type') === type) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}
</script>
</body>
</html>
