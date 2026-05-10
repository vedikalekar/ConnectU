// ============================================
// js/stories.js — Story Feature Logic
// ============================================

let currentStoryGroupIndex = 0;
let currentStoryIndex = 0;
let storiesData = [];
let storyTimer = null;
const STORY_DURATION = 5000; // 5 seconds per story

$(document).ready(function() {
    // Only load stories if we are on the dashboard
    if ($('#stories-tray').length) {
        loadStories();
    }

    // Handle form submission for creating a story
    $('#create-story-form').on('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        let btn = $(this).find('button[type="submit"]');
        let originalText = btn.text();
        
        btn.prop('disabled', true).text('Posting...');

        $.ajax({
            url: 'create_story_ajax.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(res) {
                btn.prop('disabled', false).text(originalText);
                if (res.success) {
                    closeModal('create-story-modal');
                    $('#create-story-form')[0].reset();
                    loadStories(); // Reload tray
                    if(typeof showToast === 'function') showToast('Story posted successfully!', 'success');
                } else {
                    if(typeof showToast === 'function') showToast(res.error || 'Failed to post story', 'error');
                }
            },
            error: function() {
                btn.prop('disabled', false).text(originalText);
                if(typeof showToast === 'function') showToast('Network error while posting story', 'error');
            }
        });
    });
});

function loadStories() {
    $.getJSON('get_stories_ajax.php', function(res) {
        if (res.success) {
            storiesData = res.data;
            renderStoriesTray();
        }
    });
}

function renderStoriesTray() {
    let tray = $('#stories-tray');
    tray.empty();

    // Add "Create Story" button
    tray.append(`
        <div class="story-item add-story-btn" onclick="openModal('create-story-modal')">
            <div class="story-ring">
                +
            </div>
            <div class="story-username">Add Story</div>
        </div>
    `);

    // Render active stories
    storiesData.forEach((group, index) => {
        let isUnread = !group.all_viewed;
        let ringClass = isUnread ? 'story-ring unread' : 'story-ring';
        let picHtml = '';
        
        if (group.profile_pic && group.profile_pic !== 'default.png') {
            picHtml = `<img src="uploads/profiles/${group.profile_pic}" alt="${group.name}">`;
        } else {
            let initial = group.name ? group.name.charAt(0).toUpperCase() : '?';
            picHtml = `<div class="avatar-placeholder">${initial}</div>`;
        }

        tray.append(`
            <div class="story-item" onclick="openStoryViewer(${index})">
                <div class="${ringClass}">
                    ${picHtml}
                </div>
                <div class="story-username">${group.name}</div>
            </div>
        `);
    });
}

function openModal(modalId) {
    $('#' + modalId).addClass('active');
}

function closeModal(modalId) {
    $('#' + modalId).removeClass('active');
    if (modalId === 'view-story-modal') {
        clearTimeout(storyTimer);
    }
}

function openStoryViewer(groupIndex) {
    if (!storiesData[groupIndex] || storiesData[groupIndex].stories.length === 0) return;
    
    currentStoryGroupIndex = groupIndex;
    
    // Find the first unread story in this group, or start at 0 if all read
    let startIndex = 0;
    let group = storiesData[groupIndex];
    for (let i = 0; i < group.stories.length; i++) {
        if (!group.stories[i].is_viewed) {
            startIndex = i;
            break;
        }
    }
    
    currentStoryIndex = startIndex;
    openModal('view-story-modal');
    renderCurrentStory();
}

