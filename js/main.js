// ============================================
// SocialMini - Main JavaScript (Enhanced)
// jQuery + Vanilla JS — All 5 new features
// ============================================

document.addEventListener('DOMContentLoaded', function () {
    initDropdowns();
    initLikeButtons();
    initCommentForms();
    initImageUpload();
    initFollowButtons();
    initSearch();
    initDeleteConfirm();
    initCharCounter();        // Feature 4: Live character count
    highlightActiveNav();
    initProfilePicPreview();
    initDragDrop();
    initTimestamps();         // Feature 2: Relative timestamps
    initEmojiPicker();        // Feature 5: Emoji picker
    initPopupNotifications(); // Feature 3: Popup notifications
    initPasswordStrength();
    initPostActions();        // Feature: Undo delete/hide
});

// ============================================
// FEATURE 2 — RELATIVE TIMESTAMPS ("just now")
// ============================================
function timeAgoJS(dateString) {
    var date = new Date(dateString);
    var now  = new Date();
    var diff = Math.floor((now - date) / 1000);

    if (diff < 5)    return 'just now';
    if (diff < 60)   return diff + 's ago';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function initTimestamps() {
    function updateAll() {
        document.querySelectorAll('[data-timestamp]').forEach(function(el) {
            el.textContent = timeAgoJS(el.getAttribute('data-timestamp'));
        });
    }
    updateAll();
    setInterval(updateAll, 30000);
}

// ============================================
// FEATURE 3 — POPUP NOTIFICATIONS (toast)
// ============================================
var notifQueue = [];
var notifShowing = false;

function showNotification(message, type, duration, actionHtml) {
    type     = type     || 'success';
    duration = duration || 3500;
    notifQueue.push({ message: message, type: type, duration: duration, actionHtml: actionHtml });
    if (!notifShowing) processNotifQueue();
}

function processNotifQueue() {
    if (notifQueue.length === 0) { notifShowing = false; return; }
    notifShowing = true;
    var notif = notifQueue.shift();
    var icons  = { success: '✅', error: '❌', info: 'ℹ️', warning: '⚠️' };

    var toast = document.createElement('div');
    toast.className = 'sm-toast sm-toast-' + notif.type;
    var msgHtml = escapeHtml(notif.message);
    if (notif.actionHtml) msgHtml += ' ' + notif.actionHtml;
    
    toast.innerHTML =
        '<span class="sm-toast-icon">' + (icons[notif.type] || '✅') + '</span>' +
        '<span class="sm-toast-msg">' + msgHtml + '</span>' +
        '<button class="sm-toast-close" onclick="this.parentElement.classList.remove(\'sm-toast-show\');this.parentElement.classList.add(\'sm-toast-hide\');var el=this.parentElement;setTimeout(function(){el.remove();processNotifQueue();},350);event.stopPropagation();">×</button>';

    var existing = document.querySelectorAll('.sm-toast');
    toast.style.top = (20 + existing.length * 72) + 'px';
    document.body.appendChild(toast);

    requestAnimationFrame(function() { toast.classList.add('sm-toast-show'); });

    setTimeout(function() {
        if (toast.parentNode) {
            toast.classList.remove('sm-toast-show');
            toast.classList.add('sm-toast-hide');
            setTimeout(function() { if (toast.parentNode) toast.remove(); processNotifQueue(); }, 350);
        }
    }, notif.duration);
}

// ============================================
// FEATURE 4 — LIVE CHARACTER COUNT (enhanced)
// ============================================
function initCharCounter() {
    document.querySelectorAll('[data-maxlength]').forEach(function(textarea) {
        var max = parseInt(textarea.getAttribute('data-maxlength'));

        var wrapper = document.createElement('div');
        wrapper.className = 'sm-char-wrapper';

        var bar = document.createElement('div');
        bar.className = 'sm-char-bar';
        var fill = document.createElement('div');
        fill.className = 'sm-char-fill sm-char-ok';
        bar.appendChild(fill);

        var label = document.createElement('span');
        label.className = 'sm-char-label';
        label.textContent = max + ' remaining';

        wrapper.appendChild(bar);
        wrapper.appendChild(label);
        textarea.parentNode.appendChild(wrapper);

        function update() {
            var len = textarea.value.length;
            var rem = max - len;
            var pct = Math.min((len / max) * 100, 100);

            fill.style.width = pct + '%';
            fill.className   = 'sm-char-fill';
            label.className  = 'sm-char-label';

            if (pct >= 100) {
                fill.classList.add('sm-char-over');
                label.classList.add('sm-char-label-danger');
                label.textContent = Math.abs(rem) + ' over limit!';
            } else if (pct >= 90) {
                fill.classList.add('sm-char-warn');
                label.classList.add('sm-char-label-warn');
                label.textContent = rem + ' remaining';
            } else if (pct >= 70) {
                fill.classList.add('sm-char-caution');
                label.textContent = rem + ' remaining';
            } else {
                fill.classList.add('sm-char-ok');
                label.textContent = rem + ' remaining';
            }
        }

        textarea.addEventListener('input', update);
        update();
    });
}

// ============================================
// FEATURE 5 — EMOJI PICKER
// ============================================
var EMOJIS = [
    '😀','😂','😍','🥰','😎','🤔','😅','😭','😤','🥳',
    '😇','🤩','😏','😬','🫡','😴','🤗','🥺','😱','😋',
    '❤️','🧡','💛','💚','💙','💜','🖤','🤍','💔','💯',
    '🔥','✨','⭐','🎉','🎊','🙌','👏','💪','🫶','👍',
    '🌸','🌺','🌻','🌈','☀️','🌙','⭐','🌊','🍀','🦋',
    '🍕','🍔','🍣','🍦','🎂','☕','🍺','🍷','🥂','🍎',
    '⚽','🏀','🎮','🎵','🎸','📸','✈️','🚀','💻','📱',
    '🐶','🐱','🐻','🦊','🐼','🦁','🐨','🐸','🦄','🐙'
];

function initEmojiPicker() {
    document.querySelectorAll('[data-maxlength]').forEach(function(textarea) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'sm-emoji-btn';
        btn.textContent = '😊';
        btn.title = 'Add emoji';

        var picker = document.createElement('div');
        picker.className = 'sm-emoji-picker';
        picker.innerHTML =
            '<div class="sm-emoji-search-wrap"><input type="text" class="sm-emoji-search" placeholder="Search emoji..."></div>' +
            '<div class="sm-emoji-grid"></div>';

        var grid   = picker.querySelector('.sm-emoji-grid');
        var search = picker.querySelector('.sm-emoji-search');

        function renderEmojis(filter) {
            grid.innerHTML = '';
            EMOJIS.filter(function(e) { return !filter || e.includes(filter); })
                  .forEach(function(emoji) {
                      var span = document.createElement('button');
                      span.type = 'button';
                      span.className = 'sm-emoji-item';
                      span.textContent = emoji;
                      span.addEventListener('click', function(e) {
                          e.stopPropagation();
                          insertAtCursor(textarea, emoji);
                          textarea.dispatchEvent(new Event('input'));
                          picker.classList.remove('sm-emoji-open');
                      });
                      grid.appendChild(span);
                  });
        }
        renderEmojis('');

        search.addEventListener('input', function() { renderEmojis(search.value.trim()); });

        // Wrap in container
        var container = document.createElement('div');
        container.className = 'sm-emoji-container';
        textarea.parentNode.insertBefore(container, textarea);
        container.appendChild(textarea);
        container.appendChild(btn);
        container.appendChild(picker);

        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            picker.classList.toggle('sm-emoji-open');
        });
        document.addEventListener('click', function() { picker.classList.remove('sm-emoji-open'); });
        picker.addEventListener('click', function(e) { e.stopPropagation(); });
    });
}

