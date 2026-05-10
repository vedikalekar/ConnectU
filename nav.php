<?php
// ============================================
// includes/nav.php
// Shared Sidebar Navigation
// ============================================
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

// Get current user info
$currentUser = null;
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([getCurrentUserId()]);
    $currentUser = $stmt->fetch();
}

// Count unread notifications (new follows/likes/comments since yesterday)
$notifCount = 0;
if ($currentUser) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM follows
        WHERE following_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $stmt->execute([$currentUser['id']]);
    $notifCount = $stmt->fetchColumn();
}
?>
<!-- ============================================
     SIDEBAR NAVIGATION
     ============================================ -->
<aside class="sidebar">

    <!-- Logo -->
    <div class="sidebar-logo">
        <h1>Connect<span>U</span></h1>
    </div>

    <!-- Logged-in user info -->
    <?php if ($currentUser): ?>
    <div class="sidebar-user">
        <?php if ($currentUser['profile_pic'] && $currentUser['profile_pic'] !== 'default.png' && file_exists('uploads/profiles/' . $currentUser['profile_pic'])): ?>
            <img src="uploads/profiles/<?= safe($currentUser['profile_pic']) ?>" alt="profile">
        <?php else: ?>
            <div style="width:40px;height:40px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:1rem;flex-shrink:0;">
                <?= strtoupper(substr($currentUser['name'], 0, 1)) ?>
            </div>
        <?php endif; ?>
        <div class="user-info">
            <div class="name"><?= safe($currentUser['name']) ?></div>
            <div class="username">@<?= safe($currentUser['username']) ?></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Nav Links -->
    <nav class="sidebar-nav">

        <a href="dashboard.php" title="Home Feed">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            <span>Home</span>
        </a>

        <a href="create_post.php" title="Create Post">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>
            </svg>
            <span>New Post</span>
        </a>

        <a href="profile.php?id=<?= $currentUser['id'] ?? '' ?>" title="My Profile">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            <span>Profile</span>
        </a>

        <a href="search.php" title="Search Users">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <span>Search</span>
        </a>

        <a href="notifications.php" title="Notifications" style="position:relative;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/>
            </svg>
            <span>Notifications</span>
            <?php if ($notifCount > 0): ?>
                <span class="badge" style="margin-left:auto;"><?= $notifCount ?></span>
            <?php endif; ?>
        </a>

        <div class="nav-divider"></div>

        <a href="edit_profile.php" title="Edit Profile">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
            </svg>
            <span>Edit Profile</span>
        </a>

    </nav>

    <!-- Logout -->
    <div class="sidebar-footer">
        <a href="logout.php" title="Logout">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            <span>Log out</span>
        </a>
    </div>

</aside>
