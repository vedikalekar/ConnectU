<?php
// ============================================
// logout.php — Log Out User
// ============================================
require_once 'includes/auth.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session data
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to landing page
header("Location: index.php");
exit();
