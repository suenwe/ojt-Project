{{-- A member's round photo (or initials) linking to their profile. Pass $user and optionally $sizeClass. --}}
@if ($user)
    <a class="avatar {{ $sizeClass ?? '' }}" href="{{ route('users.show', $user) }}" aria-hidden="true" tabindex="-1">
        @if ($user->photoFile())
            <img class="avatar-photo-fill" src="{{ route('users.photo', $user) }}" alt="" loading="lazy">
        @else
            {{ $user->initials() }}
        @endif
    </a>
@else
    <span class="avatar {{ $sizeClass ?? '' }}" aria-hidden="true">?</span>
@endif
