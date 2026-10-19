@extends('layouts.app')

@section('content')
<style>
    /* Original UI Base */
    .post-card { padding: 20px; margin-bottom: 15px; background: var(--surface); border: 1px solid var(--line); border-radius: 8px; }
    .post-header { display: flex; align-items: flex-start; gap: 10px; }
    .post-author { min-width: 0; flex: 1; }
    .post-author strong { display: block; font-size: 14px; }
    .post-author small { color: var(--muted); font-size: 12px; }
    .post-category { display: inline-block; padding: 4px 10px; border-radius: 20px; background: var(--brand-soft); color: var(--brand); font-size: 11px; font-weight: 600; margin-right: 10px; }
    .post-text { margin: 14px 0; white-space: pre-wrap; overflow-wrap: anywhere; }
    .post-visual { margin-top: 12px; border-radius: 8px; overflow: hidden; background: #10141b; }
    .post-counts { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--line-soft); font-size: 12px; color: var(--muted); }
    .post-actions { display: flex; justify-content: space-around; padding-top: 10px; }
    .post-action { background: transparent; border: 0; color: var(--ink); font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; }
    .post-action:hover { color: var(--brand); }
    .avatar-img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid var(--line); }
    .hashtag-link { color: var(--brand); font-weight: bold; text-decoration: none; cursor: pointer; display: inline-block; margin-right: 5px; }
    .hashtag-link:hover { text-decoration: underline; }

    /* 3-Dot Menu */
    .post-report-menu-wrap { position: relative; align-self: flex-start; margin-top: -4px; margin-left: auto; flex-shrink: 0; }
    .post-report-menu-button { display: grid; place-items: center; width: 36px; height: 36px; padding: 0; color: var(--ink); background: transparent; border: 0; border-radius: 7px; font-size: var(--text-2xl); line-height: 1; cursor: pointer; }
    .post-report-menu-button:hover, .post-report-menu-button[aria-expanded="true"] { background: var(--brand-soft); }
    .post-report-menu { position: absolute; top: 40px; right: 0; min-width: 160px; z-index: 12; padding: 5px; background: var(--surface); border: 1px solid var(--line); border-radius: 8px; box-shadow: 0 8px 24px rgb(0 0 0 / 12%); }
    .post-report-menu button { display: block; width: 100%; padding: 10px 12px; color: var(--ink); background: transparent; border: 0; border-radius: 5px; text-align: left; font-size: var(--text-md); cursor: pointer; }
    .post-report-menu button:hover { background: var(--brand-soft); }
    .post-report-menu .delete-btn { color: var(--red); }

    /* Modal / Dialog Styling */
    .rp-dialog { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: min(530px, calc(100% - 28px)); max-height: 90vh; margin: 0; overflow-y: auto; padding: 24px; color: var(--ink); background: var(--surface); border: 1px solid var(--line); border-radius: 12px; box-shadow: 0 16px 45px rgb(0 0 0 / 20%); z-index: 100; }
    .rp-dialog::backdrop { background: rgb(10 16 40 / 60%); }
    .rp-dialog-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; margin-bottom: 18px; }
    .rp-dialog-heading h2 { margin-bottom: 3px; font-size: var(--text-xl); }
    .rp-close { border: 0; background: transparent; color: var(--muted); font-size: var(--text-2xl); line-height: 1; padding: 2px 6px; cursor: pointer; }
    .rp-field-group { display: flex; flex-direction: column; gap: 7px; margin-bottom: 16px; }
    .rp-field-group label { font-size: var(--text-md); font-weight: 700; }
    .rp-field-group .field { display: block; width: 100%; min-height: 44px; margin: 0; }
    .rp-field-group textarea.field { min-height: 110px; resize: vertical; }
    .rp-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 9px; margin-top: 16px; }

    /* Carousel */
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
    
    .category-link { display: flex; justify-content: space-between; padding: 8px 0; color: var(--ink); text-decoration: none; border-bottom: 1px solid var(--line-soft); cursor: pointer; }
    .category-link:hover { color: var(--brand); }
    .category-link strong { background: var(--line-soft); padding: 2px 8px; border-radius: 10px; font-size: 11px; }
    
    /* Ensure custom dropdowns are visible */
    .feed-actions .filter-group { display: flex !important; align-items: center !important; gap: 10px !important; visibility: visible !important; opacity: 1 !important; }
    .feed-actions select.field { display: block !important; min-height: 38px; padding: 0 12px; border-radius: 6px; border: 1px solid var(--line); background: var(--surface); color: var(--ink); font-size: 13px; }
    
    [hidden] { display: none !important; }
