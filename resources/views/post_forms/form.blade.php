@php($editingPost = $editingPost ?? null)
@php($isRescheduling = $editingPost?->isUpcomingScheduled() ?? false)
<section class="panel composer">
    <h2 class="composer-title">{{ $isRescheduling ? 'Edit Scheduled Post' : ($editingPost ? 'Edit Post' : ($isScheduling ? 'Schedule a Post' : 'Create a Post')) }}</h2>
    <p class="subtext">
        {{ $isRescheduling ? 'Change your post or move it to a different time.' : ($editingPost ? 'Change your post and save it.' : ($isScheduling ? 'Choose when your post should be published.' : "What's on your mind today?")) }}
    </p>

    @if (session('status'))
        <p class="form-status" role="status">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <div class="form-errors" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form class="post-form" action="{{ $editingPost ? route('posts.update', $editingPost) : ($isScheduling ? route('schedule-post.store') : route('posts.store')) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if ($editingPost)
            @method('PUT')
        @endif

        <label for="post-content">Post description</label>
        <textarea
            class="field"
            id="post-content"
            name="content"
            maxlength="1200"
            placeholder="Share your thoughts, questions or experiences..."
            required
        >{{ old('content', $editingPost?->descriptions) }}</textarea>

        <div class="post-form-row">
            <label for="post-category">
                Category
                <select class="field" id="post-category" name="category_id" required>
                    <option value="">Select category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->category_id }}" @selected(old('category_id', $editingPost?->category_id) == $category->category_id)>{{ $category->category_type }}</option>
                    @endforeach
                </select>
            </label>

            <label for="post-type">
                Post type
                <select class="field" id="post-type" name="post_type" required>
                    @foreach (\App\Services\PostPublisher::POST_TYPES as $postType)
                        <option value="{{ $postType }}" @selected(old('post_type', $editingPost?->post_type ?? 'Discussion') === $postType)>{{ $postType }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        <label for="post-hashtags">Hashtags (optional)</label>
        <input
            class="field"
            id="post-hashtags"
            name="hashtag"
            type="text"
            maxlength="150"
            value="{{ old('hashtag', $editingPost?->hashtag) }}"
            placeholder="#learning #knowvia"
        >

        @if ($isScheduling || $isRescheduling)
            <label for="post-scheduled-at">Schedule date and time</label>
            <input
                class="field"
                id="post-scheduled-at"
                name="scheduled_at"
                type="datetime-local"
                value="{{ old('scheduled_at', $editingPost?->date_time?->format('Y-m-d\TH:i')) }}"
                min="{{ now()->addMinute()->format('Y-m-d\TH:i') }}"
                required
            >
        @endif

        <label for="post-media">{{ $editingPost ? 'Replace photos or videos (optional)' : 'Photos or videos (optional)' }}</label>
        <input
            class="field"
            id="post-media"
            data-media-preview="post-media-preview"
            name="media[]"
            type="file"
            multiple
            accept="image/jpeg,image/png,image/webp,video/mp4,video/webm"
        >

        <p class="subtext">
            Up to 10 files. Images up to 5 MB; videos up to 25 MB.
            @if ($editingPost)
                {{ $editingPost->media_count ? 'Choosing new files replaces the '.$editingPost->media_count.' current '.\Illuminate\Support\Str::plural('file', $editingPost->media_count).'; leave empty to keep them.' : '' }}
            @endif
        </p>
        <div class="media-preview" id="post-media-preview" data-current-media='@json($editingPost?->mediaPreviewItems() ?? [])' hidden></div>

        <div class="composer-footer">
            <div class="composer-tools account-tools">
                <a class="btn secondary" href="{{ route('home') }}" data-composer-cancel>Cancel</a>
                <button class="btn" type="submit">
                    {{ $editingPost ? 'Save Changes' : ($isScheduling ? 'Schedule Post' : 'Publish Post') }}
                </button>
            </div>
        </div>
    </form>
</section>

@if ($isScheduling)
    <section class="panel composer" aria-labelledby="scheduled-posts-title">
        <h2 class="composer-title" id="scheduled-posts-title">Your scheduled posts</h2>

        @forelse ($scheduledPosts as $scheduledPost)
            <div class="scheduled-post">
                <div class="scheduled-post-copy">
                    <strong>{{ $scheduledPost->date_time->format('M j, Y \a\t g:i A') }}</strong>
                    <p>{{ \Illuminate\Support\Str::limit($scheduledPost->descriptions, 120) }}</p>
                    <small class="subtext">
                        {{ $scheduledPost->category?->category_type ?? 'Uncategorized' }}
                        @if ($scheduledPost->media_count) · {{ $scheduledPost->media_count }} {{ \Illuminate\Support\Str::plural('file', $scheduledPost->media_count) }} @endif
                    </small>
                </div>
                <div class="scheduled-post-actions">
                    <a class="btn secondary small" href="{{ route('posts.edit', $scheduledPost) }}">Edit</a>
                    <form method="POST" action="{{ route('schedule-post.destroy', $scheduledPost) }}" onsubmit="return confirm('Cancel this scheduled post?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn secondary small" type="submit">Cancel</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="subtext">You have no upcoming scheduled posts.</p>
        @endforelse
    </section>
@endif
