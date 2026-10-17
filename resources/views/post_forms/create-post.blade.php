@extends('layouts.app')

@section('content')
<header class="topbar">
    <a class="brand" href="{{ url('/') }}" aria-label="Knowvia home">
        <span class="brand-mark">V</span>
        <span>Knowvia</span>
    </a>

    <div class="top-actions">
        <input class="theme-toggle" id="top-theme-toggle" type="checkbox">
        <label class="icon-button theme-toggle-label" for="top-theme-toggle" title="Toggle theme" aria-label="Toggle light and dark theme">◐</label>
        <details class="top-menu">
            <summary class="icon-button" aria-label="Open main menu">☰</summary>
            <nav aria-label="Main menu">
                <a href="{{ url('/') }}#posts">Posts</a>
                <a href="{{ route('posts.schedule') }}">Schedules</a>
                <a href="{{ url('/') }}#bookmarks">Saved posts</a>
                <a href="{{ url('/') }}#help">Help and support</a>
            </nav>
        </details>
    </div>
</header>

<div class="app-layout">
    <aside class="sidebar">
        <section class="panel account-card">
            <span class="avatar avatar-large" aria-label="Member profile">AM</span>
            <div class="account-copy">
                <strong>Alex Morgan</strong>
            </div>
            <div class="account-tools">
                <span class="account-icon bell-icon" title="Notifications" aria-label="Notifications"></span>
                <details class="account-settings">
                    <summary class="account-icon" title="Settings" aria-label="Open settings">⚙</summary>
                    <nav aria-label="Account settings">
                        <a href="/ui-preview/edit-profile">Profile settings</a>
                        <a href="{{ url('/') }}#logout">Sign out</a>
                    </nav>
                </details>
            </div>
        </div>
    </aside>

    <main class="main-column" id="home">
        <div class="feed-toolbar">
            <form class="search-box" action="{{ url('/') }}" method="GET" role="search">
                <span aria-hidden="true">⌕</span>
                <input type="search" name="search" placeholder="Search posts and hashtags" aria-label="Search posts and hashtags">
                <button class="search-submit" type="submit" aria-label="Submit search">↵</button>
            </form>
        </div>

        <div class="feed-actions">
            <div class="feed-action-buttons">
                <a class="btn" href="{{ route('posts.create') }}">Create post</a>
                <a class="btn secondary" href="{{ route('posts.schedule') }}">Schedule post</a>
            </div>
            <label class="sort-control" for="post-sort">
                Sort by:
                <select class="field filter-select" id="post-sort" name="sort" aria-label="Sort posts">
                    <option value="category">Category</option>
                    <option value="newest">Newest</option>
                    <option value="reacted">Most reacted</option>
                </select>
                <select class="field category-filter" name="category" aria-label="Filter by topic">
                    <option>All topics</option>
                    <option>Technology</option>
                    <option>Community</option>
                    <option>Education</option>
                </select>
            </label>
        </div>
        <section class="panel composer">
            <h2 class="composer-title">{{ $isScheduling ? 'Schedule a Post' : 'Create a Post' }}</h2>
            <p class="subtext">{{ $isScheduling ? 'Choose when your post should be published.' : "What's on your mind today?" }}</p>
            <form id="create-form">
                <div>
                    <label for="post-content">Post Description <span>*</span></label>
                    <textarea class="field" id="post-content" name="content" maxlength="1200" placeholder="Share your thoughts, questions or experiences..." required></textarea>
                    <span id="create-count">0 / 1200</span>
                </div>
                <div>
                    <div>
                        <label for="post-category">Category <span>*</span></label>
                        <select class="field" id="post-category" name="category" required>
                            <option value="">Select category</option>
                        </select>
                    </div>
                    <div>
                        <label for="post-tags">Hashtags (Optional)</label>
                        <input class="field" type="text" id="post-tags" name="hashtags" maxlength="150" placeholder="#learning #vibely">
                    </div>
                </div>
                <div id="schedule-post">
                    <label for="post-schedule">Schedule publication{{ $isScheduling ? '' : ' (Optional)' }}</label>
                    <input class="field" type="datetime-local" id="post-schedule" name="scheduled_at" @required($isScheduling)>
                    <span>{{ $isScheduling ? 'Choose a future date and time for this post.' : 'Leave empty to publish immediately.' }} This is currently a UI preview and is not saved to the database.</span>
                </div>
                <div>
                    <label for="post-media">Add Photo or Video (Optional)</label>
                    <input class="field" type="file" id="post-media" name="media" accept="image/jpeg,image/png,image/webp,video/mp4,video/webm">
                    <span>JPG, PNG, WebP: max 5 MB · MP4, WebM: max 25 MB · One file per post</span>
                    <p id="create-media-error" role="alert" hidden></p>
                    <div id="create-media-preview" hidden></div>
                </div>
                <div class="composer-footer">
                    <button class="btn secondary" type="reset">Clear</button>
                    <button class="btn" type="submit">{{ $isScheduling ? 'Schedule Post' : 'Publish Post' }}</button>
                </div>
            </form>
        </section>
        <p class="subtext" id="notice" role="status" hidden></p>
    </main>

    <aside class="rightbar">
        <section class="panel right-panel">
            <h3>Explore</h3>
            <p class="subtext">Browse posts by hashtag.</p>
            <div class="category-list">
                <a class="category-link" href="{{ url('/') }}#laravel"><span>#laravel</span><strong>12</strong></a>
                <a class="category-link" href="{{ url('/') }}#postgresql"><span>#postgresql</span><strong>8</strong></a>
                <a class="category-link" href="{{ url('/') }}#webdevelopment"><span>#webdevelopment</span><strong>5</strong></a>
            </div>
        </section>
    </aside>
