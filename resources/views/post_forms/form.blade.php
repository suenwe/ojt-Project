<section class="panel composer">
    <h2 class="composer-title">{{ $isScheduling ? 'Schedule a Post' : 'Create a Post' }}</h2>
    <p class="subtext">
        {{ $isScheduling ? 'Choose when your post should be published.' : "What's on your mind today?" }}
    </p>

    <form action="#" method="POST" enctype="multipart/form-data">
        @csrf

        <label for="post-content">Post description</label>
        <textarea
            class="field"
            id="post-content"
            name="content"
            maxlength="1200"
            placeholder="Share your thoughts, questions or experiences..."
            required
        ></textarea>

        <div class="composer-footer">
            <div class="composer-tools">
                <label for="post-category">Category</label>
                <select class="field" id="post-category" name="category" required>
                    <option value="">Select category</option>
                    <option>Technology</option>
                    <option>Education</option>
                    <option>Gaming</option>
                    <option>Entertainment</option>
                    <option>Health &amp; Fitness</option>
                    <option>Business &amp; Career</option>
                    <option>Travel</option>
                    <option>Art &amp; Design</option>
                    <option>Fashion &amp; Lifestyle</option>
                    <option>Food &amp; Cooking</option>
                    <option>Sport</option>
                    <option>General Discussion</option>
                    <option>Events &amp; Activities</option>
                    <option>Books and Literature</option>
                    <option>News &amp; Updates</option>
                </select>
            </div>
        </div>

        <label for="post-hashtags">Hashtags (optional)</label>
        <input
            class="field"
            id="post-hashtags"
            name="hashtags"
            type="text"
            maxlength="150"
            placeholder="#learning #vibely"
        >

        @if($isScheduling)
            <label for="post-scheduled-at">Schedule date and time</label>
            <input
                class="field"
                id="post-scheduled-at"
                name="scheduled_at"
                type="datetime-local"
                required
            >
        @endif

        <label for="post-media">Photo or video (optional)</label>
        <input
            class="field"
            id="post-media"
            name="media"
            type="file"
            accept="image/jpeg,image/png,image/webp,video/mp4,video/webm"
        >

        <p class="subtext">Images up to 5 MB; videos up to 25 MB.</p>
        <div class="composer-footer">
            <div class="composer-tools account-tools">
                <a class="btn secondary" href="{{ url('/') }}">Cancel</a>
                <button class="btn" type="submit">
                    {{ $isScheduling ? 'Schedule Post' : 'Publish Post' }}
                </button>
            </div>
        </div>
    </form>
</section>
