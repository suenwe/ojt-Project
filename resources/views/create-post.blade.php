<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vibely | Community Feed</title>
    <link rel="stylesheet" href="{{ asset('assets/post-prototype.css') }}">

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
        .vp-preview-media, .vp-current-media { padding: 0; overflow: hidden; background: #10141b; border: 1px solid var(--line); border-radius: 6px; }
        .vp-carousel { position: relative; min-width: 0; overflow: hidden; background: #10141b; }
        .vp-carousel-track { display: flex; overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; overscroll-behavior-x: contain; }
        .vp-carousel-track::-webkit-scrollbar { display: none; }
        
        /* Fixed-size media area for every post */
        .vp-post-media {
            margin-top: 12px;
            width: 100%;
            max-width: 100%;
            overflow: hidden;
            border-radius: 8px;
        }

        /* Keep the carousel within a consistent height */
        .vp-carousel {
            position: relative;
            width: 100%;
            height: 300px;
            overflow: hidden;
            background: #10141b;
            border-radius: 8px;
        }

        /* Each photo/video occupies the same fixed-size area */
        .vp-carousel-slide {
            display: flex;
            flex: 0 0 100%;
            justify-content: center;
            align-items: center;
            width: 100%;
            min-width: 0;
            height: 300px;
            overflow: hidden;
            scroll-snap-align: center;
        }

        /* Show the entire image without cropping */
        .vp-carousel-slide img,
        .vp-carousel-slide video {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* Keep media a little shorter on mobile */
        @media (max-width: 700px) {
            .vp-carousel,
            .vp-carousel-slide {
                height: 240px;
            }
        }

        .vp-carousel-count { position: absolute; top: 10px; right: 10px; padding: 3px 8px; color: white; background: rgb(0 0 0 / 62%); border-radius: 20px; font-size: 11px; }
        .vp-carousel-nav { position: absolute; top: 50%; display: grid; place-items: center; width: 34px; height: 34px; color: #20232a; background: rgb(255 255 255 / 90%); border: 0; border-radius: 50%; font-size: 22px; line-height: 1; transform: translateY(-50%); }
        .vp-carousel-prev { left: 10px; }
        .vp-carousel-next { right: 10px; }
        .vp-carousel-dots { position: absolute; right: 0; bottom: 9px; left: 0; display: flex; justify-content: center; gap: 5px; }
        .vp-carousel-dot { width: 6px; height: 6px; padding: 0; background: rgb(255 255 255 / 55%); border: 0; border-radius: 50%; }
        .vp-carousel-dot[aria-current="true"] { background: white; }
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
        .vp-post-actions { display: flex; align-items: center; gap: 18px; padding-top: 12px; border-top: 1px solid var(--line-soft); }
        .vp-post-action { display: inline-flex; align-items: center; gap: 6px; padding: 4px 0; color: var(--ink); background: transparent; border: 0; font-size: 13px; font-weight: 650; }
        .vp-post-action:hover, .vp-post-action[aria-pressed="true"] { color: var(--red); }
        .vp-like-icon { font-size: 20px; line-height: 1; }
        .vp-comment-panel { padding-top: 12px; }
        .vp-comment-list { display: grid; gap: 8px; margin-bottom: 11px; }
        .vp-comment { display: flex; gap: 8px; align-items: baseline; overflow-wrap: anywhere; font-size: 12px; }
        .vp-comment strong { flex: 0 0 auto; }
        .vp-comment-form { display: flex; gap: 8px; }
        .vp-comment-form input { flex: 1; min-width: 0; padding: 8px 10px; color: var(--ink); background: var(--surface); border: 1px solid var(--line); border-radius: 5px; }
        .vp-comment-form button { padding: 7px 10px; color: var(--brand); background: transparent; border: 0; font-weight: 700; }
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
    <a class="brand" href="#feed"><span class="brand-mark">V</span> Vibely</a>
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
        
        {{-- Success/Error Messages from Laravel --}}
        @if(session('success'))
            <div class="vp-notice" style="margin-bottom: 15px;">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="vp-notice" style="margin-bottom: 15px; background: #fee2e2; color: #991b1b;">
                <ul style="margin: 0; padding-left: 15px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="panel vp-card">
            <h2>Create a Post</h2>
            <p class="subtext" style="margin-bottom:19px">What's on your mind today?</p>
            <form id="create-form" action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="vp-form-field">
                    <label for="post-content">Post Description <span class="vp-required">*</span></label>
                    <textarea class="field" id="post-content" name="content" maxlength="1200" placeholder="Share your thoughts, questions or experiences..." required></textarea>
                    <span class="vp-count" id="create-count">0 / 1200</span>
                </div>
                <div class="vp-two-columns">
                    <div class="vp-form-field">
                        <label for="post-category">Category <span class="vp-required">*</span></label>
                        <select class="field" id="post-category" name="category_id" required>
                            <option value="">Select Category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->category_id }}">
                                    {{ $category->category_type }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="vp-form-field">
                        <label for="postType">Post Type <span class="vp-required">*</span></label>
                        <select class="field" id="postType" name="post_type" required>
                            <option value="">Select Post Type</option>
                            <option value="Questions">Questions</option>
                            <option value="Discussion">Discussion</option>
                            <option value="Knowledge Sharing">Knowledge Sharing</option>
                        </select>
                    </div>
                    <div class="vp-form-field">
                        <label for="post-tags">Hashtags (Optional)</label>
                        <input class="field" type="text" id="post-tags" name="hashtag" maxlength="150" placeholder="#learning #vibely">
                    </div>
                </div>
                <div class="vp-form-field">
                    <label for="post-media">Add Photo or Video (Optional)</label>
                    <input class="field" type="file" id="post-media" name="media[]" accept="image/jpeg,image/png,image/webp,video/mp4,video/webm" multiple>
                    <span class="vp-help">Select multiple photos or videos. JPG, PNG, WebP: max 5 MB each · MP4, WebM: max 25 MB each · Up to 10 files</span>
                    <p class="vp-error" id="create-media-error" role="alert" hidden></p>
                    <div class="vp-preview-media" id="create-media-preview" hidden></div>
                </div>
                <div class="vp-actions">
                    <button class="btn secondary" type="reset">Clear</button>
                    <button class="btn" type="submit">Publish Post</button>
                </div>
            </form>
        </section>
        <div><h2>Latest Posts</h2><p class="subtext">Your posts appear here.</p></div>
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
            <label for="edit-media">Replace Photos or Videos (Optional)</label>
            <input class="field" type="file" id="edit-media" accept="image/jpeg,image/png,image/webp,video/mp4,video/webm" multiple>
            <span class="vp-help">Selecting files replaces the current media. Up to 10 files; images max 5 MB each and videos max 25 MB each.</span>
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
    // Categories array for the Edit Dialog (Dynamic from database)
    const categories = @json($categories->pluck('category_type'));

    const serverPosts = @json($serverPosts);

    const createForm = document.getElementById('create-form');
    const createContent = document.getElementById('post-content');
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
    
    // Initialize with database posts
    let posts = serverPosts; 
    let nextId = serverPosts.length + 1;
    let editingId = null;
    let createDraftUrls = [];
    let editDraftUrls = [];

    // Fill edit dropdown with categories
    categories.forEach(function(name) {
        const option = document.createElement('option');
        option.value = name;
        option.textContent = name;
        editCategory.appendChild(option);
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

    function checkFiles(input, errorElement) {
        input.setCustomValidity('');
        errorElement.hidden = true;
        const files = Array.from(input.files);
        if (!files.length) return true;

        const images = ['image/jpeg', 'image/png', 'image/webp'];
        const videos = ['video/mp4', 'video/webm'];
        let error = '';
        if (files.length > 10) error = 'Choose no more than 10 files.';
        for (const file of files) {
            const sizeLimit = images.includes(file.type)
                ? 5 * 1024 * 1024
                : videos.includes(file.type)
                    ? 25 * 1024 * 1024
                    : 0;
            if (!sizeLimit) error = 'Choose JPG, PNG, WebP, MP4 or WebM files only.';
            else if (file.size > sizeLimit) error = file.name + ' is too large for its media type.';
            if (error) break;
        }

        if (error) {
            errorElement.textContent = error;
            errorElement.hidden = false;
            input.setCustomValidity(error);
            return false;
        }
        return true;
    }

    function drawMedia(container, mediaItems) {
        container.replaceChildren();
        if (!mediaItems.length) { container.hidden = true; return; }

        const carousel = document.createElement('div');
        carousel.className = 'vp-carousel';
        const track = document.createElement('div');
        track.className = 'vp-carousel-track';
        track.setAttribute('role', 'region');
        track.setAttribute('aria-label', 'Post media. Swipe or use the arrow buttons to browse.');
        track.tabIndex = 0;
        
        mediaItems.forEach(function(item, index) {
            const slide = document.createElement('div');
            slide.className = 'vp-carousel-slide';
            slide.setAttribute('aria-label', 'Media ' + (index + 1) + ' of ' + mediaItems.length);
            
            // Determine if it's a video or image
            const isVideo = item.type.startsWith('video/');
            const element = document.createElement(isVideo ? 'video' : 'img');
            
            element.src = item.url;
            
            if (!isVideo) {
                element.alt = 'Post media ' + (index + 1);
            } else {
                element.controls = true;
                element.playsInline = true;
            }
            slide.appendChild(element);
            track.appendChild(slide);
        });
        carousel.appendChild(track);

        if (mediaItems.length > 1) {
            const count = document.createElement('span');
            count.className = 'vp-carousel-count';
            count.textContent = '1 / ' + mediaItems.length;
            carousel.appendChild(count);

            const previous = makeButton('‹', 'vp-carousel-nav vp-carousel-prev');
            previous.setAttribute('aria-label', 'Previous media');
            const next = makeButton('›', 'vp-carousel-nav vp-carousel-next');
            next.setAttribute('aria-label', 'Next media');
            const dots = document.createElement('div');
            dots.className = 'vp-carousel-dots';
            let currentIndex = 0;
            
            function updateCarouselPosition(index) {
                currentIndex = Math.max(0, Math.min(mediaItems.length - 1, index));
                count.textContent = (currentIndex + 1) + ' / ' + mediaItems.length;
                dotButtons.forEach(function(dot, dotIndex) {
                    dot.setAttribute('aria-current', String(dotIndex === currentIndex));
                });
            }
            function goToSlide(index) {
                updateCarouselPosition(index);
                track.scrollTo({ left: currentIndex * track.clientWidth, behavior: 'smooth' });
            }
            const dotButtons = mediaItems.map(function(item, index) {
                const dot = makeButton('', 'vp-carousel-dot');
                dot.setAttribute('aria-label', 'Show media ' + (index + 1));
                dot.setAttribute('aria-current', String(index === 0));
                dot.addEventListener('click', function() {
                    goToSlide(index);
                });
                dots.appendChild(dot);
                return dot;
            });
            previous.addEventListener('click', function() {
                goToSlide(currentIndex - 1);
            });
            next.addEventListener('click', function() {
                goToSlide(currentIndex + 1);
            });
            track.addEventListener('scroll', function() {
                updateCarouselPosition(Math.round(track.scrollLeft / track.clientWidth));
            }, { passive: true });
            carousel.append(previous, next, dots);
        }
        container.appendChild(carousel);
        container.hidden = false;
    }

    function revokeMediaItems(mediaItems) {
        mediaItems.forEach(function(item) { 
            // Only revoke if it's a local blob URL (not a server URL)
            if (item.url.startsWith('blob:')) {
                URL.revokeObjectURL(item.url); 
            }
        });
    }

    function revokeDraft(kind) {
        const urls = kind === 'create' ? createDraftUrls : editDraftUrls;
        urls.forEach(function(url) { URL.revokeObjectURL(url); });
        if (kind === 'create') createDraftUrls = [];
        else editDraftUrls = [];
    }

    function handleMediaChange(kind) {
        const input = kind === 'create' ? createMedia : editMedia;
        const error = kind === 'create' ? createMediaError : editMediaError;
        const preview = kind === 'create' ? createMediaPreview : editMediaPreview;
        revokeDraft(kind);
        drawMedia(preview, []);
        if (!checkFiles(input, error) || !input.files.length) return;
        const files = Array.from(input.files);
        const urls = files.map(function(file) { return URL.createObjectURL(file); });
        if (kind === 'create') createDraftUrls = urls;
        else editDraftUrls = urls;
        drawMedia(preview, files.map(function(file, index) {
            return { url: urls[index], type: file.type };
        }));
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
        timestamp.textContent = 'Just now';
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
            revokeMediaItems(post.mediaItems);
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

        if (post.mediaItems.length) {
            const mediaContainer = document.createElement('div');
            mediaContainer.className = 'vp-post-media';
            drawMedia(mediaContainer, post.mediaItems);
            card.appendChild(mediaContainer);
        }
        if (post.hashtags) {
            const tags = document.createElement('p');
            tags.className = 'vp-post-tags';
            tags.textContent = post.hashtags;
            card.appendChild(tags);
        }

        const postActions = document.createElement('div');
        postActions.className = 'vp-post-actions';
        const likeButton = makeButton('', 'vp-post-action vp-like');
        likeButton.setAttribute('aria-pressed', String(post.liked));
        likeButton.setAttribute('aria-label', post.liked ? 'Unlike post' : 'Like post');
        const heart = document.createElement('span');
        heart.className = 'vp-like-icon';
        heart.textContent = post.liked ? '♥' : '♡';
        const likeLabel = document.createElement('span');
        likeLabel.textContent = 'Like';
        const likeCount = document.createElement('span');
        likeCount.textContent = String(post.likes);
        likeButton.append(heart, likeLabel, likeCount);
        likeButton.addEventListener('click', function() {
            post.liked = !post.liked;
            post.likes += post.liked ? 1 : -1;
            heart.textContent = post.liked ? '♥' : '♡';
            likeCount.textContent = String(post.likes);
            likeButton.setAttribute('aria-pressed', String(post.liked));
            likeButton.setAttribute('aria-label', post.liked ? 'Unlike post' : 'Like post');
        });

        const commentButton = makeButton('Comment (' + post.comments.length + ')', 'vp-post-action');
        commentButton.setAttribute('aria-expanded', 'false');
        postActions.append(likeButton, commentButton);
        card.appendChild(postActions);

        const commentPanel = document.createElement('section');
        commentPanel.className = 'vp-comment-panel';
        commentPanel.hidden = true;
        const commentList = document.createElement('div');
        commentList.className = 'vp-comment-list';
        post.comments.forEach(function(comment) {
            const row = document.createElement('div');
            row.className = 'vp-comment';
            const commenter = document.createElement('strong');
            commenter.textContent = 'Alex Morgan';
            const commentText = document.createElement('span');
            commentText.textContent = comment;
            row.append(commenter, commentText);
            commentList.appendChild(row);
        });
        const commentForm = document.createElement('form');
        commentForm.className = 'vp-comment-form';
        const commentInput = document.createElement('input');
        commentInput.type = 'text';
        commentInput.maxLength = 300;
        commentInput.placeholder = 'Add a comment...';
        commentInput.setAttribute('aria-label', 'Write a comment');
        commentInput.required = true;
        const commentSubmit = makeButton('Post', '');
        commentSubmit.type = 'submit';
        commentForm.append(commentInput, commentSubmit);
        commentPanel.append(commentList, commentForm);
        card.appendChild(commentPanel);
        commentButton.addEventListener('click', function() {
            commentPanel.hidden = !commentPanel.hidden;
            commentButton.setAttribute('aria-expanded', String(!commentPanel.hidden));
            if (!commentPanel.hidden) commentInput.focus();
        });
        commentForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const text = commentInput.value.trim();
            if (!text) return;
            post.comments.push(text);
            const row = document.createElement('div');
            row.className = 'vp-comment';
            const commenter = document.createElement('strong');
            commenter.textContent = 'Alex Morgan';
            const commentText = document.createElement('span');
            commentText.textContent = text;
            row.append(commenter, commentText);
            commentList.appendChild(row);
            commentButton.textContent = 'Comment (' + post.comments.length + ')';
            commentInput.value = '';
            commentInput.focus();
        });
        return card;
    }

    function renderPosts() {
        feed.replaceChildren();
        posts.forEach(function(post) { feed.appendChild(buildPostCard(post)); });
    }

    function resetCreatePreview() {
        revokeDraft('create');
        drawMedia(createMediaPreview, []);
        createMedia.setCustomValidity('');
        createMediaError.hidden = true;
        createContent.setCustomValidity('');
        createCount.textContent = '0 / 1200';
    }
    createForm.addEventListener('reset', function() {
        resetCreatePreview();
        notice.hidden = true;
    });

    function openEdit(id) {
        const post = posts.find(function(item) { return item.id === id; });
        if (!post) return;
        editingId = id;
        editForm.reset();
        revokeDraft('edit');
        drawMedia(editMediaPreview, []);
        editMediaError.hidden = true;
        editMedia.setCustomValidity('');
        editContent.setCustomValidity('');
        editContent.value = post.content;
        editCategory.value = post.category;
        editTags.value = post.hashtags;
        updateCount(editContent, editCount);
        drawMedia(currentMedia, post.mediaItems);
        removeMediaLabel.hidden = !post.mediaItems.length;
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
        drawMedia(editMediaPreview, []);
        editingId = null;
    });

    editForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const post = posts.find(function(item) { return item.id === editingId; });
        if (!post) return;
        const text = editContent.value.trim();
        editContent.setCustomValidity(text ? '' : 'Write a post description.');
        if (!checkFiles(editMedia, editMediaError) || !editForm.reportValidity()) return;

        post.content = text;
        post.category = editCategory.value;
        post.hashtags = editTags.value.trim();
        const replacements = Array.from(editMedia.files);
        if (replacements.length && editDraftUrls.length) {
            revokeMediaItems(post.mediaItems);
            post.mediaItems = replacements.map(function(file, index) {
                return { url: editDraftUrls[index], type: file.type };
            });
            editDraftUrls = []; // Transfer preview URL ownership to the post.
        } else if (removeMedia.checked) {
            revokeMediaItems(post.mediaItems);
            post.mediaItems = [];
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

    // Initial render of the posts from the database
    renderPosts();
</script>
</body>
</html>