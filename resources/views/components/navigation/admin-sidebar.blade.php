@props(['admin' => null, 'mobile' => false])

@php
    $preview = request()->routeIs('preview.*');
    $dashboardUrl = $preview ? route('preview.admin.dashboard') : (Route::has('admin.dashboard') ? route('admin.dashboard') : '#');
    $issuesUrl = $preview ? route('preview.admin.issues.index') : (Route::has('admin.issues.index') ? route('admin.issues.index') : '#');
    $name = data_get($admin, 'name', 'Administrator');
    $email = data_get($admin, 'email', 'Admin account');
    $initials = collect(explode(' ', $name))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->join('');
@endphp

<div class="d-flex flex-column h-100">
    @unless ($mobile)<x-brand light />@endunless
    <p class="student-sidebar-label">Administration</p>
    <nav class="student-nav" aria-label="Admin navigation">
        <a data-sidebar-link class="student-nav-link {{ request()->routeIs('admin.dashboard', 'preview.admin.dashboard') ? 'active' : '' }}" href="{{ $dashboardUrl }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 13h6V4H4zM14 20h6V11h-6zM4 20h6v-3H4zM14 7h6V4h-6z"/></svg>
            Dashboard
        </a>
        <a data-sidebar-link class="student-nav-link {{ request()->routeIs('admin.issues.*', 'preview.admin.issues.*') ? 'active' : '' }}" href="{{ $issuesUrl }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 4h14v16H5zM8 9h8M8 13h8M8 17h5"/></svg>
            Issue Management
        </a>
    </nav>

    <div class="sidebar-profile">
        <span class="profile-avatar">{{ $initials ?: 'AD' }}</span>
        <span class="min-w-0"><strong class="text-truncate">{{ $name }}</strong><small class="text-truncate">{{ $email }}</small></span>
    </div>

    @if (Route::has('logout') && !$preview)
        <form action="{{ route('logout') }}" method="POST" class="mt-2 px-2">@csrf<button class="student-nav-link border-0 bg-transparent w-100" type="submit">Log out</button></form>
    @else
        <span class="student-nav-link mt-2 opacity-50" aria-disabled="true">Log out <span class="soon-badge">Soon</span></span>
    @endif
</div>
