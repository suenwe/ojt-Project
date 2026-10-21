@if ($reactionTabs->isNotEmpty())
    <div class="people-tabs" aria-label="Filter by reaction">
        <button type="button" class="people-tab" data-people-filter="all" aria-pressed="true">All {{ $people->count() }}</button>
        @foreach ($reactionTabs as $type => $count)
            <button type="button" class="people-tab" data-people-filter="{{ $type }}" aria-pressed="false" title="{{ \App\Models\Reaction::TYPES[$type]['label'] }}">{{ \App\Models\Reaction::TYPES[$type]['emoji'] }} {{ $count }}</button>
        @endforeach
    </div>
@endif

<ul class="people-list">
    @forelse ($people as $person)
        <li class="people-row" @if ($person['type']) data-people-type="{{ $person['type'] }}" @endif>
            @include('post_forms.avatar-link', ['user' => $person['user'], 'sizeClass' => 'avatar-small'])
            @if ($person['user'])
                <a class="people-name" href="{{ route('users.show', $person['user']) }}">{{ $person['user']->username }}</a>
            @else
                <span class="people-name">Deleted member</span>
            @endif
            <span class="people-detail" title="{{ $person['detailLabel'] }}" aria-label="{{ $person['detailLabel'] }}">{{ $person['detail'] }}</span>
        </li>
    @empty
        <li class="people-empty">{{ $emptyMessage }}</li>
    @endforelse
</ul>
