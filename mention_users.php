<?php
// ============================================
// mention_users.php — Returns users as JSON
// Used by the @mention autocomplete (jQuery)
// ============================================
require_once 'includes/auth.php';
require_once 'includes/db.php';

header('Content-Type: application/json');

// Must be logged in
if (!isLoggedIn()) {
    echo json_encode([]);
    exit;
}

// Fetch all users except the current one
$stmt = $pdo->prepare("
    SELECT id, name, username
    FROM users
    WHERE id != ?
    ORDER BY name ASC
    LIMIT 50
");
$stmt->execute([getCurrentUserId()]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($users);
