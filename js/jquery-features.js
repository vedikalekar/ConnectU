// ============================================
// SocialMini — jQuery Features
// Dark Mode | Share Button | Mention Autocomplete
// ============================================

$(document).ready(function () {
    initDarkMode();
    initShareButtons();
    initMentionSystem();
});

// ============================================================
// FEATURE A — DARK MODE TOGGLE
// ============================================================
function initDarkMode() {
    var saved = localStorage.getItem('sm_theme') || 'light';
    if (saved === 'dark') $('html').addClass('dark-mode');

    var $toggle = $(
        '<button id="dark-mode-toggle" title="Toggle dark mode">' +
            '<span class="dm-icon">' + (saved === 'dark' ? '☀️' : '🌙') + '</span>' +
            '<span class="dm-label">' + (saved === 'dark' ? 'Light Mode' : 'Dark Mode') + '</span>' +
        '</button>'
    );

    $('.sidebar-footer').prepend($toggle);

    $('#dark-mode-toggle').on('click', function () {
        var isDark = $('html').hasClass('dark-mode');
        $('body').animate({ opacity: 0.85 }, 120, function () {
            $('html').toggleClass('dark-mode');
            $('body').animate({ opacity: 1 }, 120);
        });
        var nowDark = !isDark;
        localStorage.setItem('sm_theme', nowDark ? 'dark' : 'light');
        $(this).find('.dm-icon').text(nowDark ? '☀️' : '🌙');
        $(this).find('.dm-label').text(nowDark ? 'Light Mode' : 'Dark Mode');
    });
}

// ============================================================
// FEATURE B — POST SHARE / COPY LINK
// FIX: Don't inject buttons via JS — they are already in HTML
//      for post_detail.php. For dashboard, inject cleanly once.
//      Fix URL building to work on Windows Apache paths.
// ============================================================
function initShareButtons() {

    // Build the base URL reliably (works on Windows Apache too)
    function getPostUrl(postId) {
        var origin   = window.location.origin;                        // http://localhost
        var path     = window.location.pathname;                      // /socialmini_enhanced/dashboard.php
        var folder   = path.substring(0, path.lastIndexOf('/') + 1); // /socialmini_enhanced/
        return origin + folder + 'post_detail.php?id=' + postId;
    }

    // --- dashboard.php: inject share button into each post-footer ---
    // Only run on dashboard (post-footer elements have no .share-btn yet)
    $('.post-footer').each(function () {
        var $footer = $(this);

        // Skip if share button already exists (post_detail has it in HTML)
        if ($footer.find('.share-btn').length) return;

        var postId = $footer.find('.like-btn').data('post-id');
        if (!postId) return;

        var $shareBtn = $(
            '<button class="share-btn" data-post-id="' + postId + '" title="Share post">' +
                '<svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">' +
                    '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>' +
                    '<line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>' +
                    '<line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>' +
                '</svg>' +
                '<span>Share</span>' +
            '</button>'
        );
        $footer.find('.post-actions-right').before($shareBtn);
    });

    // --- Delegated click handler for ALL share buttons on the page ---
    $(document).on('click', '.share-btn', function (e) {
        e.stopPropagation();
        var $btn   = $(this);
        var postId = $btn.data('post-id');
        var url    = getPostUrl(postId);
        showSharePanel($btn, url);
    });

    // Close panel when clicking outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.sm-share-panel, .share-btn').length) {
            $('.sm-share-panel').slideUp(150, function () { $(this).remove(); });
        }
    });
}

