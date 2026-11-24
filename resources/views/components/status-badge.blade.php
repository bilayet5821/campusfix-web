@props(['status'])

@php
    $value = $status ?: 'submitted';
    $labels = [
        'submitted' => 'Submitted',
        'under_review' => 'Under Review',
        'in_progress' => 'In Progress',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
        'rejected' => 'Rejected',
    ];
    $class = 'status-'.str_replace('_', '-', $value);
@endphp

<span {{ $attributes->class(['status-badge', $class, 'status-default' => !isset($labels[$value])]) }}>{{ $labels[$value] ?? str($value)->replace('_', ' ')->title() }}</span>
