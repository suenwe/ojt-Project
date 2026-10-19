<section class="panel member-profile" aria-labelledby="member-profile-name">
    @if ($photoDataUri = $member->photoDataUri())
        <img class="avatar member-profile-avatar avatar-photo" src="{{ $photoDataUri }}" alt="{{ $member->username }} profile photo">
    @else
        <span class="avatar member-profile-avatar" aria-hidden="true">{{ $member->initials() }}</span>
    @endif

    <div class="member-profile-copy">
        <h1 id="member-profile-name">{{ $member->username }}</h1>
        <p class="subtext">
            Joined {{ $member->created_date?->format('F Y') ?? 'recently' }}
            · {{ $memberPostCount }} {{ \Illuminate\Support\Str::plural('post', $memberPostCount) }}
            @if ($member->role === 'admin') · Admin @endif
        </p>
        @if ($member->isBanned())
            <p class="member-profile-banned">This account is suspended.</p>
        @endif
    </div>

    @if ($isOwnProfile)
        <a class="btn secondary" href="{{ route('profile.edit') }}">Edit profile</a>
    @endif
</section>

<section class="feed-list" aria-label="Posts by {{ $member->username }}">
    @forelse ($posts as $post)
        @include('post_forms.card', ['post' => $post])
    @empty
        <div class="panel empty-feed">
            <h2>No posts yet</h2>
            <p class="subtext">
                {{ $isOwnProfile ? 'Your posts will show up here.' : $member->username.' has not posted anything yet.' }}
            </p>
            @if ($isOwnProfile)
                <a class="btn" href="{{ route('posts.create') }}">Create post</a>
            @endif
        </div>
    @endforelse
</section>
