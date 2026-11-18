@props(['type' => 'info', 'title' => null, 'dismissible' => false])

@php
    $classes = ['success' => 'alert-success', 'danger' => 'alert-danger', 'warning' => 'alert-warning', 'info' => 'alert-primary'];
@endphp

<div {{ $attributes->class(['alert', $classes[$type] ?? 'alert-primary', 'alert-dismissible fade show' => $dismissible])->merge(['role' => 'alert']) }}>
    @if ($title)<strong class="d-block mb-1">{{ $title }}</strong>@endif
    {{ $slot }}
    @if ($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Dismiss"></button>
    @endif
</div>