function insertAtCursor(el, text) {
    var start = el.selectionStart;
    var end   = el.selectionEnd;
    el.value  = el.value.slice(0, start) + text + el.value.slice(end);
    el.selectionStart = el.selectionEnd = start + text.length;
    el.focus();
}

// ============================================
// FEATURE 1 — IMAGE PREVIEW (enhanced)
// ============================================
function initImageUpload() {
    var fileInput  = document.getElementById('post-image-input');
    var previewBox = document.getElementById('image-preview');
    var uploadArea = document.getElementById('upload-area');
    if (!fileInput) return;

    if (uploadArea) {
        uploadArea.addEventListener('click', function() { fileInput.click(); });
    }

    fileInput.addEventListener('change', function() {
        var file = fileInput.files[0];
        if (!file) return;

        if (!file.type.startsWith('image/')) {
            showNotification('Please select an image file (JPG, PNG, GIF, WEBP).', 'error');
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            showNotification('Image too large! Maximum size is 5MB.', 'error');
            return;
        }

        var reader = new FileReader();
        reader.onload = function(e) {
            if (!previewBox) return;
            previewBox.style.display = 'block';
            previewBox.innerHTML = '';

            var img = document.createElement('img');
            img.src = e.target.result;
            img.alt = 'Preview';

            var info = document.createElement('div');
            info.className = 'sm-preview-info';
            var sizeMB = (file.size / (1024 * 1024)).toFixed(2);
            info.innerHTML =
                '<span>📎 ' + escapeHtml(file.name) + '</span>' +
                '<span>' + sizeMB + ' MB</span>' +
                '<button type="button" class="sm-preview-remove">✕ Remove</button>';

            info.querySelector('.sm-preview-remove').addEventListener('click', function() {
                fileInput.value = '';
                previewBox.style.display = 'none';
                previewBox.innerHTML = '';
                if (uploadArea) uploadArea.querySelector('p').textContent = 'Click to upload';
                showNotification('Image removed.', 'info');
            });

            previewBox.appendChild(img);
            previewBox.appendChild(info);

            if (uploadArea) uploadArea.querySelector('p').textContent = 'Image ready — click to change.';
            showNotification('Image loaded!', 'success', 2000);
        };
        reader.readAsDataURL(file);
    });
}

