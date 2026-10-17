<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knowvia | Community Feed</title>
    <link rel="stylesheet" href="{{ asset('assets/prototype.css') }}">

    <style>
        .vp-main { display: grid; gap: 17px; min-width: 0; }
        .vp-card { padding: 20px; }
        .vp-card h2 { margin-bottom: 3px; }
        .vp-form-field { display: flex; flex-direction: column; gap: 7px; min-width: 0; margin-bottom: 16px; }
        .vp-form-field > label { font-size: 13px; font-weight: 700; }
        .vp-form-field .field { min-width: 0; min-height: 44px; width: 100%; }
        .vp-form-field textarea.field { min-height: 122px; resize: vertical; }
        .vp-two-columns { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 15px; align-items: start; }
        .vp-two-columns .vp-form-field { margin-bottom: 16px; }
        .vp-required { color: var(--red); }
        .vp-help, .vp-count { color: var(--muted); font-size: 11px; }
        .vp-count { align-self: flex-end; }
        .vp-error { margin: 0; color: var(--red); font-size: 12px; }
        .vp-actions { display: flex; justify-content: flex-end; flex-wrap: wrap; gap: 9px; }
        .vp-actions .btn { min-width: 110px; }
        .vp-preview-media, .vp-current-media { padding: 10px; background: var(--page); border: 1px solid var(--line); border-radius: 6px; }
        .vp-preview-media img, .vp-preview-media video,
        .vp-current-media img, .vp-current-media video,
        .vp-post-media img, .vp-post-media video { display: block; width: 100%; max-height: 340px; object-fit: contain; border-radius: 5px; }
        .vp-notice { padding: 11px 13px; background: var(--green-soft); color: var(--green); border-radius: 6px; font-size: 12px; }
        .vp-post { padding: 17px; }
        .vp-post-head { display: flex; align-items: flex-start; gap: 10px; }
        .vp-post-author { min-width: 0; flex: 1; }
        .vp-post-author strong { display: block; font-size: 13px; }
        .vp-post-author small { color: var(--muted); font-size: 11px; }
        .vp-post-text { margin: 14px 0; white-space: pre-wrap; overflow-wrap: anywhere; }
        .vp-post-tags { margin: 10px 0 0; color: var(--brand); overflow-wrap: anywhere; }
        .vp-post-media { margin-top: 12px; }
        .vp-post-category { margin-top: 11px; }
        .vp-menu-wrap { position: relative; margin-left: auto; flex-shrink: 0; }
        .vp-more { display: grid; place-items: center; width: 35px; height: 34px; background: transparent; border: 0; border-radius: 6px; color: var(--ink); font-size: 23px; line-height: 1; }
        .vp-more:hover, .vp-more[aria-expanded="true"] { background: var(--brand-soft); }
        .vp-menu { position: absolute; z-index: 30; right: 0; top: 39px; min-width: 148px; padding: 5px; background: var(--surface); border: 1px solid var(--line); border-radius: 8px; box-shadow: 0 9px 22px rgb(0 0 0 / 12%); }
        .vp-menu button { display: block; width: 100%; padding: 9px 11px; border: 0; background: transparent; border-radius: 5px; color: var(--ink); text-align: left; font-size: 13px; }
        .vp-menu button:hover { background: var(--brand-soft); }
        .vp-menu .vp-delete { color: var(--red); }
        .vp-dialog { width: min(530px, calc(100% - 28px)); max-height: 90vh; overflow-y: auto; padding: 24px; color: var(--ink); background: var(--surface); border: 1px solid var(--line); border-radius: 10px; box-shadow: 0 16px 45px rgb(0 0 0 / 18%); }
        .vp-dialog::backdrop { background: rgb(10 16 40 / 58%); }
        .vp-dialog-head { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 15px; }
        .vp-close { padding: 5px 10px; background: transparent; color: var(--muted); border: 0; font-size: 24px; }
        .vp-inline-check { display: flex; gap: 8px; align-items: center; margin: 8px 0 15px; font-size: 12px; }
        .vp-static-nav { display: block; padding: 9px 11px; color: var(--muted); font-weight: 600; }
        [hidden] { display: none !important; }
        @media (max-width: 700px) {
            .vp-two-columns { grid-template-columns: 1fr; gap: 0; }
            .vp-card { padding: 15px; }
            .vp-dialog { padding: 17px; }
        }
    </style>
