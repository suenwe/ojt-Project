@extends('layouts.app')

@section('content')
<style>
    .vp-main { display: grid; gap: 17px; min-width: 0; max-width: 800px; margin: 0 auto; padding: 20px; }
    .vp-card { padding: 20px; background: var(--surface); border: 1px solid var(--line); border-radius: 8px; }
    .vp-card h2 { margin-bottom: 3px; }
    .vp-form-field { display: flex; flex-direction: column; gap: 7px; min-width: 0; margin-bottom: 16px; }
    .vp-form-field > label { font-size: 13px; font-weight: 700; }
    .vp-form-field .field { min-width: 0; min-height: 44px; width: 100%; }
    .vp-form-field textarea.field { min-height: 122px; resize: vertical; }
    .vp-two-columns { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 15px; align-items: start; }
    .vp-required { color: var(--red); }
    .vp-help, .vp-count { color: var(--muted); font-size: 11px; }
    .vp-count { align-self: flex-end; }
    .vp-error { margin: 0; color: var(--red); font-size: 12px; }
    .vp-actions { display: flex; justify-content: flex-end; flex-wrap: wrap; gap: 9px; }
    .vp-notice { padding: 11px 13px; background: var(--green-soft); color: var(--green); border-radius: 6px; font-size: 12px; margin-bottom: 15px; }
    
    /* Carousel Styles */
    .vp-preview-media { padding: 0; overflow: hidden; background: #10141b; border: 1px solid var(--line); border-radius: 6px; margin-top: 10px; }
    .vp-carousel { position: relative; width: 100%; height: 300px; overflow: hidden; background: #10141b; border-radius: 8px; }
    .vp-carousel-track { display: flex; overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; height: 100%; }
    .vp-carousel-track::-webkit-scrollbar { display: none; }
    .vp-carousel-slide { display: flex; flex: 0 0 100%; justify-content: center; align-items: center; width: 100%; height: 100%; scroll-snap-align: center; }
    .vp-carousel-slide img, .vp-carousel-slide video { display: block; width: 100%; height: 100%; object-fit: contain; }
    .vp-carousel-count { position: absolute; top: 10px; right: 10px; padding: 3px 8px; color: white; background: rgb(0 0 0 / 62%); border-radius: 20px; font-size: 11px; }
    .vp-carousel-nav { position: absolute; top: 50%; display: grid; place-items: center; width: 34px; height: 34px; color: #20232a; background: rgb(255 255 255 / 90%); border: 0; border-radius: 50%; font-size: 22px; line-height: 1; transform: translateY(-50%); cursor: pointer; }
    .vp-carousel-prev { left: 10px; }
    .vp-carousel-next { right: 10px; }
    .vp-carousel-dots { position: absolute; right: 0; bottom: 9px; left: 0; display: flex; justify-content: center; gap: 5px; }
    .vp-carousel-dot { width: 6px; height: 6px; padding: 0; background: rgb(255 255 255 / 55%); border: 0; border-radius: 50%; }
    .vp-carousel-dot[aria-current="true"] { background: white; }
    [hidden] { display: none !important; }

    @media (max-width: 700px) {
        .vp-two-columns { grid-template-columns: 1fr; gap: 0; }
        .vp-card { padding: 15px; }
        .vp-carousel, .vp-carousel-slide { height: 240px; }
    }
</style>

    <!-- Updated Header matching the Welcome page screenshot -->
    <header class="topbar">
        <a class="brand" href="{{ url('/') }}" aria-label="Knowvia home">
            <span class="brand-mark">V</span>
            <span>Knowvia</span>
        </a>

        <div class="top-actions">
            <!-- Theme Toggle -->
            <input class="theme-toggle" id="top-theme-toggle" type="checkbox">
            <label class="icon-button theme-toggle-label" for="top-theme-toggle" title="Toggle theme" aria-label="Toggle light and dark theme">◐</label>
            
            <!-- Hamburger Menu -->
            <details class="top-menu">
                <summary class="icon-button" aria-label="Open main menu">☰</summary>
                <nav aria-label="Main menu">
                    <a href="{{ route('home') }}">Home Feed</a>
                    <a href="{{ route('posts.create') }}">Create post</a>
                    <a href="{{ route('schedule-post') }}">Schedule post</a>
                    <a href="#bookmarks">Saved posts</a>
                    <a href="#help">Help and support</a>
                </nav>
            </details>
        </div>
    </header>

    <div class="app-layout" style="display: flex; justify-content: center;">
        <main class="vp-main" id="feed">
            <div class="page-heading">
                <div><h1>Create a Post</h1><p class="subtext">Share your ideas with the community.</p></div>
            </div>
            
            @if(session('success'))
                <div class="vp-notice">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="vp-notice" style="background: #fee2e2; color: #991b1b;">
                    <ul style="margin: 0; padding-left: 15px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="panel vp-card">
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
                                    <option value="{{ $category->category_id }}">{{ $category->category_type }}</option>
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
                        <span class="vp-help">Select multiple photos or videos. Up to 10 files.</span>
                        <div class="vp-preview-media" id="create-media-preview" hidden></div>
                    </div>
                    <div class="vp-actions">
                        <a href="{{ route('home') }}" class="btn secondary">Cancel</a>
                        <button class="btn secondary" type="reset" id="clear-btn">Clear</button>
                        <button class="btn" type="submit">Publish Post</button>
                    </div>
                </form>
            </section>
        </main>
    </div>

<script>
    const createContent = document.getElementById('post-content');
    const createCount = document.getElementById('create-count');
    const createMedia = document.getElementById('post-media');
    const createPreview = document.getElementById('create-media-preview');
    const createForm = document.getElementById('create-form');
    let draftUrls = [];

    // Character count
    createContent.addEventListener('input', () => {
        createCount.textContent = createContent.value.length + ' / 1200';
    });

    // Build the swipable carousel for the preview
    function drawMedia(container, mediaItems) {
        container.replaceChildren();
        if (!mediaItems.length) { container.hidden = true; return; }

        const carousel = document.createElement('div');
        carousel.className = 'vp-carousel';
        const track = document.createElement('div');
        track.className = 'vp-carousel-track';
        
        mediaItems.forEach((item) => {
            const slide = document.createElement('div');
            slide.className = 'vp-carousel-slide';
            const isVideo = item.type.startsWith('video/');
            const element = document.createElement(isVideo ? 'video' : 'img');
            element.src = item.url;
            if (isVideo) { element.controls = true; element.playsInline = true; }
            slide.appendChild(element);
            track.appendChild(slide);
        });
        carousel.appendChild(track);

        if (mediaItems.length > 1) {
            const count = document.createElement('span');
            count.className = 'vp-carousel-count';
            count.textContent = '1 / ' + mediaItems.length;
            carousel.appendChild(count);

            const previous = document.createElement('button');
            previous.type = 'button';
            previous.className = 'vp-carousel-nav vp-carousel-prev';
            previous.innerHTML = '‹';
            
            const next = document.createElement('button');
            next.type = 'button';
            next.className = 'vp-carousel-nav vp-carousel-next';
            next.innerHTML = '›';

            const dots = document.createElement('div');
            dots.className = 'vp-carousel-dots';
            let currentIndex = 0;
            
            function updatePosition(index) {
                currentIndex = Math.max(0, Math.min(mediaItems.length - 1, index));
                count.textContent = (currentIndex + 1) + ' / ' + mediaItems.length;
                dotButtons.forEach((dot, i) => dot.setAttribute('aria-current', String(i === currentIndex)));
            }
            function goToSlide(index) {
                updatePosition(index);
                track.scrollTo({ left: currentIndex * track.clientWidth, behavior: 'smooth' });
            }
            const dotButtons = mediaItems.map((_, index) => {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.className = 'vp-carousel-dot';
                dot.setAttribute('aria-current', String(index === 0));
                dot.addEventListener('click', () => goToSlide(index));
                dots.appendChild(dot);
                return dot;
            });
            
            previous.addEventListener('click', () => goToSlide(currentIndex - 1));
            next.addEventListener('click', () => goToSlide(currentIndex + 1));
            track.addEventListener('scroll', () => {
                updatePosition(Math.round(track.scrollLeft / track.clientWidth));
            }, { passive: true });
            
            carousel.append(previous, next, dots);
        }
        container.appendChild(carousel);
        container.hidden = false;
    }

    // Handle file selection
    createMedia.addEventListener('change', () => {
        draftUrls.forEach(url => URL.revokeObjectURL(url));
        draftUrls = [];
        
        const files = Array.from(createMedia.files);
        if (!files.length) { createPreview.hidden = true; return; }
        
        const items = files.map(file => {
            const url = URL.createObjectURL(file);
            draftUrls.push(url);
            return { url: url, type: file.type };
        });
        
        drawMedia(createPreview, items);
    });

    // Clear preview on form reset
    createForm.addEventListener('reset', () => {
        draftUrls.forEach(url => URL.revokeObjectURL(url));
        draftUrls = [];
        createPreview.hidden = true;
        createCount.textContent = '0 / 1200';
    });
</script>
@endsection