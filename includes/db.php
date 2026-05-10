<?php
// ============================================
// includes/db.php
// Database Connection File
// ============================================

// --- Your database settings ---
$host     = "localhost";   // Usually localhost
$dbname   = "socialmini";  // Database name from database.sql
$username = "root";        // Your MySQL username
$password = "vedika@11";            // Your MySQL password (empty for XAMPP default)

// Connect to database using PDO (beginner-friendly error messages)
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

    // Force utf8mb4 so emojis (4-byte chars) are stored correctly
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");

    // Show errors during development
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Return results as associative arrays (like $row['name'])
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // If connection fails, show a friendly error
    die("
    <div style='font-family:sans-serif;padding:40px;background:#fff5f5;color:#c0392b;border-radius:8px;margin:40px auto;max-width:600px;border:1px solid #f5c6cb;'>
        <h2>⚠️ Database Connection Error</h2>
        <p>Could not connect to the database. Please check:</p>
        <ul>
            <li>Is XAMPP/WAMP running? Start Apache + MySQL.</li>
            <li>Did you run <strong>database.sql</strong> in phpMyAdmin?</li>
            <li>Are your username/password correct in <strong>includes/db.php</strong>?</li>
        </ul>
        <p><small>Error detail: " . htmlspecialchars($e->getMessage()) . "</small></p>
    </div>
    ");
}
?>