// ============================================
// DRAG & DROP
// ============================================
function initDragDrop() {
    var uploadArea = document.getElementById('upload-area');
    var fileInput  = document.getElementById('post-image-input');
    if (!uploadArea || !fileInput) return;

    ['dragenter','dragover'].forEach(function(evt) {
        uploadArea.addEventListener(evt, function(e) {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
    });
    uploadArea.addEventListener('dragleave', function() { uploadArea.classList.remove('dragover'); });
    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            fileInput.dispatchEvent(new Event('change'));
        }
    });
}

// ============================================
// POPUP NOTIFICATIONS — on page load (flash)
// ============================================
function initPopupNotifications() {
    // data-flash attributes from PHP
    document.querySelectorAll('[data-flash]').forEach(function(el) {
        var msg  = el.getAttribute('data-flash');
        var type = el.getAttribute('data-flash-type') || 'info';
        if (msg) setTimeout(function() { showNotification(msg, type); }, 300);
        el.remove();
    });

    // Convert static .alert elements to toasts
    document.querySelectorAll('.alert').forEach(function(alertEl) {
        var text = alertEl.textContent.trim().replace(/^[❌✅ℹ️⚠️]\s*/, '');
        var type = alertEl.classList.contains('alert-error')   ? 'error'   :
                   alertEl.classList.contains('alert-success') ? 'success' : 'info';
        if (text) setTimeout(function() { showNotification(text, type); }, 400);
    });
}

// ============================================
// DROPDOWNS
// ============================================
function initDropdowns() {
    document.querySelectorAll('[data-dropdown-toggle]').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var menu = document.getElementById(btn.getAttribute('data-dropdown-toggle'));
            if (menu) menu.classList.toggle('show');
        });
    });
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu.show').forEach(function(m) { m.classList.remove('show'); });
    });
}

// ============================================
// LIKE BUTTONS (AJAX)
// ============================================
function initLikeButtons() {
    document.querySelectorAll('.like-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var postId    = btn.getAttribute('data-post-id');
            var countSpan = btn.querySelector('.like-count');

            fetch('like.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'post_id=' + postId
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    btn.classList.toggle('liked', data.liked);
                    if (countSpan) countSpan.textContent = data.count;
                    btn.style.transform = 'scale(1.3)';
                    setTimeout(function() { btn.style.transform = ''; }, 200);
                    if (data.liked) {
                        var actionHtml = '<span class="undo-like-action" data-post-id="' + postId + '" style="text-decoration:underline;cursor:pointer;margin-left:10px;font-weight:bold;">[Undo]</span>';
                        showNotification('Liked! ❤️', 'success', 3000, actionHtml);
                    }
                } else if (data.redirect) {
                    window.location.href = 'login.php';
                }
            })
            .catch(function(err) { console.log('Like error:', err); });
        });
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('undo-like-action')) {
            var postId = e.target.getAttribute('data-post-id');
            var btn = document.querySelector('.like-btn[data-post-id="' + postId + '"]');
            if (btn) btn.click();
            var closeBtn = e.target.closest('.sm-toast').querySelector('.sm-toast-close');
            if (closeBtn) closeBtn.click();
        }
    });
}

