<?php
// ============================================
// login.php — User Login
// ============================================
require_once 'includes/auth.php';
require_once 'includes/db.php';

redirectIfLoggedIn(); // Already logged in? Go to dashboard

$error = '';

// ---- Handle login form ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $login    = trim($_POST['login']    ?? ''); // Can be email or username
    $password =       $_POST['password'] ?? '';

    if (empty($login) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        // Find user by email OR username
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1");
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Password correct — start session
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];

            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            $error = 'Incorrect email/username or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In — ConnectU</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="new_logo.png">
</head>
<body>

<div class="auth-page">

    <!-- ---- LEFT PANEL ---- -->
    <div class="auth-left">
        <div class="auth-left-content">
            <h2>Welcome<br>back to <span>ConnectU</span></h2>
            <p>Your friends are waiting for you. Log in to see what they've been sharing.</p>

            <!-- <div class="auth-testimonial">
                <blockquote>"I check ConnectU every morning with my coffee. It's become a nice little ritual."</blockquote>
                <div class="author">
                    <img src="new_logo1.png" alt="logo" width="140" height="120">
                    <div class="author-avatar">C</div>
                    <div class="author-info">
                        <div class="name">Carol White</div>
                        <div class="handle">@carol</div>
                    </div>
                </div>
            </div> -->

            <div style="margin-top:32px;">
                <p style="color:rgba(255,255,255,0.4);font-size:0.82rem;margin-bottom:12px;">New here?</p>
                <a href="register.php" class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,0.3);">
                    Create a free account →
                </a>
            </div>
        </div>
    </div>

    <!-- ---- RIGHT PANEL (form) ---- -->
    <div class="auth-right">
        <div class="auth-form-container">
            <a href="index.php" class="auth-logo">Connect<span>U</span></a>
            <h3>Log in to your account</h3>
            <p class="subtitle">Don't have one? <a href="register.php">Sign up free</a></p>

            <!-- Error message -->
            <?php if ($error): ?>
                <div class="alert alert-error">❌ <?= safe($error) ?></div>
            <?php endif; ?>

            <!-- Success message if redirected from password change -->
            <?php if (isset($_GET['updated'])): ?>
                <div class="alert alert-success">✅ Profile updated! Please log in again.</div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="login.php">

                <!-- Email or Username -->
                <div class="form-group">
                    <label for="login">Email or Username</label>
                    <input
                        type="text"
                        id="login"
                        name="login"
                        placeholder="your@email.com or @username"
                        value="<?= safe($_POST['login'] ?? '') ?>"
                        required
                        autocomplete="username"
                        autofocus
                    >
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Your password"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <!-- Hint for demo -->
                <div class="alert alert-info" style="font-size:0.82rem;">
                    💡 <strong>Demo tip:</strong> Use <code>alice</code> / <code>password123</code> to try the demo account.
                </div>

                <!-- Submit -->
                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    Log In →
                </button>

            </form>

            <div class="divider"></div>

            <p style="text-align:center;color:var(--muted);font-size:0.85rem;">
                <a href="index.php" style="color:var(--muted);">← Back to home</a>
            </p>

        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="js/main.js"></script>
<script src="js/jquery-features.js"></script>
</body>
</html>
