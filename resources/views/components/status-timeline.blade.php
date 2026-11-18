@props(['history'])

<ol {{ $attributes->class(['timeline']) }}>
    @forelse (collect($history ?? []) as $entry)
        @php $entryDate = data_get($entry, 'created_at'); @endphp
        <li class="timeline-item">
            <span class="timeline-marker" aria-hidden="true"></span>
            <div class="timeline-content">
                <strong>{{ data_get($entry, 'title', str(data_get($entry, 'status', 'Update'))->replace('_', ' ')->title()) }}</strong>
                @if (data_get($entry, 'note'))<p>{{ data_get($entry, 'note') }}</p>@endif
                @if (data_get($entry, 'actor.name'))<p class="mb-0">Updated by {{ data_get($entry, 'actor.name') }}</p>@endif
                <time>{{ $entryDate instanceof \DateTimeInterface ? $entryDate->format('M j, Y \a\t g:i A') : $entryDate }}</time>
            </div>
        </li>
    @empty
        <li class="text-muted-cf small">No status history is available.</li>
    @endforelse
</ol>
