@props(['title', 'description', 'actionUrl' => null, 'actionLabel' => null])

<div {{ $attributes->class(['empty-state']) }}>
    <span class="empty-state-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 4h14v16H5z M8 9h8 M8 13h5"/></svg></span>
    <h2>{{ $title }}</h2>
    <p>{{ $description }}</p>
    @if ($actionUrl && $actionLabel)
        <a class="btn btn-primary" href="{{ $actionUrl }}">{{ $actionLabel }}</a>
    @endif
</div>
