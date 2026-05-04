<?php
// ============================================
// index.php — Landing Page (Home)
// ============================================
require_once 'includes/auth.php';

// If user is already logged in, go to dashboard
redirectIfLoggedIn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ConnectU — Share Your World</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="landing-page">

    <!-- ---- TOP NAV ---- -->
    <header class="landing-nav">
        <div class="landing-logo">Connect<span>U</span></div>
        <div class="landing-nav-links">
            <a href="login.php"    class="btn btn-dark">Log In</a>
            <a href="register.php" class="btn btn-primary">Sign Up Free</a>
        </div>
    </header>

    <!-- ---- HERO SECTION ---- -->
    <section class="landing-hero">
        <h1>
            Share <em>moments</em>,<br>
            build <em>connections</em>
        </h1>
        <p>
            ConnectU is a cozy social platform where you can post your thoughts,
            share photos, follow friends, and discover new people.
            No noise — just genuine connections.
        </p>
        <div class="landing-cta">
            <a href="register.php" class="btn btn-primary btn-lg">
                ✨ Create your account
            </a>
            <a href="login.php" class="btn btn-dark btn-lg">
                Already have one? Log in
            </a>
        </div>
    </section>

    <!-- ---- FEATURES GRID ---- -->
    <section class="landing-features">

        <div class="feature-card">
            <span class="feature-icon">📝</span>
            <h3>Share Posts</h3>
            <p>Write thoughts, share photos, and express yourself. Your posts, your voice.</p>
        </div>

        <div class="feature-card">
            <span class="feature-icon">❤️</span>
            <h3>Like & Comment</h3>
            <p>React to posts you love and join conversations with likes and comments.</p>
        </div>

        <div class="feature-card">
            <span class="feature-icon">👥</span>
            <h3>Follow People</h3>
            <p>Follow friends and interesting people. Build your own personalized feed.</p>
        </div>

        <div class="feature-card">
            <span class="feature-icon">🔔</span>
            <h3>Notifications</h3>
            <p>Stay updated when someone follows you, likes, or comments on your posts.</p>
        </div>

        <div class="feature-card">
            <span class="feature-icon">🔍</span>
            <h3>Discover Users</h3>
            <p>Search by username to find and connect with new interesting people.</p>
        </div>

        <div class="feature-card">
            <span class="feature-icon">🎨</span>
            <h3>Your Profile</h3>
            <p>Customize your bio, upload a profile picture, and make it truly yours.</p>
        </div>

    </section>

    <!-- ---- HOW IT WORKS ---- -->
    <section style="padding: 20px 60px 80px; text-align:center;">
        <h2 style="font-family:var(--font-display);color:#fff;font-size:2rem;margin-bottom:12px;">
            How it works
        </h2>
        <p style="color:rgba(255,255,255,0.45);margin-bottom:48px;">Three simple steps to get started</p>

        <div style="display:flex;justify-content:center;gap:40px;flex-wrap:wrap;max-width:800px;margin:0 auto;">
            <div style="text-align:center;flex:1;min-width:180px;">
                <div style="width:56px;height:56px;border-radius:50%;background:var(--accent);color:#fff;font-weight:900;font-size:1.3rem;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">1</div>
                <h4 style="color:#fff;margin-bottom:6px;">Create Account</h4>
                <p style="color:rgba(255,255,255,0.45);font-size:0.88rem;">Sign up with your name, username, and email. Takes under a minute.</p>
            </div>
            <div style="width:1px;background:rgba(255,255,255,0.1);align-self:center;height:60px;"></div>
            <div style="text-align:center;flex:1;min-width:180px;">
                <div style="width:56px;height:56px;border-radius:50%;background:var(--accent);color:#fff;font-weight:900;font-size:1.3rem;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">2</div>
                <h4 style="color:#fff;margin-bottom:6px;">Find & Follow</h4>
                <p style="color:rgba(255,255,255,0.45);font-size:0.88rem;">Search for people you know or discover new ones. Follow to build your feed.</p>
            </div>
            <div style="width:1px;background:rgba(255,255,255,0.1);align-self:center;height:60px;"></div>
            <div style="text-align:center;flex:1;min-width:180px;">
                <div style="width:56px;height:56px;border-radius:50%;background:var(--accent);color:#fff;font-weight:900;font-size:1.3rem;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">3</div>
                <h4 style="color:#fff;margin-bottom:6px;">Share & Connect</h4>
                <p style="color:rgba(255,255,255,0.45);font-size:0.88rem;">Post content, like and comment, and enjoy real conversations.</p>
            </div>
        </div>

        <div style="margin-top:56px;">
            <a href="register.php" class="btn btn-primary btn-lg">
                Get Started — It's Free
            </a>
        </div>
    </section>

    <!-- ---- FOOTER ---- -->
    <footer class="landing-footer">
        <p>© <?= date('Y') ?> ConnectU &nbsp;·&nbsp; Built with PHP &amp; MySQL &nbsp;·&nbsp; Made with ❤️</p>
    </footer>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="js/main.js"></script>
<script src="js/jquery-features.js"></script>
</body>
</html>