</head>
<body>
<header class="topbar">
    <a class="brand" href="#feed"><span class="brand-mark">V</span> Knowvia</a>
    <div class="top-actions">
        <a class="top-link" href="/ui-preview/edit-profile">My Profile</a>
        <span class="avatar">AM</span>
    </div>
</header>

<div class="app-layout">
    <aside class="sidebar">
        <div class="panel side-panel">
            <p class="side-heading">Community</p>
            <nav class="side-nav">
                <a class="active" href="#feed">Home Feed</a>
                <span class="vp-static-nav">Explore Topics</span>
                <a href="/ui-preview/edit-profile">My Profile</a>
                <a href="/ui-preview/change-password">Account Security</a>
            </nav>
        </div>
        <div class="panel account-card">
            <span class="avatar">AM</span>
            <div><strong>Alex Morgan</strong><small>Community Member</small></div>
        </div>
    </aside>

    <main class="vp-main" id="feed">
        <div class="page-heading">
            <div><h1>Community Feed</h1><p class="subtext">Share your ideas with the community.</p></div>
        </div>
        <section class="panel vp-card">
            <h2>Create a Post</h2>
            <p class="subtext" style="margin-bottom:19px">What's on your mind today?</p>
            <form id="create-form">
                <div class="vp-form-field">
                    <label for="post-content">Post Description <span class="vp-required">*</span></label>
                    <textarea class="field" id="post-content" name="content" maxlength="1200" placeholder="Share your thoughts, questions or experiences..." required></textarea>
                    <span class="vp-count" id="create-count">0 / 1200</span>
                </div>
                <div class="vp-two-columns">
                    <div class="vp-form-field">
                        <label for="post-category">Category <span class="vp-required">*</span></label>
                        <select class="field" id="post-category" name="category" required>
                            <option value="">Select category</option>
                        </select>
                    </div>
                    <div class="vp-form-field">
                        <label for="post-tags">Hashtags (Optional)</label>
                        <input class="field" type="text" id="post-tags" name="hashtags" maxlength="150" placeholder="#learning #vibely">
                    </div>
                </div>
                <div class="vp-form-field">
                    <label for="post-media">Add Photo or Video (Optional)</label>
                    <input class="field" type="file" id="post-media" name="media" accept="image/jpeg,image/png,image/webp,video/mp4,video/webm">
                    <span class="vp-help">JPG, PNG, WebP: max 5 MB · MP4, WebM: max 25 MB · One file per post</span>
                    <p class="vp-error" id="create-media-error" role="alert" hidden></p>
                    <div class="vp-preview-media" id="create-media-preview" hidden></div>
                </div>
                <div class="vp-actions">
                    <button class="btn secondary" type="reset">Clear</button>
                    <button class="btn" type="submit">Publish Post</button>
                </div>
            </form>
        </section>
        <div><h2>Latest Posts</h2><p class="subtext">Your preview posts appear here.</p></div>
        <p class="vp-notice" id="notice" role="status" hidden></p>
        <div class="feed-list" id="post-feed" aria-live="polite"></div>
    </main>

    <aside class="rightbar">
        <section class="panel right-panel">
            <h3>Explore Topics</h3>
            <p class="subtext">Discover conversations from the community.</p>
        </section>
    </aside>
</div>

