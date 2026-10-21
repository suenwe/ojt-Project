{{-- A member's round photo (or initials). Pass $user, optionally $sizeClass, and 'linked' => false when it sits inside another link. --}}
@php($isLinked = ($linked ?? true) && $user)
@if ($isLinked)
<a class="avatar {{ $sizeClass ?? '' }}" href="{{ route('users.show', $user) }}" aria-hidden="true" tabindex="-1">
@else
<span class="avatar {{ $sizeClass ?? '' }}" aria-hidden="true">
@endif
    @if ($user?->photoFile())
        <img class="avatar-photo-fill" src="{{ route('users.photo', $user) }}" alt="" loading="lazy">
    @elseif ($user)
        {{ $user->initials() }}
    @else
        ?
    @endif
@if ($isLinked)
</a>
@else
</span>
@endif