function renderCurrentStory() {
    clearTimeout(storyTimer);
    
    let group = storiesData[currentStoryGroupIndex];
    let story = group.stories[currentStoryIndex];
    
    // Setup header
    let picUrl = (group.profile_pic && group.profile_pic !== 'default.png') ? 'uploads/profiles/' + group.profile_pic : '';
    $('#story-viewer-avatar').attr('src', picUrl).toggle(!!picUrl);
    $('#story-viewer-name').text(group.name);
    
    // Simple time ago
    let t = new Date(story.created_at);
    let diff = Math.floor((new Date() - t) / 1000); // seconds
    let timeStr = diff < 60 ? 'just now' : 
                  diff < 3600 ? Math.floor(diff/60) + 'm' : 
                  Math.floor(diff/3600) + 'h';
    $('#story-viewer-time').text(timeStr);
    
    // Setup content
    $('#story-viewer-text').text(story.text_content || '');
    if (story.image) {
        $('#story-viewer-img').attr('src', 'uploads/stories/' + story.image).show();
    } else {
        $('#story-viewer-img').hide();
    }
    
    // Setup progress bars
    let progressContainer = $('#story-progress-container');
    progressContainer.empty();
    for (let i = 0; i < group.stories.length; i++) {
        progressContainer.append(`
            <div class="story-progress-bar">
                <div class="story-progress-fill" id="progress-fill-${i}"></div>
            </div>
        `);
        // Fill previously seen segments
        if (i < currentStoryIndex) {
            $(`#progress-fill-${i}`).css('width', '100%');
        }
    }
    
    // Check if it's my own story to show viewers button
    // Assuming currentUser ID is passed or inferable. We'll check if group is at index 0 and has name 'You' maybe?
    // Let's use an ajax call to fetch views if it's mine. Wait, we can get current user ID by checking if there's a specific class or we can just fetch and handle error.
    // Let's check the API directly
    $.getJSON('get_story_viewers_ajax.php?story_id=' + story.id, function(res) {
        if (res.success) {
            $('#story-footer-actions').show();
            $('#story-views-count').text(res.viewers.length);
        } else {
            $('#story-footer-actions').hide();
        }
    });

    // Mark as viewed
    if (!story.is_viewed) {
        story.is_viewed = true;
        $.ajax({
            url: 'view_story_ajax.php',
            type: 'POST',
            data: JSON.stringify({ story_id: story.id }),
            contentType: 'application/json'
        });
        
        // Re-check if all are viewed
        group.all_viewed = group.stories.every(s => s.is_viewed);
        renderStoriesTray(); // Update tray ring
    }
    
    // Animate progress
    let fill = $(`#progress-fill-${currentStoryIndex}`);
    fill.css({ width: '0%', transition: 'none' });
    setTimeout(() => {
        fill.css({ width: '100%', transition: `width ${STORY_DURATION}ms linear` });
    }, 50);
    
    // Auto advance
    storyTimer = setTimeout(nextStory, STORY_DURATION);
}

function nextStory() {
    let group = storiesData[currentStoryGroupIndex];
    if (currentStoryIndex < group.stories.length - 1) {
        currentStoryIndex++;
        renderCurrentStory();
    } else {
        // Move to next group
        if (currentStoryGroupIndex < storiesData.length - 1) {
            currentStoryGroupIndex++;
            currentStoryIndex = 0;
            renderCurrentStory();
        } else {
            closeModal('view-story-modal');
        }
    }
}

function prevStory() {
    if (currentStoryIndex > 0) {
        currentStoryIndex--;
        renderCurrentStory();
    } else {
        // Move to prev group
        if (currentStoryGroupIndex > 0) {
            currentStoryGroupIndex--;
            let group = storiesData[currentStoryGroupIndex];
            currentStoryIndex = group.stories.length - 1;
            renderCurrentStory();
        } else {
            // Replay first story
            renderCurrentStory();
        }
    }
}

function openViewersModal() {
    clearTimeout(storyTimer); // Pause story
    
    let story = storiesData[currentStoryGroupIndex].stories[currentStoryIndex];
    $.getJSON('get_story_viewers_ajax.php?story_id=' + story.id, function(res) {
        if (res.success) {
            let container = $('#viewers-list-container');
            container.empty();
            
            if (res.viewers.length === 0) {
                container.append('<p style="color:var(--muted); text-align:center;">No viewers yet.</p>');
            } else {
                res.viewers.forEach(v => {
                    let picUrl = (v.profile_pic && v.profile_pic !== 'default.png') ? 'uploads/profiles/' + v.profile_pic : '';
                    let imgHtml = picUrl ? `<img src="${picUrl}" alt="${v.name}">` : `<div style="width:36px;height:36px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:bold;font-size:0.8rem;">${v.name.charAt(0)}</div>`;
                    
                    container.append(`
                        <div class="viewer-item">
                            ${imgHtml}
                            <div class="name">${v.name}</div>
                            <!-- Could add time formatting here -->
                        </div>
                    `);
                });
            }
            openModal('story-viewers-modal');
        }
    });
}