<!-- Edit Post popup: stays on the same page -->
<dialog class="vp-dialog" id="edit-dialog" aria-labelledby="edit-title">
    <div class="vp-dialog-head">
        <div><h2 id="edit-title">Edit Post</h2><p class="subtext">Make changes to your post.</p></div>
        <button class="vp-close" type="button" id="close-edit" aria-label="Close edit form">&times;</button>
    </div>
    <form id="edit-form">
        <div class="vp-form-field">
            <label for="edit-content">Post Description <span class="vp-required">*</span></label>
            <textarea class="field" id="edit-content" maxlength="1200" required></textarea>
            <span class="vp-count" id="edit-count">0 / 1200</span>
        </div>
        <div class="vp-two-columns">
            <div class="vp-form-field">
                <label for="edit-category">Category <span class="vp-required">*</span></label>
                <select class="field" id="edit-category" required><option value="">Select category</option></select>
            </div>
            <div class="vp-form-field">
                <label for="edit-tags">Hashtags (Optional)</label>
                <input class="field" id="edit-tags" type="text" maxlength="150" placeholder="#learning #vibely">
            </div>
        </div>
        <div class="vp-form-field">
            <label for="edit-media">Replace Photo or Video (Optional)</label>
            <input class="field" type="file" id="edit-media" accept="image/jpeg,image/png,image/webp,video/mp4,video/webm">
            <span class="vp-help">JPG, PNG, WebP: max 5 MB · MP4, WebM: max 25 MB</span>
            <p class="vp-error" id="edit-media-error" role="alert" hidden></p>
            <div class="vp-current-media" id="current-media" hidden></div>
            <div class="vp-preview-media" id="edit-media-preview" hidden></div>
            <label class="vp-inline-check" id="remove-media-label" hidden>
                <input type="checkbox" id="remove-media"> Remove existing photo/video
            </label>
        </div>
        <div class="vp-actions">
            <button class="btn secondary" type="button" id="cancel-edit">Cancel</button>
            <button class="btn" type="submit">Save Changes</button>
        </div>
    </form>
</dialog>