</style>

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
                    <a href="#posts">Posts</a>
                    <a href="#schedules">Schedules</a>
                    <a href="#bookmarks">Saved posts</a>
                    <a href="#help">Help and support</a>
                </nav>
            </details>
        </div>
    </header>

    <div class="app-layout">
        <aside class="sidebar">
            <section class="panel account-card">
                @if ($photoDataUri = auth()->user()->photoDataUri())
                    <img class="avatar avatar-large avatar-photo" src="{{ $photoDataUri }}" alt="{{ auth()->user()->username }} profile photo">
                @else
                    <span class="avatar avatar-large" aria-label="Member profile">{{ auth()->user()->initials() }}</span>
                @endif
                <div class="account-copy">
                    <strong>{{ auth()->user()->username }}</strong>
                </div>
                <div class="account-tools">
                    <span class="account-icon bell-icon" title="Notifications" aria-label="Notifications"></span>
                    <details class="account-settings">
                        <summary class="account-icon" title="Settings" aria-label="Open settings">⚙</summary>
                        <nav aria-label="Account settings">
                            <a href="{{ route('profile.edit') }}">Profile settings</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="account-menu-button" type="submit">Sign out</button>
                            </form>
                        </nav>
                    </details>
                </div>
            </section>
        </aside>

        <main class="main-column" id="home">
            @unless ($showProfileSettings ?? false)
            <div class="feed-toolbar">
                <form class="search-box" id="search-form" onsubmit="event.preventDefault();">
                    <span aria-hidden="true">⌕</span>
                    <input type="search" id="search-input" placeholder="Search posts and hashtags" aria-label="Search posts and hashtags">
                    <button class="search-submit" type="submit" aria-label="Submit search">↵</button>
                </form>
            </div>

            <div class="feed-actions" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 20px;">
                <div class="feed-action-buttons">
                    <a class="btn" href="{{ route('posts.create') }}">Create post</a>
                    <a class="btn secondary" href="{{ route('schedule-post') }}">Schedule post</a>
                </div>
                
                <!-- Category Filter Dropdown -->
                <div class="filter-group">
                    <label for="post-category-filter" style="font-size: 12px; color: var(--muted); margin: 0;">Filter Category:</label>
                    <select class="field" id="post-category-filter" style="margin: 0;">
                        <option value="all">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->category_type }}">{{ $category->category_type }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endunless

            @if ($showProfileSettings ?? false)
                @include('profile_forms.settings', ['user' => auth()->user()])
            @elseif ($hidePosts ?? false)
                @include('post_forms.form', ['isScheduling' => $isScheduling ?? false])
            @else
                <section class="feed-list" id="feed-container" aria-label="Latest posts"></section>
            @endif

        </main>

        <aside class="rightbar">
            <section class="panel right-panel">
                <h3>Explore</h3>
                <p class="subtext">Browse posts by hashtag.</p>
                <div class="category-list" id="explore-tags">
                    <a class="category-link" data-tag="#laravel"><span>#laravel</span><strong>12</strong></a>
                    <a class="category-link" data-tag="#postgresql"><span>#postgresql</span><strong>8</strong></a>
                    <a class="category-link" data-tag="#webdevelopment"><span>#webdevelopment</span><strong>5</strong></a>
                </div>
            </section>
        </aside>
    </div>

    <!-- EDIT POST DIALOG -->
    <dialog class="rp-dialog" id="edit-dialog">
        <div class="rp-dialog-heading">
            <div><h2>Edit Post</h2></div>
            <button type="button" class="rp-close" onclick="document.getElementById('edit-dialog').close()">&times;</button>
        </div>
        <form id="edit-form" enctype="multipart/form-data">
            <input type="hidden" id="edit-id">
            <div class="rp-field-group">
                <label>Post Description</label>
                <textarea class="field" id="edit-content" maxlength="1200" required></textarea>
            </div>
            <div class="rp-field-group">
                <label>Replace Photos or Videos (Optional)</label>
                <input class="field" type="file" id="edit-media" accept="image/jpeg,image/png,image/webp,video/mp4,video/webm" multiple>
                <span style="font-size: 11px; color: var(--muted);">Selecting new files will replace the current media.</span>
            </div>
            <div class="rp-actions">
                <button type="button" class="btn secondary" onclick="document.getElementById('edit-dialog').close()">Cancel</button>
                <button type="submit" class="btn">Save Changes</button>
            </div>
        </form>
    </dialog>

    <!-- REPORT POST DIALOG -->
    <dialog class="rp-dialog" id="report-dialog">
        <div class="rp-dialog-heading">
            <div>
                <h2>Report Post</h2>
                <p class="subtext">Tell us why you're reporting this post.</p>
            </div>
            <button type="button" class="rp-close" onclick="document.getElementById('report-dialog').close()">&times;</button>
        </div>
        <form action="{{ route('report.store') }}" method="POST">
            @csrf
            <input type="hidden" name="post_id" id="report-post-id" value="">
            <div class="rp-field-group">
                <label for="post-report-reason">Report Reason <span class="rp-required" style="color:var(--red)">*</span></label>
                <select class="field" name="reason" required>
                    <option value="">Select a reason</option>
                    <option value="Problem involving someone under 18">Problem involving someone under 18</option>
                    <option value="Bullying, harassment or abuse">Bullying, harassment or abuse</option>
                    <option value="Suicide or self-harm">Suicide or self-harm</option>
                    <option value="Violent, hateful or disturbing content">Violent, hateful or disturbing content</option>
                    <option value="Adult content">Adult content</option>
                    <option value="Scam, fraud or false information">Scam, fraud or false information</option>
                    <option value="I don't want to see this">I don't want to see this</option>
                </select>
            </div>
            <div class="rp-field-group">
                <label for="post-report-details">Additional Details (Optional)</label>
                <textarea class="field" name="details" maxlength="500" placeholder="Describe your concern..."></textarea>
            </div>
            <div class="rp-actions">
                <button type="button" class="btn secondary" onclick="document.getElementById('report-dialog').close()">Cancel</button>
                <button type="submit" class="btn">Submit Report</button>
            </div>
        </form>
    </dialog>

    <script>
        const allPosts = @json($serverPosts ?? []);
        const currentUserName = @json(auth()->user()->username);
        const currentUserInitials = @json(auth()->user()->initials());
        const feedContainer = document.getElementById('feed-container');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        let currentSearch = '';
        let currentCategory = 'all';

        document.getElementById('search-input').addEventListener('input', (e) => {
            currentSearch = e.target.value.toLowerCase();
            renderFilteredPosts();
        });

        document.getElementById('post-category-filter').addEventListener('change', (e) => {
            currentCategory = e.target.value;
            renderFilteredPosts();
        });

        document.querySelectorAll('.category-link').forEach(link => {
            link.addEventListener('click', () => {
                const tag = link.getAttribute('data-tag');
                document.getElementById('search-input').value = tag;
                currentSearch = tag.toLowerCase();
                document.getElementById('post-category-filter').value = 'all'; 
                currentCategory = 'all';
                renderFilteredPosts();
            });
        });

        function clickTag(tag) {
            document.getElementById('search-input').value = tag;
            currentSearch = tag.toLowerCase();
            document.getElementById('post-category-filter').value = 'all';
            currentCategory = 'all';
            renderFilteredPosts();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function timeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const seconds = Math.floor((now - date) / 1000);
            if (seconds < 60) return 'Just now';
            const minutes = Math.floor(seconds / 60);
            if (minutes < 60) return minutes + 'm ago';
            const hours = Math.floor(minutes / 60);
            if (hours < 24) return hours + 'h ago';
            const days = Math.floor(hours / 24);
            return days + 'd ago';
        }

        function renderFilteredPosts() {
            let filtered = [...allPosts];

            if (currentSearch) {
                filtered = filtered.filter(post => {
                    const text = post.content.toLowerCase();
                    const tags = (post.hashtags || '').toLowerCase();
                    const cat = (post.category || '').toLowerCase();
                    return text.includes(currentSearch) || tags.includes(currentSearch) || cat.includes(currentSearch);
                });
            }

            if (currentCategory !== 'all') {
                filtered = filtered.filter(post => post.category === currentCategory);
            }

            filtered.sort((a, b) => new Date(b.time) - new Date(a.time));

            feedContainer.innerHTML = '';
            if (filtered.length === 0) {
                feedContainer.innerHTML = '<p class="subtext" style="padding:20px;">No posts found matching your criteria.</p>';
                return;
            }
            filtered.forEach(post => feedContainer.appendChild(buildPostCard(post)));
        }

        function drawMedia(container, mediaItems) {
            container.replaceChildren();
            if (!mediaItems || !mediaItems.length) { container.hidden = true; return; }
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
                const count = document.createElement('span'); count.className = 'vp-carousel-count'; count.textContent = '1 / ' + mediaItems.length;
                carousel.appendChild(count);
                const prev = document.createElement('button'); prev.type = 'button'; prev.className = 'vp-carousel-nav vp-carousel-prev'; prev.innerHTML = '‹';
                const next = document.createElement('button'); next.type = 'button'; next.className = 'vp-carousel-nav vp-carousel-next'; next.innerHTML = '›';
                const dots = document.createElement('div'); dots.className = 'vp-carousel-dots';
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
                    const dot = document.createElement('button'); dot.type = 'button'; dot.className = 'vp-carousel-dot';
                    dot.setAttribute('aria-current', String(index === 0));
                    dot.addEventListener('click', () => goToSlide(index));
                    dots.appendChild(dot); return dot;
                });
                prev.addEventListener('click', () => goToSlide(currentIndex - 1));
                next.addEventListener('click', () => goToSlide(currentIndex + 1));
                track.addEventListener('scroll', () => updatePosition(Math.round(track.scrollLeft / track.clientWidth)), { passive: true });
                carousel.append(prev, next, dots);
            }
            container.appendChild(carousel);
            container.hidden = false;
        }

        function buildPostCard(post) {
            const card = document.createElement('article');
            card.className = 'panel post-card';
            
            const header = document.createElement('div');
            header.className = 'post-header';
            
            const avatarHtml = post.authorAvatar 
                ? `<img src="${post.authorAvatar}" class="avatar-img" alt="Avatar">` 
                : `<span class="avatar">${post.authorInitials || currentUserInitials}</span>`;

            let menuButtons = post.is_owner 
                ? `<button type="button" class="edit-btn">Edit</button><button type="button" class="delete-btn">Delete</button>`
                : `<button type="button" class="report-btn">Report Post</button>`;

            header.innerHTML = `
                ${avatarHtml}
                <div class="post-author">
                    <strong>${post.author || currentUserName}</strong>
                    <small>${timeAgo(post.time)} · ${post.post_type}</small>
                </div>
                <span class="post-category">${post.category}</span>
                <div class="post-report-menu-wrap">
                    <button type="button" class="post-report-menu-button">⋯</button>
                    <div class="post-report-menu" hidden>${menuButtons}</div>
                </div>
            `;

            const menuBtn = header.querySelector('.post-report-menu-button');
            const menu = header.querySelector('.post-report-menu');
            menuBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const isHidden = menu.hidden;
                document.querySelectorAll('.post-report-menu').forEach(m => m.hidden = true);
                menu.hidden = !isHidden;
            });

            const deleteBtn = header.querySelector('.delete-btn');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', () => {
                    if(!confirm('Are you sure you want to delete this post?')) return;
                    fetch(`/posts/${post.id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                    }).then(res => {
                        if(res.ok) {
                            const index = allPosts.findIndex(p => p.id === post.id);
                            if (index > -1) allPosts.splice(index, 1);
                            renderFilteredPosts();
                        } else {
                            alert('Failed to delete post.');
                        }
                    });
                });
            }

            const editBtn = header.querySelector('.edit-btn');
            if (editBtn) {
                editBtn.addEventListener('click', () => {
                    document.getElementById('edit-id').value = post.id;
                    document.getElementById('edit-content').value = post.content;
                    document.getElementById('edit-media').value = '';
                    document.getElementById('edit-dialog').showModal();
                });
            }

            const reportBtn = header.querySelector('.report-btn');
            if (reportBtn) {
                reportBtn.addEventListener('click', () => {
                    document.getElementById('report-post-id').value = post.id;
                    document.getElementById('report-dialog').showModal();
                });
            }

            const text = document.createElement('p');
            text.className = 'post-text';
            text.textContent = post.content;

            let hashtags = null;
            if(post.hashtags) {
                hashtags = document.createElement('p');
                const tagsArray = post.hashtags.split(' ');
                tagsArray.forEach(tag => {
                    if(!tag) return;
                    const a = document.createElement('a');
                    a.className = 'hashtag-link';
                    a.textContent = tag + ' ';
                    a.onclick = () => clickTag(tag);
                    hashtags.appendChild(a);
                });
            }

            let mediaContainer = null;
            if (post.mediaItems && post.mediaItems.length > 0) {
                mediaContainer = document.createElement('div');
                mediaContainer.className = 'post-visual';
                drawMedia(mediaContainer, post.mediaItems);
            }

            const counts = document.createElement('div');
            counts.className = 'post-counts';
            counts.innerHTML = `<span>0 reactions</span><span>0 comments · 0 views</span>`;

            const actions = document.createElement('div');
            actions.className = 'post-actions';
            actions.innerHTML = `<button class="post-action">♡ React</button><button class="post-action">Comment</button>`;

            card.appendChild(header);
            card.appendChild(text);
            if (hashtags) card.appendChild(hashtags);
            if (mediaContainer) card.appendChild(mediaContainer);
            card.appendChild(counts);
            card.appendChild(actions);
            
            return card;
        }

        document.getElementById('edit-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const id = document.getElementById('edit-id').value;
            const content = document.getElementById('edit-content').value;
            const mediaInput = document.getElementById('edit-media');

            const formData = new FormData();
            formData.append('_method', 'PUT'); 
            formData.append('content', content);
            
            if (mediaInput.files.length > 0) {
                Array.from(mediaInput.files).forEach(file => {
                    formData.append('media[]', file);
                });
            }

            fetch(`/posts/${id}`, {
                method: 'POST', 
                headers: { 
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData 
            }).then(res => {
                if(res.ok) {
                    document.getElementById('edit-dialog').close();
                    setTimeout(() => window.location.reload(), 200);
                } else {
                    alert('Failed to edit post.');
                }
            });
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.post-report-menu-wrap')) {
                document.querySelectorAll('.post-report-menu').forEach(m => m.hidden = true);
            }
        });

        renderFilteredPosts();
    </script>
@endsection