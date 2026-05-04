<?php
// ============================================
// edit_post.php — Edit Existing Post
// ============================================
require_once 'includes/auth.php';
require_once 'includes/db.php';

requireLogin();

$userId = getCurrentUserId();
$postId = intval($_GET['id'] ?? 0);

if (!$postId) {
    header("Location: dashboard.php");
    exit();
}

// ---- Get post (must belong to logged-in user) ----
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->execute([$postId, $userId]);
$post = $stmt->fetch();

if (!$post) {
    // Not found or not owner — redirect
    echo "<p style='padding:40px;font-family:sans-serif;'>Post not found or you don't have permission. <a href='dashboard.php'>Go home</a></p>";
    exit();
}

$error   = '';
$success = '';

// ---- Handle update form ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $content = trim($_POST['content'] ?? '');

    if (empty($content)) {
        $error = 'Content cannot be empty.';
    } elseif (strlen($content) > 2000) {
        $error = 'Post is too long. Maximum 2000 characters.';
    } else {

        $imageName = $post['image']; // Keep old image by default

        // ---- Handle new image upload ----
        if (!empty($_FILES['image']['name'])) {
            $file    = $_FILES['image'];
            $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $maxSize = 5 * 1024 * 1024;

            if (!in_array($ext, $allowed)) {
                $error = 'Only JPG, PNG, GIF, and WEBP are allowed.';
            } elseif ($file['size'] > $maxSize) {
                $error = 'Image too large. Max 5MB.';
            } elseif ($file['error'] !== UPLOAD_ERR_OK) {
                $error = 'Upload failed. Please try again.';
            } else {
                $newImageName = uniqid('post_', true) . '.' . $ext;
                $uploadDir    = 'uploads/posts/';

                if (move_uploaded_file($file['tmp_name'], $uploadDir . $newImageName)) {
                    // Delete old image if exists
                    if ($imageName && file_exists($uploadDir . $imageName)) {
                        unlink($uploadDir . $imageName);
                    }
                    $imageName = $newImageName;
                } else {
                    $error = 'Could not save new image.';
                }
            }
        }

        // ---- Remove image if requested ----
        if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
            if ($imageName && file_exists('uploads/posts/' . $imageName)) {
                unlink('uploads/posts/' . $imageName);
            }
            $imageName = null;
        }

        // ---- Update database ----
        if (empty($error)) {
            $upd = $pdo->prepare("UPDATE posts SET content = ?, image = ? WHERE id = ? AND user_id = ?");
            $upd->execute([$content, $imageName, $postId, $userId]);

            header("Location: post_detail.php?id=$postId");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post — ConnectU</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="app-shell">
    <?php include 'includes/nav.php'; ?>

    <div class="main-content">

        <header class="top-header">
            <h2>✏️ Edit Post</h2>
            <a href="post_detail.php?id=<?= $postId ?>" class="btn btn-outline btn-sm">← Cancel</a>
        </header>

        <div class="page-body">

            <?php if ($error): ?>
                <div class="alert alert-error">❌ <?= safe($error) ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 style="font-size:1rem;color:var(--charcoal);">Update your post</h3>
                    <span style="font-size:0.8rem;color:var(--muted);">
                        Posted <?= timeAgo($post['created_at']) ?>
                    </span>
                </div>
                <div class="card-body">

                    <form method="POST" action="edit_post.php?id=<?= $postId ?>" enctype="multipart/form-data">

                        <!-- Content -->
                        <div class="form-group">
                            <label for="content">Post Content</label>
                            <textarea
                                id="content"
                                name="content"
                                rows="6"
                                data-maxlength="2000"
                                required
                            ><?= safe($post['content']) ?></textarea>
                        </div>

                        <!-- Current image preview -->
                        <?php if ($post['image'] && file_exists('uploads/posts/' . $post['image'])): ?>
                        <div style="margin-bottom:20px;">
                            <label style="font-size:0.82rem;font-weight:600;color:var(--ink);text-transform:uppercase;letter-spacing:0.5px;display:block;margin-bottom:8px;">
                                Current Image
                            </label>
                            <div style="position:relative;display:inline-block;">
                                <img src="uploads/posts/<?= safe($post['image']) ?>"
                                     style="max-width:300px;border-radius:var(--radius-sm);"
                                     alt="Current post image">
                            </div>
                            <div style="margin-top:10px;">
                                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.88rem;color:#dc2626;">
                                    <input type="checkbox" name="remove_image" value="1">
                                    Remove current image
                                </label>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Replace image -->
                        <div class="form-group">
                            <label>Replace Photo (Optional)</label>
                            <input type="file"
                                   id="post-image-input"
                                   name="image"
                                   accept="image/*"
                                   style="display:none;">
                            <div class="upload-area" id="upload-area">
                                <div class="upload-icon">🔄</div>
                                <p><strong>Click to upload a new photo</strong></p>
                                <p style="margin-top:4px;font-size:0.78rem;">JPG, PNG, GIF, WEBP · Max 5MB</p>
                            </div>
                            <div id="image-preview"></div>
                        </div>

                        <!-- Buttons -->
                        <div style="display:flex;gap:12px;">
                            <button type="submit" class="btn btn-primary">
                                💾 Save Changes
                            </button>
                            <a href="post_detail.php?id=<?= $postId ?>" class="btn btn-outline">
                                Cancel
                            </a>
                            <a href="delete_post.php?id=<?= $postId ?>"
                               class="btn btn-danger"
                               data-confirm="Delete this post? This cannot be undone."
                               style="margin-left:auto;">
                               🗑️ Delete Post
                            </a>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="js/main.js"></script>
<script src="js/jquery-features.js"></script>
</body>
</html>