// ============================================
// COMMENT FORMS (AJAX)
// ============================================
function initCommentForms() {
    document.querySelectorAll('.comment-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var postId  = form.getAttribute('data-post-id');
            var input   = form.querySelector('input[name="comment"]');
            var content = input.value.trim();
            if (!content) return;

            fetch('comment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'post_id=' + postId + '&content=' + encodeURIComponent(content)
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    addCommentToPage(data.comment, form);
                    input.value = '';
                    showNotification('Comment posted! 💬', 'success', 2000);
                } else if (data.redirect) {
                    window.location.href = 'login.php';
                } else {
                    showNotification(data.message || 'Could not post comment.', 'error');
                }
            })
            .catch(function(err) { console.log('Comment error:', err); });
        });
    });
}

function addCommentToPage(comment, form) {
    var section = form.closest('.comments-section');
    if (!section) section = form.closest('.post-card') && form.closest('.post-card').querySelector('.comments-section');
    if (!section) return;

    var list = section.querySelector('.comments-list');
    if (!list) {
        list = document.createElement('div');
        list.className = 'comments-list';
        form.parentNode.insertBefore(list, form);
    }

    var initial = (comment.username || 'U').charAt(0).toUpperCase();
    var now = new Date().toISOString();
    list.insertAdjacentHTML('beforeend',
        '<div class="comment-item">' +
            '<div class="comment-avatar">' + initial + '</div>' +
            '<div class="comment-bubble">' +
                '<span class="comment-author">@' + escapeHtml(comment.username) + '</span>' +
                '<p class="comment-text">' + escapeHtml(comment.content) + '</p>' +
                '<span class="comment-time" data-timestamp="' + now + '">just now</span>' +
            '</div>' +
        '</div>');
}

// ============================================
// FOLLOW / UNFOLLOW (AJAX)
// ============================================
function initFollowButtons() {
    document.querySelectorAll('.follow-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            fetch('follow.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'user_id=' + btn.getAttribute('data-user-id')
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    if (data.following) {
                        btn.textContent = 'Unfollow';
                        btn.classList.remove('btn-primary'); btn.classList.add('btn-outline');
                        showNotification('Now following! 👥', 'success');
                    } else {
                        btn.textContent = 'Follow';
                        btn.classList.remove('btn-outline'); btn.classList.add('btn-primary');
                        showNotification('Unfollowed.', 'info');
                    }
                    var countEl = document.getElementById('followers-count');
                    if (countEl && data.follower_count !== undefined) countEl.textContent = data.follower_count;
                } else if (data.redirect) { window.location.href = 'login.php'; }
            })
            .catch(function(err) { console.log('Follow error:', err); });
        });
    });
}

// ============================================
// SEARCH (live filter)
// ============================================
function initSearch() {
    var searchInput = document.getElementById('search-input');
    var userItems   = document.querySelectorAll('.user-item');
    if (!searchInput || !userItems.length) return;

    searchInput.addEventListener('input', function() {
        var query = searchInput.value.toLowerCase().trim();
        userItems.forEach(function(item) {
            var name     = (item.getAttribute('data-name')     || '').toLowerCase();
            var username = (item.getAttribute('data-username') || '').toLowerCase();
            item.style.display = (!query || name.includes(query) || username.includes(query)) ? '' : 'none';
        });
        var visible = Array.from(userItems).filter(function(i) { return i.style.display !== 'none'; });
        var emptyEl = document.getElementById('no-results');
        if (emptyEl) emptyEl.style.display = visible.length === 0 ? 'block' : 'none';
    });
}

// ============================================
// DELETE CONFIRMATION
// ============================================
function initDeleteConfirm() {
    document.querySelectorAll('[data-confirm]').forEach(function(el) {
        el.addEventListener('click', function(e) {
            if (!confirm(el.getAttribute('data-confirm') || 'Are you sure?')) e.preventDefault();
        });
    });
}

// ============================================
// HIGHLIGHT ACTIVE NAV
// ============================================
function highlightActiveNav() {
    var current = window.location.pathname.split('/').pop();
    document.querySelectorAll('.sidebar-nav a').forEach(function(link) {
        var href = link.getAttribute('href');
        if (href === current || href === './' + current) link.classList.add('active');
    });
}

// ============================================
// PROFILE PICTURE PREVIEW
// ============================================
function initProfilePicPreview() {
    var input   = document.getElementById('profile-pic-input');
    var preview = document.getElementById('profile-pic-preview');
    if (!input || !preview) return;

    input.addEventListener('change', function() {
        var file = input.files[0];
        if (!file || !file.type.startsWith('image/')) {
            showNotification('Please select a valid image.', 'error'); return;
        }
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            showNotification('Profile picture updated!', 'success', 2000);
        };
        reader.readAsDataURL(file);
    });
}

