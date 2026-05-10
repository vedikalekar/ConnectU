<?php
// ============================================
// search.php — Search Users
// ============================================
require_once 'includes/auth.php';
require_once 'includes/db.php';

requireLogin();

$currentUserId = getCurrentUserId();
$query         = trim($_GET['q'] ?? '');
$users         = [];

// ---- Search database if query provided ----
if (!empty($query)) {
    $like = '%' . $query . '%';
    $stmt = $pdo->prepare("
        SELECT u.*,
               (SELECT COUNT(*) FROM follows WHERE following_id = u.id) AS follower_count,
               (SELECT COUNT(*) FROM follows WHERE follower_id = ? AND following_id = u.id) AS i_follow
        FROM users u
        WHERE u.id != ?
          AND (u.username LIKE ? OR u.name LIKE ?)
        ORDER BY follower_count DESC
        LIMIT 30
    ");
    $stmt->execute([$currentUserId, $currentUserId, $like, $like]);
    $users = $stmt->fetchAll();
} else {
    // No query — show all users by follower count
    $stmt = $pdo->prepare("
        SELECT u.*,
               (SELECT COUNT(*) FROM follows WHERE following_id = u.id) AS follower_count,
               (SELECT COUNT(*) FROM follows WHERE follower_id = ? AND following_id = u.id) AS i_follow
        FROM users u
        WHERE u.id != ?
        ORDER BY follower_count DESC
        LIMIT 20
    ");
    $stmt->execute([$currentUserId, $currentUserId]);
    $users = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Users — ConnectU</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="app-shell">
    <?php include 'includes/nav.php'; ?>

    <div class="main-content">

        <header class="top-header">
            <h2>🔍 Search Users</h2>
        </header>

        <div class="page-body" style="max-width:720px;padding:28px 32px;">

            <!-- Search form -->
            <div class="search-bar">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input
                    type="text"
                    id="search-input"
                    placeholder="Search by name or @username..."
                    value="<?= safe($query) ?>"
                    autofocus
                >
            </div>

            <!-- Results count -->
            <div style="margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;">
                <p style="color:var(--muted);font-size:0.88rem;">
                    <?php if ($query): ?>
                        Found <strong style="color:var(--charcoal);"><?= count($users) ?></strong>
                        result<?= count($users) !== 1 ? 's' : '' ?> for
                        "<strong style="color:var(--charcoal);"><?= safe($query) ?></strong>"
                    <?php else: ?>
                        Showing all users · <?= count($users) ?> people
                    <?php endif; ?>
                </p>
                <?php if ($query): ?>
                    <a href="search.php" class="btn btn-ghost btn-sm">Clear search</a>
                <?php endif; ?>
            </div>

            <!-- User list -->
            <?php if (empty($users)): ?>
                <div class="empty-state card">
                    <div class="empty-icon">🔍</div>
                    <h3>No users found</h3>
                    <p>Try a different search term.</p>
                </div>
                <div id="no-results" style="display:none;"></div>

            <?php else: ?>
                <div class="user-list" id="users-container">
                    <?php foreach ($users as $u): ?>
                    <div class="user-item"
                         data-name="<?= safe($u['name']) ?>"
                         data-username="<?= safe($u['username']) ?>">

                        <!-- Avatar -->
                        <?php if ($u['profile_pic'] && $u['profile_pic'] !== 'default.png' && file_exists('uploads/profiles/' . $u['profile_pic'])): ?>
                            <img class="user-avatar" src="uploads/profiles/<?= safe($u['profile_pic']) ?>" alt="">
                        <?php else: ?>
                            <div class="user-avatar-placeholder">
                                <?= strtoupper(substr($u['name'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Info -->
                        <div class="user-details">
                            <a href="profile.php?id=<?= $u['id'] ?>" class="name">
                                <?= safe($u['name']) ?>
                            </a>
                            <div class="handle">@<?= safe($u['username']) ?></div>
                            <?php if ($u['bio']): ?>
                                <div class="bio-preview"><?= safe(substr($u['bio'], 0, 70)) ?></div>
                            <?php endif; ?>
                            <div style="font-size:0.76rem;color:var(--muted);margin-top:2px;">
                                👥 <?= $u['follower_count'] ?> follower<?= $u['follower_count'] !== 1 ? 's' : '' ?>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div style="display:flex;gap:8px;flex-direction:column;align-items:flex-end;">
                            <a href="profile.php?id=<?= $u['id'] ?>" class="btn btn-ghost btn-sm">
                                View
                            </a>
                            <button class="follow-btn btn btn-sm <?= $u['i_follow'] ? 'btn-outline' : 'btn-primary' ?>"
                                    data-user-id="<?= $u['id'] ?>">
                                <?= $u['i_follow'] ? 'Unfollow' : 'Follow' ?>
                            </button>
                        </div>

                    </div>
                    <?php endforeach; ?>

                    <!-- Hidden empty state (shown by JS when filter yields no results) -->
                    <div id="no-results" style="display:none;">
                        <div class="empty-state">
                            <div class="empty-icon">😕</div>
                            <h3>No matches</h3>
                            <p>No users match your search.</p>
                        </div>
                    </div>

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
