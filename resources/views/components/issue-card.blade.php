@props(['issue', 'viewUrl' => null])

@php
    $createdAt = data_get($issue, 'created_at');
    $date = $createdAt instanceof \DateTimeInterface ? $createdAt->format('M j, Y') : ($createdAt ?: '—');
@endphp

<article {{ $attributes->class(['issue-card']) }}>
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div class="min-w-0">
            <div class="issue-reference">{{ data_get($issue, 'reference_number', 'Reference pending') }}</div>
            <h3 class="issue-title">{{ data_get($issue, 'title', 'Untitled report') }}</h3>
            <div class="issue-meta">
                <span>{{ data_get($issue, 'category.name', data_get($issue, 'category', 'Uncategorized')) }}</span>
                <span>{{ data_get($issue, 'location.name', data_get($issue, 'location', 'Location pending')) }}</span>
                <span>{{ $date }}</span>
            </div>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <x-status-badge :status="data_get($issue, 'status')" />
            <x-priority-badge :priority="data_get($issue, 'priority')" />
            @if ($viewUrl)
                <a class="btn btn-sm btn-outline-primary ms-md-1" href="{{ $viewUrl }}">View Details</a>
            @endif
        </div>
    </div>
</article>
