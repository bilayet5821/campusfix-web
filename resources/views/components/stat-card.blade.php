@props(['label', 'value' => 0, 'tone' => 'blue', 'icon' => 'reports'])

<article class="stat-card" data-tone="{{ $tone }}">
    <div class="stat-card-top">
        <div>
            <div class="stat-card-label">{{ $label }}</div>
            <div class="stat-card-value">{{ $value }}</div>
        </div>
        <span class="stat-card-icon" aria-hidden="true">
            @switch($icon)
                @case('clock') <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg> @break
                @case('progress') <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12a8 8 0 1 0 8-8"/><path d="M4 4v5h5"/></svg> @break
                @case('check') <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/></svg> @break
                @default <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 4h14v16H5z M8 8h8 M8 12h8 M8 16h5"/></svg>
            @endswitch
        </span>
    </div>
</article>
