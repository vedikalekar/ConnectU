<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$userId = getCurrentUserId();
$storyId = $_GET['story_id'] ?? null;

if (!$storyId) {
    echo json_encode(['success' => false, 'error' => 'Missing story ID']);
    exit();
}

// First verify the story belongs to the current user
$checkStmt = $pdo->prepare("SELECT user_id FROM stories WHERE id = ?");
$checkStmt->execute([$storyId]);
$story = $checkStmt->fetch();

if (!$story || $story['user_id'] != $userId) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized or story not found']);
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.username, u.profile_pic, sv.viewed_at
        FROM story_views sv
        JOIN users u ON sv.viewer_id = u.id
        WHERE sv.story_id = ?
        ORDER BY sv.viewed_at DESC
    ");
    $stmt->execute([$storyId]);
    $viewers = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'viewers' => $viewers]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
