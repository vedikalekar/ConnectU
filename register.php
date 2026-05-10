<?php
// ============================================
// register.php — User Registration
// ============================================
require_once 'includes/auth.php';
require_once 'includes/db.php';

redirectIfLoggedIn(); // If already logged in, go to dashboard

$errors  = [];
$success = '';

// ---- Handle form submission ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get and clean inputs
    $name     = trim($_POST['name']     ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password =       $_POST['password'] ?? '';
    $confirm  =       $_POST['confirm']  ?? '';

    // ---- Validation ----
    if (empty($name)) {
        $errors[] = 'Please enter your full name.';
    }

    if (empty($username) || strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, and underscores.';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    // ---- Check if username or email already exists ----
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        $existing = $stmt->fetch();

        if ($existing) {
            $errors[] = 'That username or email is already taken. Try another.';
        }
    }

    // ---- Save to database ----
    if (empty($errors)) {
        // Hash the password (never store plain text!)
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (name, username, email, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $username, $email, $hashedPassword]);

        $newUserId = $pdo->lastInsertId();

        // Log them in automatically
        $_SESSION['user_id']  = $newUserId;
        $_SESSION['username'] = $username;

        // Redirect to dashboard
        header("Location: dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — ConnectU</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="auth-page">

    <!-- ---- LEFT PANEL (decorative) ---- -->
    <div class="auth-left">
        <div class="auth-left-content">
            <h2>Join <span>ConnectU</span> today</h2>
            <p>Create your account and start sharing moments, following friends, and building real connections.</p>

            <!-- <div class="auth-testimonial">
                <blockquote>"ConnectU feels like home. No ads, no algorithms — just people I care about."</blockquote>
                <div class="author">
                    <div class="author-avatar">A</div>
                    <div class="author-info">
                        <div class="name">Alice Johnson</div>
                        <div class="handle">@alice</div>
                    </div>
                </div>
            </div> -->

            <div style="margin-top:24px;display:flex;gap:24px;">
                <div>
                    <div style="color:#fff;font-weight:700;font-size:1.4rem;">500+</div>
                    <div style="color:rgba(255,255,255,0.4);font-size:0.82rem;">Users</div>
                </div>
                <div>
                    <div style="color:#fff;font-weight:700;font-size:1.4rem;">2K+</div>
                    <div style="color:rgba(255,255,255,0.4);font-size:0.82rem;">Posts</div>
                </div>
                <div>
                    <div style="color:#fff;font-weight:700;font-size:1.4rem;">10K+</div>
                    <div style="color:rgba(255,255,255,0.4);font-size:0.82rem;">Connections</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ---- RIGHT PANEL (form) ---- -->
    <div class="auth-right">
        <div class="auth-form-container">
            <a href="index.php" class="auth-logo">Connect<span>U</span></a>
            <h3>Create your account</h3>
            <p class="subtitle">Already have one? <a href="login.php">Log in</a></p>

            <!-- Error messages -->
            <?php if (!empty($errors)): ?>
                <?php foreach ($errors as $error): ?>
                    <div class="alert alert-error">❌ <?= safe($error) ?></div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Registration Form -->
            <form method="POST" action="register.php">

                <!-- Full Name -->
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="e.g. Jane Doe"
                        value="<?= safe($_POST['name'] ?? '') ?>"
                        required
                        autocomplete="name"
                    >
                </div>

                <!-- Username -->
                <div class="form-group">
                    <label for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="e.g. jane_doe"
                        value="<?= safe($_POST['username'] ?? '') ?>"
                        required
                        autocomplete="username"
                    >
                    <p class="hint">Letters, numbers and underscores only. Min 3 characters.</p>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="jane@example.com"
                        value="<?= safe($_POST['email'] ?? '') ?>"
                        required
                        autocomplete="email"
                    >
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="At least 6 characters"
                        required
                        autocomplete="new-password"
                    >
                    <!-- Password strength bar -->
                    <!-- <div style="margin-top:6px;height:4px;background:var(--border);border-radius:4px;overflow:hidden;">
                        <div id="password-strength" style="height:100%;width:0%;border-radius:4px;transition:all 0.3s ease;"></div>
                    </div> -->
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="confirm">Confirm Password</label>
                    <input
                        type="password"
                        id="confirm"
                        name="confirm"
                        placeholder="Repeat your password"
                        required
                        autocomplete="new-password"
                    >
                </div>

                <!-- Terms -->
                <p style="font-size:0.8rem;color:var(--muted);margin-bottom:16px;">
                    By signing up you agree to our made-up Terms of Service. 😄
                </p>

                <!-- Submit -->
                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    🚀 Create Account
                </button>

            </form>

        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="js/main.js"></script>
<script src="js/jquery-features.js"></script>
</body>
</html>