// ============================================
// PASSWORD STRENGTH
// ============================================
function initPasswordStrength() {
    var passwordInput = document.getElementById('password');
    var strengthBar   = document.getElementById('password-strength');
    if (!passwordInput || !strengthBar) return;

    passwordInput.addEventListener('input', function() {
        var p = passwordInput.value, s = 0;
        if (p.length >= 8)          s++;
        if (/[A-Z]/.test(p))        s++;
        if (/[0-9]/.test(p))        s++;
        if (/[^A-Za-z0-9]/.test(p)) s++;
        strengthBar.style.width      = (s * 25) + '%';
        strengthBar.style.background = ['#e74c3c','#e74c3c','#f39c12','#27ae60','#27ae60'][s];
        strengthBar.setAttribute('title', ['Too short','Weak','Fair','Good','Strong'][s]);
    });
}

// ============================================
// TOGGLE COMMENTS
// ============================================
function toggleComments(postId) {
    var section = document.getElementById('comments-' + postId);
    if (!section) return;
    section.style.display = (section.style.display === 'none' || !section.style.display) ? 'block' : 'none';
}

// ============================================
// showAlert (legacy alias)
// ============================================
function showAlert(message, type) { showNotification(message, type || 'info'); }

// ============================================
// UTILITY: Escape HTML
// ============================================
function escapeHtml(str) {
    var d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

// ============================================
// AJAX DELETE & HIDE POSTS WITH UNDO
// ============================================
var pendingActions = {};

function initPostActions() {
    document.addEventListener('click', function(e) {
        var actionHtml, card, postId;

        if (e.target.closest('.sm-delete-post')) {
            e.preventDefault();
            var delBtn = e.target.closest('.sm-delete-post');
            postId = delBtn.getAttribute('data-post-id');
            card = delBtn.closest('.post-card');
            
            card.style.display = 'none';
            actionHtml = '<span class="undo-post-action" data-action="delete" data-post-id="' + postId + '" style="text-decoration:underline;cursor:pointer;margin-left:10px;font-weight:bold;">[Undo]</span>';
            showNotification('Post deleted.', 'info', 5000, actionHtml);
            
            pendingActions['delete_' + postId] = setTimeout(function() {
                fetch('delete_post_ajax.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'post_id=' + postId
                }).then(function(res) { return res.json(); }).then(function(data) {
                    if (data.success) card.remove();
                });
                delete pendingActions['delete_' + postId];
            }, 5000);
            
            document.querySelectorAll('.dropdown-menu.show').forEach(function(m) { m.classList.remove('show'); });
        }
        
        if (e.target.closest('.sm-hide-post')) {
            e.preventDefault();
            var hideBtn = e.target.closest('.sm-hide-post');
            postId = hideBtn.getAttribute('data-post-id');
            card = hideBtn.closest('.post-card');
            
            card.style.display = 'none';
            actionHtml = '<span class="undo-post-action" data-action="hide" data-post-id="' + postId + '" style="text-decoration:underline;cursor:pointer;margin-left:10px;font-weight:bold;">[Undo]</span>';
            showNotification('Post hidden.', 'info', 5000, actionHtml);
            
            pendingActions['hide_' + postId] = setTimeout(function() {
                fetch('hide_post_ajax.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'post_id=' + postId
                }).then(function(res) { return res.json(); }).then(function(data) {
                    if (data.success) card.remove();
                });
                delete pendingActions['hide_' + postId];
            }, 5000);
            
            document.querySelectorAll('.dropdown-menu.show').forEach(function(m) { m.classList.remove('show'); });
        }
        
        // Handle Undo Click
        if (e.target.classList.contains('undo-post-action')) {
            e.preventDefault();
            var action = e.target.getAttribute('data-action');
            postId = e.target.getAttribute('data-post-id');
            
            if (pendingActions[action + '_' + postId]) {
                clearTimeout(pendingActions[action + '_' + postId]);
                delete pendingActions[action + '_' + postId];
            }
            
            var btnClass = action === 'delete' ? '.sm-delete-post' : '.sm-hide-post';
            var btn = document.querySelector(btnClass + '[data-post-id="' + postId + '"]');
            if (btn) {
                card = btn.closest('.post-card');
                if (card) {
                    card.style.display = '';
                    card.style.opacity = '0';
                    setTimeout(function() { card.style.transition = 'opacity 0.3s'; card.style.opacity = '1'; }, 10);
                }
            }
            
            var closeBtn = e.target.closest('.sm-toast').querySelector('.sm-toast-close');
            if (closeBtn) closeBtn.click();
            showNotification('Action undone.', 'success', 2000);
        }
    });
}