</div>

<!-- Edit Post popup: stays on the same page -->
<dialog id="edit-dialog" aria-labelledby="edit-title">
    <div>
        <div><h2 id="edit-title">Edit Post</h2><p class="subtext">Make changes to your post.</p></div>
        <button type="button" id="close-edit" aria-label="Close edit form">&times;</button>
    </div>
    <form id="edit-form">
        <div>
            <label for="edit-content">Post Description <span>*</span></label>
            <textarea class="field" id="edit-content" maxlength="1200" required></textarea>
            <span id="edit-count">0 / 1200</span>
        </div>
        <div>
            <div>
                <label for="edit-category">Category <span>*</span></label>
                <select class="field" id="edit-category" required><option value="">Select category</option></select>
            </div>
            <div>
                <label for="edit-tags">Hashtags (Optional)</label>
                <input class="field" id="edit-tags" type="text" maxlength="150" placeholder="#learning #vibely">
            </div>
        </div>
        <div>
            <label for="edit-media">Replace Photo or Video (Optional)</label>
            <input class="field" type="file" id="edit-media" accept="image/jpeg,image/png,image/webp,video/mp4,video/webm">
            <span>JPG, PNG, WebP: max 5 MB · MP4, WebM: max 25 MB</span>
            <p id="edit-media-error" role="alert" hidden></p>
            <div id="current-media" hidden></div>
            <div id="edit-media-preview" hidden></div>
            <label id="remove-media-label" hidden>
                <input type="checkbox" id="remove-media"> Remove existing photo/video
            </label>
        </div>
        <div class="composer-footer">
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
    const createSchedule = document.getElementById('post-schedule');
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
        card.className = 'panel post-card';
        const header = document.createElement('div');
        header.className = 'post-header';
        const avatar = document.createElement('span');
        avatar.className = 'avatar';
        avatar.textContent = 'AM';
        const author = document.createElement('div');
        author.className = 'post-author';
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
        categoryTag.className = 'post-category';
        categoryTag.textContent = post.category;
        const description = document.createElement('p');
        description.className = 'post-text';
        description.textContent = post.content;
        card.append(header, categoryTag, description);

        if (post.mediaUrl) {
            const mediaContainer = document.createElement('div');
            mediaContainer.className = 'post-visual';
            drawMedia(mediaContainer, post.mediaUrl, post.mediaType);
            card.appendChild(mediaContainer);
        }
        if (post.hashtags) {
            const tags = document.createElement('p');
            tags.className = 'post-text';
            tags.textContent = post.hashtags;
            card.appendChild(tags);
        }
        return card;
    }

    function renderPosts() {
        if (!feed) return;
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
            scheduledAt: createSchedule.value,
            mediaUrl: file ? createDraftUrl : null,
            mediaType: file ? file.type : null
        };
        posts.unshift(post);
        createDraftUrl = null; // The post now owns the preview URL.
        createForm.reset();
        renderPosts();
        showNotice(post.scheduledAt
            ? 'Post scheduled in the preview. No post was saved to the database.'
            : 'Post added to preview. No post was saved to the database.');
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
@endsection
