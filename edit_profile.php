<?php
// ============================================
// edit_profile.php — Edit User Profile
// ============================================
require_once 'includes/auth.php';
require_once 'includes/db.php';

requireLogin();

$userId = getCurrentUserId();

// Get current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$errors  = [];
$success = '';

// ---- Handle form submission ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name  = trim($_POST['name']  ?? '');
    $email = trim($_POST['email'] ?? '');
    $bio   = trim($_POST['bio']   ?? '');

    // Validation
    if (empty($name))  $errors[] = 'Name cannot be empty.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = 'Please enter a valid email.';

    // Check email uniqueness (excluding self)
    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check->execute([$email, $userId]);
        if ($check->fetch()) $errors[] = 'That email is already used by another account.';
    }

    // ---- Handle profile picture ----
    $profilePic = $user['profile_pic'];

    if (!empty($_FILES['profile_pic']['name'])) {
        $file    = $_FILES['profile_pic'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxSize = 3 * 1024 * 1024; // 3MB

        if (!in_array($ext, $allowed)) {
            $errors[] = 'Profile picture must be JPG, PNG, GIF, or WEBP.';
        } elseif ($file['size'] > $maxSize) {
            $errors[] = 'Profile picture too large. Maximum 3MB.';
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Profile picture upload failed.';
        } else {
            $newPic    = 'profile_' . $userId . '_' . time() . '.' . $ext;
            $uploadDir = 'uploads/profiles/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            if (move_uploaded_file($file['tmp_name'], $uploadDir . $newPic)) {
                // Delete old pic
                if ($profilePic && $profilePic !== 'default.png' && file_exists($uploadDir . $profilePic)) {
                    unlink($uploadDir . $profilePic);
                }
                $profilePic = $newPic;
            } else {
                $errors[] = 'Could not save profile picture.';
            }
        }
    }

    // ---- Handle password change (optional) ----
    $newPasswordSql = '';
    $newPasswordVal = null;

    $currentPass = $_POST['current_password'] ?? '';
    $newPass     = $_POST['new_password']     ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    if (!empty($currentPass) || !empty($newPass)) {
        if (!password_verify($currentPass, $user['password'])) {
            $errors[] = 'Current password is incorrect.';
        } elseif (strlen($newPass) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        } elseif ($newPass !== $confirmPass) {
            $errors[] = 'New passwords do not match.';
        } else {
            $newPasswordVal = password_hash($newPass, PASSWORD_DEFAULT);
        }
    }

    // ---- Update database ----
    if (empty($errors)) {
        if ($newPasswordVal) {
            $upd = $pdo->prepare("UPDATE users SET name=?, email=?, bio=?, profile_pic=?, password=? WHERE id=?");
            $upd->execute([$name, $email, $bio, $profilePic, $newPasswordVal, $userId]);
        } else {
            $upd = $pdo->prepare("UPDATE users SET name=?, email=?, bio=?, profile_pic=? WHERE id=?");
            $upd->execute([$name, $email, $bio, $profilePic, $userId]);
        }

        $success = 'Profile updated successfully!';

        // Refresh user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile — ConnectU</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="app-shell">
    <?php include 'includes/nav.php'; ?>

    <div class="main-content">

        <header class="top-header">
            <h2>⚙️ Edit Profile</h2>
            <a href="profile.php?id=<?= $userId ?>" class="btn btn-outline btn-sm">← View Profile</a>
        </header>

        <div class="page-body" style="max-width:680px;padding:28px 32px;">

            <?php foreach ($errors as $err): ?>
                <div class="alert alert-error">❌ <?= safe($err) ?></div>
            <?php endforeach; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">✅ <?= safe($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="edit_profile.php" enctype="multipart/form-data">

                <!-- ---- PROFILE PICTURE ---- -->
                <div class="card" style="margin-bottom:20px;">
                    <div class="card-header"><h3 style="font-size:1rem;">Profile Picture</h3></div>
                    <div class="card-body">
                        <div style="display:flex;align-items:center;gap:24px;flex-wrap:wrap;">

                            <!-- Current pic -->
                            <?php if ($user['profile_pic'] && $user['profile_pic'] !== 'default.png' && file_exists('uploads/profiles/' . $user['profile_pic'])): ?>
                                <img id="profile-pic-preview"
                                     src="uploads/profiles/<?= safe($user['profile_pic']) ?>"
                                     style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid var(--accent);">
                            <?php else: ?>
                                <div id="profile-pic-preview"
                                     style="width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,var(--accent) 0%,#e84393 100%);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:2rem;">
                                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>

                            <div>
                                <input type="file"
                                       id="profile-pic-input"
                                       name="profile_pic"
                                       accept="image/*"
                                       style="display:none;">
                                <button type="button"
                                        class="btn btn-outline"
                                        onclick="document.getElementById('profile-pic-input').click()">
                                    📷 Change Photo
                                </button>
                                <p class="hint" style="margin-top:8px;">JPG, PNG, GIF · Max 3MB</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ---- BASIC INFO ---- -->
                <div class="card" style="margin-bottom:20px;">
                    <div class="card-header"><h3 style="font-size:1rem;">Basic Information</h3></div>
                    <div class="card-body">

                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name"
                                       value="<?= safe($user['name']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" value="@<?= safe($user['username']) ?>" disabled
                                       style="background:var(--cream);cursor:not-allowed;">
                                <p class="hint">Username cannot be changed.</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email"
                                   value="<?= safe($user['email']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="bio">Bio</label>
                            <textarea id="bio" name="bio"
                                      placeholder="Tell people a little about yourself..."
                                      rows="3"
                                      data-maxlength="200"><?= safe($user['bio']) ?></textarea>
                        </div>

                    </div>
                </div>

                <!-- ---- CHANGE PASSWORD ---- -->
                <div class="card" style="margin-bottom:24px;">
                    <div class="card-header">
                        <h3 style="font-size:1rem;">Change Password</h3>
                        <span style="font-size:0.8rem;color:var(--muted);">Leave blank to keep current</span>
                    </div>
                    <div class="card-body">

                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password"
                                   placeholder="Enter your current password">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password"
                                       placeholder="Min 6 characters">
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password"
                                       placeholder="Repeat new password">
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Save / Cancel -->
                <div style="display:flex;gap:12px;">
                    <button type="submit" class="btn btn-primary btn-lg">
                        💾 Save Changes
                    </button>
                    <a href="profile.php?id=<?= $userId ?>" class="btn btn-outline btn-lg">
                        Cancel
                    </a>
                </div>

            </form>

        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="js/main.js"></script>
<script src="js/jquery-features.js"></script>
</body>
</html>
