<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$userId = getCurrentUserId();
$data = json_decode(file_get_contents('php://input'), true);
$storyId = $data['story_id'] ?? null;

if (!$storyId) {
    echo json_encode(['success' => false, 'error' => 'Missing story ID']);
    exit();
}

try {
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO story_views (story_id, viewer_id) 
        VALUES (?, ?)
    ");
    $stmt->execute([$storyId, $userId]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