<script>
    // Preview data only: nothing is stored in a database.
    const categories = [
        'Technology', 'Education', 'Gaming', 'Entertainment',
        'Health & Fitness', 'Business & Career', 'Travel',
        'Art & Design', 'Fashion & Lifestyle', 'Food & Cooking',
        'Sports', 'General Discussion', 'Events & Activities',
        'Books & Literature', 'News & Updates'
    ];

    const createForm = document.getElementById('create-form');
    const createContent = document.getElementById('post-content');
    const createCategory = document.getElementById('post-category');
    const createTags = document.getElementById('post-tags');
    const createMedia = document.getElementById('post-media');
    const createMediaPreview = document.getElementById('create-media-preview');
    const createMediaError = document.getElementById('create-media-error');
    const createCount = document.getElementById('create-count');

    const editDialog = document.getElementById('edit-dialog');
    const editForm = document.getElementById('edit-form');
    const editContent = document.getElementById('edit-content');
    const editCategory = document.getElementById('edit-category');
    const editTags = document.getElementById('edit-tags');
    const editMedia = document.getElementById('edit-media');
    const editMediaPreview = document.getElementById('edit-media-preview');
    const editMediaError = document.getElementById('edit-media-error');
    const editCount = document.getElementById('edit-count');
    const currentMedia = document.getElementById('current-media');
    const removeMedia = document.getElementById('remove-media');
    const removeMediaLabel = document.getElementById('remove-media-label');

    const feed = document.getElementById('post-feed');
    const notice = document.getElementById('notice');
    let posts = [];
    let nextId = 1;
    let editingId = null;
    let createDraftUrl = null;
    let editDraftUrl = null;

    // Fill both dropdowns with the same 15 categories.
    categories.forEach(function(name) {
        [createCategory, editCategory].forEach(function(select) {
            const option = document.createElement('option');
            option.value = name;
            option.textContent = name;
            select.appendChild(option);
        });
    });

    function showNotice(text) {
        notice.textContent = text;
        notice.hidden = false;
    }

    function updateCount(input, element) {
        element.textContent = input.value.length + ' / 1200';
    }

    createContent.addEventListener('input', function() {
        createContent.setCustomValidity('');
        updateCount(createContent, createCount);
    });
    editContent.addEventListener('input', function() {
        editContent.setCustomValidity('');
        updateCount(editContent, editCount);
    });

    function checkFile(input, errorElement) {
        input.setCustomValidity('');
        errorElement.hidden = true;
        const file = input.files[0];
        if (!file) return true;

        const images = ['image/jpeg', 'image/png', 'image/webp'];
        const videos = ['video/mp4', 'video/webm'];
        const sizeLimit = images.includes(file.type)
            ? 5 * 1024 * 1024
            : videos.includes(file.type)
                ? 25 * 1024 * 1024
                : 0;

        let error = '';
        if (!sizeLimit) error = 'Choose a JPG, PNG, WebP, MP4 or WebM file.';
        else if (file.size > sizeLimit) error = 'File is too large for its media type.';

        if (error) {
            errorElement.textContent = error;
            errorElement.hidden = false;
            input.setCustomValidity(error);
            return false;
        }
        return true;
    }

    function drawMedia(container, url, type) {
        container.replaceChildren();
        if (!url) { container.hidden = true; return; }
        const element = document.createElement(type.startsWith('image/') ? 'img' : 'video');
        element.src = url;
        if (element.tagName === 'IMG') element.alt = 'Post media';
        else element.controls = true;
        container.appendChild(element);
        container.hidden = false;
    }

    function revokeDraft(kind) {
        if (kind === 'create' && createDraftUrl) {
            URL.revokeObjectURL(createDraftUrl);
            createDraftUrl = null;
        }
        if (kind === 'edit' && editDraftUrl) {
            URL.revokeObjectURL(editDraftUrl);
            editDraftUrl = null;
        }
    }

    function handleMediaChange(kind) {
        const input = kind === 'create' ? createMedia : editMedia;
        const error = kind === 'create' ? createMediaError : editMediaError;
        const preview = kind === 'create' ? createMediaPreview : editMediaPreview;
        revokeDraft(kind);
        drawMedia(preview, null, '');
        if (!checkFile(input, error) || !input.files[0]) return;
        const url = URL.createObjectURL(input.files[0]);
        if (kind === 'create') createDraftUrl = url;
        else editDraftUrl = url;
        drawMedia(preview, url, input.files[0].type);
    }
    createMedia.addEventListener('change', function() { handleMediaChange('create'); });
    editMedia.addEventListener('change', function() { handleMediaChange('edit'); });

    function closeMenus() {
        document.querySelectorAll('.vp-menu').forEach(function(menu) {
            menu.hidden = true;
            menu.previousElementSibling.setAttribute('aria-expanded', 'false');
        });
    }

    function makeButton(text, className) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = className;
        button.textContent = text;
        return button;
    }

    function buildPostCard(post) {
        const card = document.createElement('article');
        card.className = 'panel vp-post';
        const header = document.createElement('div');
        header.className = 'vp-post-head';
        const avatar = document.createElement('span');
        avatar.className = 'avatar';
        avatar.textContent = 'AM';
        const author = document.createElement('div');
        author.className = 'vp-post-author';
        const name = document.createElement('strong');
        name.textContent = 'Alex Morgan';
        const timestamp = document.createElement('small');
        timestamp.textContent = 'Just now · Preview';
        author.append(name, timestamp);

        const menuWrap = document.createElement('div');
        menuWrap.className = 'vp-menu-wrap';
        const more = makeButton('⋯', 'vp-more');
        more.setAttribute('aria-label', 'Post options');
        more.setAttribute('aria-haspopup', 'true');
        more.setAttribute('aria-expanded', 'false');
        const menu = document.createElement('div');
        menu.className = 'vp-menu';
        menu.hidden = true;
        const editButton = makeButton('Edit', 'vp-edit');
        const deleteButton = makeButton('Delete', 'vp-delete');
        menu.append(editButton, deleteButton);
        menuWrap.append(more, menu);
        header.append(avatar, author, menuWrap);

        more.addEventListener('click', function(event) {
            event.stopPropagation();
            const wasOpen = !menu.hidden;
            closeMenus();
            menu.hidden = wasOpen;
            more.setAttribute('aria-expanded', String(!menu.hidden));
        });
        editButton.addEventListener('click', function() {
            closeMenus();
            openEdit(post.id);
        });
        deleteButton.addEventListener('click', function() {
            closeMenus();
            if (!window.confirm('Delete this post? This cannot be undone in the preview.')) return;
            if (post.mediaUrl) URL.revokeObjectURL(post.mediaUrl);
            posts = posts.filter(function(item) { return item.id !== post.id; });
            renderPosts();
            showNotice('Post deleted from preview only. No database record was changed.');
        });

        const categoryTag = document.createElement('span');
        categoryTag.className = 'post-category vp-post-category';
        categoryTag.textContent = post.category;
        const description = document.createElement('p');
        description.className = 'vp-post-text';
        description.textContent = post.content;
        card.append(header, categoryTag, description);

        if (post.mediaUrl) {
            const mediaContainer = document.createElement('div');
            mediaContainer.className = 'vp-post-media';
            drawMedia(mediaContainer, post.mediaUrl, post.mediaType);
            card.appendChild(mediaContainer);
        }
        if (post.hashtags) {
            const tags = document.createElement('p');
            tags.className = 'vp-post-tags';
            tags.textContent = post.hashtags;
            card.appendChild(tags);
        }
        return card;
    }

    function renderPosts() {
        feed.replaceChildren();
        posts.forEach(function(post) { feed.appendChild(buildPostCard(post)); });
    }

    function resetCreatePreview() {
        revokeDraft('create');
        drawMedia(createMediaPreview, null, '');
        createMedia.setCustomValidity('');
        createMediaError.hidden = true;
        createContent.setCustomValidity('');
        createCount.textContent = '0 / 1200';
    }
    createForm.addEventListener('reset', function() {
        resetCreatePreview();
        notice.hidden = true;
    });

    createForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const text = createContent.value.trim();
        createContent.setCustomValidity(text ? '' : 'Write a post description.');
        if (!checkFile(createMedia, createMediaError) || !createForm.reportValidity()) return;

        const file = createMedia.files[0];
        const post = {
            id: nextId++,
            content: text,
            category: createCategory.value,
            hashtags: createTags.value.trim(),
            mediaUrl: file ? createDraftUrl : null,
            mediaType: file ? file.type : null
        };
        posts.unshift(post);
        createDraftUrl = null; // The post now owns the preview URL.
        createForm.reset();
        renderPosts();
        showNotice('Post added to preview. No post was saved to the database.');
    });

    function openEdit(id) {
        const post = posts.find(function(item) { return item.id === id; });
        if (!post) return;
        editingId = id;
        editForm.reset();
        revokeDraft('edit');
        drawMedia(editMediaPreview, null, '');
        editMediaError.hidden = true;
        editMedia.setCustomValidity('');
        editContent.setCustomValidity('');
        editContent.value = post.content;
        editCategory.value = post.category;
        editTags.value = post.hashtags;
        updateCount(editContent, editCount);
        drawMedia(currentMedia, post.mediaUrl, post.mediaType || '');
        removeMediaLabel.hidden = !post.mediaUrl;
        removeMedia.checked = false;
        editDialog.showModal();
        editContent.focus();
    }

    function closeEdit() {
        editDialog.close();
    }
    document.getElementById('close-edit').addEventListener('click', closeEdit);
    document.getElementById('cancel-edit').addEventListener('click', closeEdit);
    editDialog.addEventListener('click', function(event) {
        if (event.target === editDialog) closeEdit();
    });
    editDialog.addEventListener('close', function() {
        revokeDraft('edit');
        drawMedia(editMediaPreview, null, '');
        editingId = null;
    });

    editForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const post = posts.find(function(item) { return item.id === editingId; });
        if (!post) return;
        const text = editContent.value.trim();
        editContent.setCustomValidity(text ? '' : 'Write a post description.');
        if (!checkFile(editMedia, editMediaError) || !editForm.reportValidity()) return;

        post.content = text;
        post.category = editCategory.value;
        post.hashtags = editTags.value.trim();
        const replacement = editMedia.files[0];
        if (replacement && editDraftUrl) {
            if (post.mediaUrl) URL.revokeObjectURL(post.mediaUrl);
            post.mediaUrl = editDraftUrl;
            post.mediaType = replacement.type;
            editDraftUrl = null; // Transfer preview URL ownership to the post.
        } else if (removeMedia.checked) {
            if (post.mediaUrl) URL.revokeObjectURL(post.mediaUrl);
            post.mediaUrl = null;
            post.mediaType = null;
        }
        closeEdit();
        renderPosts();
        showNotice('Post updated in preview only. No database record was changed.');
    });

    // Close the three-dot menu when clicking anywhere else or pressing Escape.
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.vp-menu-wrap')) closeMenus();
    });
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') closeMenus();
    });
</script>
</body>
</html>
