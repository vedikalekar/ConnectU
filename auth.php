<?php
// ============================================
// includes/auth.php
// Session & Authentication Helpers
// ============================================

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---- Helper Functions ----

/**
 * Check if user is logged in
 * Returns true or false
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get the current logged-in user's ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get the current logged-in user's username
 */
function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}

/**
 * Redirect if NOT logged in (use on protected pages)
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

/**
 * Redirect if ALREADY logged in (use on login/register pages)
 */
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header("Location: dashboard.php");
        exit();
    }
}

/**
 * Make text safe to display in HTML (prevent XSS attacks)
 */
function safe($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Format date nicely, e.g. "March 8, 2026 · 3:45 PM"
 */
function formatDate($dateString) {
    $date = new DateTime($dateString);
    return $date->format('F j, Y · g:i A');
}

/**
 * Time ago format, e.g. "2 hours ago"
 */
function timeAgo($dateString) {
    $now  = new DateTime();
    $past = new DateTime($dateString);
    $diff = $now->diff($past);

    if ($diff->y > 0) return $diff->y . 'y ago';
    if ($diff->m > 0) return $diff->m . 'mo ago';
    if ($diff->d > 0) return $diff->d . 'd ago';
    if ($diff->h > 0) return $diff->h . 'h ago';
    if ($diff->i > 0) return $diff->i . 'm ago';
    return 'just now';
}
?>
