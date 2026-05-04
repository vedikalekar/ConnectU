<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$userId = getCurrentUserId();

// Get active stories from current user AND people they follow, within the last 24 hours
// We also want to know if all stories for a user have been viewed by the current user.
$stmt = $pdo->prepare("
    SELECT 
        s.id AS story_id,
        s.image,
        s.text_content,
        s.created_at,
        u.id AS user_id,
        u.name,
        u.username,
        u.profile_pic,
        (SELECT COUNT(*) FROM story_views sv WHERE sv.story_id = s.id AND sv.viewer_id = :uid) AS is_viewed
    FROM stories s
    JOIN users u ON s.user_id = u.id
    WHERE (s.user_id = :uid2 
       OR s.user_id IN (SELECT following_id FROM follows WHERE follower_id = :uid3))
      AND s.created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
    ORDER BY s.created_at ASC
");
$stmt->execute(['uid' => $userId, 'uid2' => $userId, 'uid3' => $userId]);
$storiesRaw = $stmt->fetchAll();

// Group by user
$groupedStories = [];
foreach ($storiesRaw as $row) {
    $uid = $row['user_id'];
    if (!isset($groupedStories[$uid])) {
        $groupedStories[$uid] = [
            'user_id' => $uid,
            'name' => $row['name'],
            'username' => $row['username'],
            'profile_pic' => $row['profile_pic'],
            'all_viewed' => true,
            'stories' => []
        ];
    }
    
    $isViewed = $row['is_viewed'] > 0;
    if (!$isViewed) {
        $groupedStories[$uid]['all_viewed'] = false;
    }
    
    $groupedStories[$uid]['stories'][] = [
        'id' => $row['story_id'],
        'image' => $row['image'],
        'text_content' => $row['text_content'],
        'created_at' => $row['created_at'],
        'is_viewed' => $isViewed
    ];
}

// Ensure current user is at the very beginning of the list if they have a story
$currentUserStory = null;
if (isset($groupedStories[$userId])) {
    $currentUserStory = $groupedStories[$userId];
    unset($groupedStories[$userId]);
}

$finalList = array_values($groupedStories);

// Sort: unviewed users first, then viewed users
usort($finalList, function($a, $b) {
    if ($a['all_viewed'] === $b['all_viewed']) return 0;
    return $a['all_viewed'] ? 1 : -1;
});

if ($currentUserStory) {
    array_unshift($finalList, $currentUserStory);
}

echo json_encode(['success' => true, 'data' => $finalList]);