function showSharePanel($btn, url) {
    // Remove any existing panel first
    $('.sm-share-panel').remove();

    var encodedUrl  = encodeURIComponent(url);
    var encodedText = encodeURIComponent('Check this out on SocialMini!');

    var $panel = $(
        '<div class="sm-share-panel">' +
            '<div class="sm-share-title">Share this post</div>' +
            '<div class="sm-share-copy-row">' +
                '<input class="sm-share-url-input" type="text" value="' + url + '" readonly>' +
                '<button class="sm-share-copy-btn btn btn-primary btn-sm">Copy</button>' +
            '</div>' +
            '<div class="sm-share-options">' +
                '<a class="sm-share-opt" href="https://wa.me/?text=' + encodedText + '%20' + encodedUrl + '" target="_blank">' +
                    '<span class="sm-share-opt-icon">💬</span><span>WhatsApp</span>' +
                '</a>' +
                '<a class="sm-share-opt" href="https://twitter.com/intent/tweet?url=' + encodedUrl + '&text=' + encodedText + '" target="_blank">' +
                    '<span class="sm-share-opt-icon">𝕏</span><span>Twitter/X</span>' +
                '</a>' +
                '<a class="sm-share-opt" href="mailto:?subject=Check%20this%20post&body=' + encodedText + '%20' + encodedUrl + '">' +
                    '<span class="sm-share-opt-icon">📧</span><span>Email</span>' +
                '</a>' +
                '<button class="sm-share-opt sm-native-share" data-url="' + url + '">' +
                    '<span class="sm-share-opt-icon">⬆️</span><span>More</span>' +
                '</button>' +
            '</div>' +
        '</div>'
    );

    $('body').append($panel);

    // Position below the button, stay within viewport
    var btnOffset = $btn.offset();
    var panelW    = 300;
    var left      = btnOffset.left;
    var maxLeft   = $(window).width() - panelW - 12;
    if (left > maxLeft) left = maxLeft;

    $panel.css({
        position : 'fixed',
        top      : (btnOffset.top - $(window).scrollTop()) + $btn.outerHeight() + 6,
        left     : left,
        zIndex   : 99999
    }).hide().slideDown(180);

    // Copy button
    $panel.find('.sm-share-copy-btn').on('click', function () {
        var $this = $(this);
        var $input = $panel.find('.sm-share-url-input');
        $input[0].select();

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(function () {
                flashCopied($this);
            }).catch(function () {
                document.execCommand('copy');
                flashCopied($this);
            });
        } else {
            document.execCommand('copy');
            flashCopied($this);
        }
    });

    // Auto-select URL on click
    $panel.find('.sm-share-url-input').on('click', function () { this.select(); });

    // Native share (mobile)
    $panel.find('.sm-native-share').on('click', function () {
        if (navigator.share) {
            navigator.share({ title: 'SocialMini Post', url: url });
        } else {
            if (navigator.clipboard) navigator.clipboard.writeText(url);
            flashCopied($(this).find('span:last'));
        }
    });
}

function flashCopied($el) {
    var orig = $el.text();
    $el.text('✅ Copied!');
    setTimeout(function () { $el.text(orig); }, 2000);
    if (typeof showNotification === 'function') {
        showNotification('Link copied to clipboard! 🔗', 'success', 2000);
    }
}

// ============================================================
// FEATURE C — @MENTION AUTOCOMPLETE
// FIX: Attach to ALL text inputs/textareas on page,
//      including comment inputs (name="comment").
//      Use event delegation so dynamically added fields work.
//      Use $(document).on() so emoji-wrapped textareas work too.
// ============================================================
var mentionCache = null;  // Cache users after first fetch

function initMentionSystem() {
    // Attach to all existing fields
    attachMentionToFields();

    // Watch for new fields added dynamically (e.g. after AJAX comments)
    $(document).on('focus', 'textarea, input[type="text"]', function () {
        if (!$(this).data('mention-ready')) {
            $(this).data('mention-ready', true);
            attachMentionToField($(this));
        }
    });
}

function attachMentionToFields() {
    $('textarea, input[type="text"]').each(function () {
        if (!$(this).data('mention-ready')) {
            $(this).data('mention-ready', true);
            attachMentionToField($(this));
        }
    });
}

