<?php
// ============================================
// following.php — View Following List
// ============================================
require_once 'includes/auth.php';
require_once 'includes/db.php';

requireLogin();

$currentUserId = getCurrentUserId();
$profileId     = intval($_GET['id'] ?? $currentUserId);

// Get profile owner
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$profileId]);
$profileUser = $stmt->fetch();

if (!$profileUser) {
    header("Location: dashboard.php");
    exit();
}

// Get list of people this user follows
$stmt = $pdo->prepare("
    SELECT u.*, f.created_at AS followed_at,
           (SELECT COUNT(*) FROM follows WHERE follower_id = ? AND following_id = u.id) AS i_follow
    FROM follows f
    JOIN users u ON f.following_id = u.id
    WHERE f.follower_id = ?
    ORDER BY f.created_at DESC
");
$stmt->execute([$currentUserId, $profileId]);
$following = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Following — ConnectU</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="app-shell">
    <?php include 'includes/nav.php'; ?>

    <div class="main-content">

        <header class="top-header">
            <h2>➡️ Following</h2>
            <a href="profile.php?id=<?= $profileId ?>" class="btn btn-outline btn-sm">← Profile</a>
        </header>

        <div class="page-body">

            <!-- Summary -->
            <div style="margin-bottom:24px;padding:16px 20px;background:var(--card-bg);border:1px solid var(--border);border-radius:var(--radius);">
                <p style="color:var(--muted);font-size:0.88rem;">
                    People that <strong style="color:var(--charcoal);">@<?= safe($profileUser['username']) ?></strong> follows
                </p>
                <p style="font-size:1.3rem;font-weight:700;color:var(--charcoal);margin-top:2px;">
                    Following <?= count($following) ?> user<?= count($following) !== 1 ? 's' : '' ?>
                </p>
            </div>

            <?php if (empty($following)): ?>
                <div class="empty-state card">
                    <div class="empty-icon">➡️</div>
                    <h3>Not following anyone yet</h3>
                    <p>
                        <?php if ($profileId == $currentUserId): ?>
                            Find interesting people to follow!
                            <a href="search.php" style="color:var(--accent);">Search users →</a>
                        <?php else: ?>
                            <?= safe($profileUser['name']) ?> isn't following anyone yet.
                        <?php endif; ?>
                    </p>
                </div>

            <?php else: ?>
                <div class="user-list">
                    <?php foreach ($following as $fu): ?>
                    <div class="user-item"
                         data-name="<?= safe($fu['name']) ?>"
                         data-username="<?= safe($fu['username']) ?>">

                        <!-- Avatar -->
                        <?php if ($fu['profile_pic'] && $fu['profile_pic'] !== 'default.png' && file_exists('uploads/profiles/' . $fu['profile_pic'])): ?>
                            <img class="user-avatar" src="uploads/profiles/<?= safe($fu['profile_pic']) ?>" alt="">
                        <?php else: ?>
                            <div class="user-avatar-placeholder">
                                <?= strtoupper(substr($fu['name'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Info -->
                        <div class="user-details">
                            <a href="profile.php?id=<?= $fu['id'] ?>" class="name">
                                <?= safe($fu['name']) ?>
                            </a>
                            <div class="handle">@<?= safe($fu['username']) ?></div>
                            <?php if ($fu['bio']): ?>
                                <div class="bio-preview"><?= safe(substr($fu['bio'], 0, 60)) ?>...</div>
                            <?php endif; ?>
                        </div>

                        <!-- Follow / Unfollow -->
                        <?php if ($fu['id'] !== $currentUserId): ?>
                            <button class="follow-btn btn btn-sm <?= $fu['i_follow'] ? 'btn-outline' : 'btn-primary' ?>"
                                    data-user-id="<?= $fu['id'] ?>">
                                <?= $fu['i_follow'] ? 'Unfollow' : 'Follow' ?>
                            </button>
                        <?php else: ?>
                            <span style="font-size:0.8rem;color:var(--muted);font-style:italic;">You</span>
                        <?php endif; ?>

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
</body>
</html>
