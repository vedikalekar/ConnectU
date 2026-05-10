<?php
// ============================================
// create_post.php — Create New Post
// ============================================
require_once 'includes/auth.php';
require_once 'includes/db.php';

requireLogin();

$userId  = getCurrentUserId();
$error   = '';
$success = '';

// Fetch draft
$draftStmt = $pdo->prepare("SELECT content FROM post_drafts WHERE user_id = ?");
$draftStmt->execute([$userId]);
$draftRow = $draftStmt->fetch();
$draftContent = $draftRow ? $draftRow['content'] : '';

// ---- Handle form submission ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $content = trim($_POST['content'] ?? '');

    if (empty($content)) {
        $error = 'Please write something before posting.';
    } elseif (strlen($content) > 2000) {
        $error = 'Post is too long. Maximum 2000 characters.';
    } else {

        $imageName = null;

        // ---- Handle image upload ----
        if (!empty($_FILES['image']['name'])) {
            $file     = $_FILES['image'];
            $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $maxSize  = 5 * 1024 * 1024; // 5MB

            if (!in_array($ext, $allowed)) {
                $error = 'Only JPG, PNG, GIF, and WEBP images are allowed.';
            } elseif ($file['size'] > $maxSize) {
                $error = 'Image too large. Maximum size is 5MB.';
            } elseif ($file['error'] !== UPLOAD_ERR_OK) {
                $error = 'Upload failed. Please try again.';
            } else {
                // Save image with unique name
                $imageName = uniqid('post_', true) . '.' . $ext;
                $uploadDir = 'uploads/posts/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                if (!move_uploaded_file($file['tmp_name'], $uploadDir . $imageName)) {
                    $error     = 'Could not save image. Check folder permissions.';
                    $imageName = null;
                }
            }
        }

        // ---- Insert post if no errors ----
        if (empty($error)) {
            $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $content, $imageName]);

            // Clear draft
            $delDraft = $pdo->prepare("DELETE FROM post_drafts WHERE user_id = ?");
            $delDraft->execute([$userId]);

            header("Location: dashboard.php");
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
    <title>Create Post — ConnectU</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="app-shell">
    <?php include 'includes/nav.php'; ?>

    <div class="main-content">

        <header class="top-header">
            <h2>✍️ Create Post</h2>
            <a href="dashboard.php" class="btn btn-outline btn-sm">← Back to Feed</a>
        </header>

        <div class="page-body">

            <?php if ($error): ?>
                <div class="alert alert-error">❌ <?= safe($error) ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 style="font-size:1rem;color:var(--charcoal);">What's on your mind?</h3>
                </div>
                <div class="card-body">

                    <form method="POST" action="create_post.php" enctype="multipart/form-data">

                        <!-- Content textarea -->
                        <div class="form-group">
                            <label for="content">Your Post</label>
                            <textarea
                                id="content"
                                name="content"
                                placeholder="Share something with your followers..."
                                rows="6"
                                data-maxlength="2000"
                                required
                            ><?= safe($_POST['content'] ?? $draftContent) ?></textarea>
                        </div>

                        <!-- Image upload -->
                        <div class="form-group">
                            <label>Add a Photo (Optional)</label>

                            <!-- Hidden file input -->
                            <input type="file"
                                   id="post-image-input"
                                   name="image"
                                   accept="image/*"
                                   style="display:none;">

                            <!-- Clickable upload area -->
                            <div class="upload-area" id="upload-area">
                                <div class="upload-icon">🖼️</div>
                                <p><strong>Click to upload</strong></p>
                                <p style="margin-top:6px;font-size:0.78rem;">JPG, PNG, GIF, WEBP · Max 5MB</p>
                            </div>

                            <!-- Preview box -->
                            <div id="image-preview">
                                <img src="" alt="Preview">
                            </div>
                        </div>

                        <!-- Tips -->
                        <div style="background:var(--cream);border-radius:var(--radius-sm);padding:14px 16px;margin-bottom:20px;">
                            <p style="font-size:0.82rem;color:var(--muted);">
                                💡 <strong>Tips:</strong> Be authentic, be kind, and share things you love.
                                Posts support up to 2,000 characters.
                            </p>
                        </div>

                        <!-- Buttons -->
                        <div style="display:flex;gap:12px;align-items:center;">
                            <button type="submit" class="btn btn-primary btn-lg">
                                🚀 Publish Post
                            </button>
                            <a href="dashboard.php" class="btn btn-outline btn-lg">
                                Cancel
                            </a>
                            <span id="draft-status" style="margin-left:auto;color:var(--muted);font-size:0.85rem;">
                                <?= $draftContent ? 'Draft loaded' : '' ?>
                            </span>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    const contentArea = document.getElementById('content');
    const statusText = document.getElementById('draft-status');
    let timeoutId;

    if (contentArea) {
        contentArea.addEventListener('input', function() {
            statusText.textContent = 'Saving...';
            clearTimeout(timeoutId);
            timeoutId = setTimeout(function() {
                const formData = new URLSearchParams();
                formData.append('content', contentArea.value);

                fetch('save_draft_ajax.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: formData.toString()
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (data.action === 'deleted') {
                            statusText.textContent = '';
                        } else {
                            statusText.textContent = 'Draft saved at ' + data.time;
                        }
                    } else {
                        statusText.textContent = 'Failed to save draft';
                    }
                }).catch(() => {
                    statusText.textContent = 'Error saving draft';
                });
            }, 1500);
        });
    }
});
</script>
</body>
</html>
