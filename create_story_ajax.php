<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$userId = getCurrentUserId();
$textContent = trim($_POST['text_content'] ?? '');
$imageName = null;

if (empty($textContent) && empty($_FILES['image']['name'])) {
    echo json_encode(['success' => false, 'error' => 'Please provide text or an image for your story.']);
    exit();
}

// Handle image upload
if (!empty($_FILES['image']['name'])) {
    $file = $_FILES['image'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if (!in_array($ext, $allowed)) {
        echo json_encode(['success' => false, 'error' => 'Only JPG, PNG, GIF, and WEBP images are allowed.']);
        exit();
    } elseif ($file['size'] > $maxSize) {
        echo json_encode(['success' => false, 'error' => 'Image too large. Maximum size is 5MB.']);
        exit();
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'error' => 'Upload failed. Please try again.']);
        exit();
    } else {
        $imageName = uniqid('story_', true) . '.' . $ext;
        $uploadDir = 'uploads/stories/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $imageName)) {
            echo json_encode(['success' => false, 'error' => 'Could not save image. Check folder permissions.']);
            exit();
        }
    }
}

try {
    $stmt = $pdo->prepare("INSERT INTO stories (user_id, image, text_content) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $imageName, $textContent]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
