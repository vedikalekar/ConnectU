<?php
// ============================================
// followers.php — View Followers List
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

// Get list of followers
$stmt = $pdo->prepare("
    SELECT u.*, f.created_at AS followed_at,
           (SELECT COUNT(*) FROM follows WHERE follower_id = ? AND following_id = u.id) AS i_follow
    FROM follows f
    JOIN users u ON f.follower_id = u.id
    WHERE f.following_id = ?
    ORDER BY f.created_at DESC
");
$stmt->execute([$currentUserId, $profileId]);
$followers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Followers of <?= safe($profileUser['name']) ?> — ConnectU</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="app-shell">
    <?php include 'includes/nav.php'; ?>

    <div class="main-content">

        <header class="top-header">
            <h2>👥 Followers</h2>
            <a href="profile.php?id=<?= $profileId ?>" class="btn btn-outline btn-sm">← Profile</a>
        </header>

        <div class="page-body">

            <!-- Profile summary -->
            <div style="margin-bottom:24px;padding:16px 20px;background:var(--card-bg);border:1px solid var(--border);border-radius:var(--radius);">
                <p style="color:var(--muted);font-size:0.88rem;">
                    People who follow <strong style="color:var(--charcoal);">@<?= safe($profileUser['username']) ?></strong>
                </p>
                <p style="font-size:1.3rem;font-weight:700;color:var(--charcoal);margin-top:2px;">
                    <?= count($followers) ?> follower<?= count($followers) !== 1 ? 's' : '' ?>
                </p>
            </div>

            <?php if (empty($followers)): ?>
                <div class="empty-state card">
                    <div class="empty-icon">👥</div>
                    <h3>No followers yet</h3>
                    <p>
                        <?php if ($profileId == $currentUserId): ?>
                            Share your profile and create great posts to attract followers!
                        <?php else: ?>
                            <?= safe($profileUser['name']) ?> doesn't have any followers yet.
                        <?php endif; ?>
                    </p>
                </div>

            <?php else: ?>
                <div class="user-list">
                    <?php foreach ($followers as $follower): ?>
                    <div class="user-item"
                         data-name="<?= safe($follower['name']) ?>"
                         data-username="<?= safe($follower['username']) ?>">

                        <!-- Avatar -->
                        <?php if ($follower['profile_pic'] && $follower['profile_pic'] !== 'default.png' && file_exists('uploads/profiles/' . $follower['profile_pic'])): ?>
                            <img class="user-avatar" src="uploads/profiles/<?= safe($follower['profile_pic']) ?>" alt="">
                        <?php else: ?>
                            <div class="user-avatar-placeholder">
                                <?= strtoupper(substr($follower['name'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>

                        <!-- User info -->
                        <div class="user-details">
                            <a href="profile.php?id=<?= $follower['id'] ?>" class="name">
                                <?= safe($follower['name']) ?>
                            </a>
                            <div class="handle">@<?= safe($follower['username']) ?></div>
                            <?php if ($follower['bio']): ?>
                                <div class="bio-preview"><?= safe(substr($follower['bio'], 0, 60)) ?>...</div>
                            <?php endif; ?>
                        </div>

                        <!-- Follow back / Unfollow button -->
                        <?php if ($follower['id'] !== $currentUserId): ?>
                            <button class="follow-btn btn btn-sm <?= $follower['i_follow'] ? 'btn-outline' : 'btn-primary' ?>"
                                    data-user-id="<?= $follower['id'] ?>">
                                <?= $follower['i_follow'] ? 'Unfollow' : 'Follow Back' ?>
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