function attachMentionToField($field) {
    // Create dropdown once per field
    var $dropdown = $(
        '<div class="sm-mention-dropdown">' +
            '<ul class="sm-mention-list"></ul>' +
        '</div>'
    ).hide().appendTo('body');

    var mentionStart = -1;
    var activeIdx    = -1;

    // ---- INPUT: detect @ trigger ----
    $field.on('input', function () {
        var val    = $field.val();
        var cursor = $field[0].selectionStart;
        var before = val.slice(0, cursor);
        var match  = before.match(/@(\w*)$/);   // @ followed by word chars

        if (match) {
            mentionStart = before.lastIndexOf('@');
            var query    = match[1];
            fetchUsers(query, function (users) {
                if (!users.length) { $dropdown.hide(); return; }
                renderDropdown($dropdown, users, $field, mentionStart);
                positionDropdown($dropdown, $field);
                $dropdown.show();
                activeIdx = -1;
            });
        } else {
            $dropdown.hide();
        }
    });

    // ---- KEYDOWN: arrow navigation + enter/tab/escape ----
    $field.on('keydown', function (e) {
        if (!$dropdown.is(':visible')) return;

        var $items = $dropdown.find('.sm-mention-item');

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeIdx = Math.min(activeIdx + 1, $items.length - 1);
            $items.removeClass('sm-mention-active').eq(activeIdx).addClass('sm-mention-active');
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeIdx = Math.max(activeIdx - 1, 0);
            $items.removeClass('sm-mention-active').eq(activeIdx).addClass('sm-mention-active');
        } else if (e.key === 'Enter' || e.key === 'Tab') {
            var $active = $items.filter('.sm-mention-active');
            if ($active.length) {
                e.preventDefault();
                doInsertMention($field, $active.data('username'), mentionStart);
                $dropdown.hide();
            }
        } else if (e.key === 'Escape') {
            $dropdown.hide();
        }
    });

    // ---- BLUR: close dropdown ----
    $field.on('blur', function () {
        // Small delay so mousedown on dropdown item fires first
        setTimeout(function () { $dropdown.hide(); }, 200);
    });
}

function renderDropdown($dropdown, users, $field, mentionStart) {
    var $list = $dropdown.find('.sm-mention-list').empty();

    $.each(users, function (i, u) {
        var initial = (u.name || u.username).charAt(0).toUpperCase();
        var $li = $(
            '<li class="sm-mention-item" data-username="' + $('<div>').text(u.username).html() + '">' +
                '<span class="sm-mention-avatar">' + $('<div>').text(initial).html() + '</span>' +
                '<span class="sm-mention-info">' +
                    '<strong>' + $('<div>').text(u.name).html() + '</strong>' +
                    '<small>@' + $('<div>').text(u.username).html() + '</small>' +
                '</span>' +
            '</li>'
        );

        // mousedown (not click) so it fires before blur
        $li.on('mousedown', function (e) {
            e.preventDefault();
            doInsertMention($field, u.username, mentionStart);
            $dropdown.hide();
        });

        $list.append($li);
    });
}

function positionDropdown($dropdown, $field) {
    var offset  = $field.offset();
    var top     = offset.top + $field.outerHeight() + 2;
    var left    = offset.left;
    var maxW    = Math.min(260, $(window).width() - left - 12);
    $dropdown.css({ position: 'absolute', top: top, left: left, width: maxW, zIndex: 99998 });
}

function doInsertMention($field, username, atIndex) {
    var val    = $field.val();
    var cursor = $field[0].selectionStart;
    var before = val.slice(0, atIndex);
    var after  = val.slice(cursor);
    var insert = '@' + username + ' ';
    $field.val(before + insert + after);
    var newPos = before.length + insert.length;
    $field[0].setSelectionRange(newPos, newPos);
    $field.trigger('input');   // update char counter if present
    $field.focus();
}

function fetchUsers(query, callback) {
    if (mentionCache) {
        // Filter locally from cache
        var filtered = $.grep(mentionCache, function (u) {
            return u.username.toLowerCase().indexOf(query.toLowerCase()) === 0 ||
                   u.name.toLowerCase().indexOf(query.toLowerCase()) === 0;
        });
        callback(filtered.slice(0, 6));
        return;
    }

    $.ajax({
        url      : 'mention_users.php',
        method   : 'GET',
        dataType : 'json',
        success  : function (data) {
            mentionCache = Array.isArray(data) ? data : [];
            var filtered = $.grep(mentionCache, function (u) {
                return u.username.toLowerCase().indexOf(query.toLowerCase()) === 0 ||
                       u.name.toLowerCase().indexOf(query.toLowerCase()) === 0;
            });
            callback(filtered.slice(0, 6));
        },
        error    : function (xhr, status, err) {
            console.warn('mention_users.php error:', status, err);
            callback([]);
        }
    });
}
