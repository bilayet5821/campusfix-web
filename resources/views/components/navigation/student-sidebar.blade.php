@props(['student' => null, 'mobile' => false])

@php
    $preview = request()->routeIs('preview.*');
    $authUser = !$preview && auth()->check() ? auth()->user() : null;

    $canStudentPortal = $preview || ($authUser?->hasPermission('view_student_portal') ?? false);
    $canCreateIssues = $preview || ($authUser?->hasPermission('create_issues') ?? false);
    $canAdminDashboard = !$preview && ($authUser?->hasPermission('view_admin_dashboard') ?? false);
    $canManageIssues = !$preview && ($authUser?->hasPermission('manage_issues') ?? false);
    $canManageLocations = !$preview && ($authUser?->hasPermission('manage_locations') ?? false);
    $canManageUsers = !$preview && ($authUser?->hasPermission('manage_users') ?? false);

    $dashboardUrl = $preview ? route('preview.student.dashboard') : (Route::has('student.dashboard') ? route('student.dashboard') : '#');
    $createUrl = $preview ? route('preview.student.issues.create') : (Route::has('student.issues.create') ? route('student.issues.create') : '#');
    $issuesUrl = $preview ? route('preview.student.issues.index') : (Route::has('student.issues.index') ? route('student.issues.index') : '#');
    $isTeacher = data_get($student, 'role') === 'teacher';
    $name = data_get($student, 'name', $isTeacher ? 'Teacher User' : 'Student User');
    $studentId = data_get($student, 'student_id') ?: ($isTeacher ? 'Teacher account' : data_get($student, 'email', 'Student account'));
    $initials = collect(explode(' ', $name))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->join('');
@endphp

<div class="d-flex flex-column h-100">
    @unless ($mobile)<x-brand light />@endunless
    <p class="student-sidebar-label">{{ $isTeacher ? 'Teacher workspace' : 'Student workspace' }}</p>
    <nav class="student-nav" aria-label="Student navigation">
        @if ($canStudentPortal)
            <a data-sidebar-link class="student-nav-link {{ request()->routeIs('student.dashboard', 'preview.student.dashboard') ? 'active' : '' }}" href="{{ $dashboardUrl }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 13h6V4H4zM14 20h6V11h-6zM4 20h6v-3H4zM14 7h6V4h-6z"/></svg>
                Dashboard
            </a>
        @endif

        @if ($canCreateIssues)
            <a data-sidebar-link class="student-nav-link {{ request()->routeIs('student.issues.create', 'preview.student.issues.create') ? 'active' : '' }}" href="{{ $createUrl }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Report Issue
            </a>
        @endif

        @if ($canStudentPortal)
            <a data-sidebar-link class="student-nav-link {{ request()->routeIs('student.issues.index', 'student.issues.show', 'preview.student.issues.index', 'preview.student.issues.show') ? 'active' : '' }}" href="{{ $issuesUrl }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 4h14v16H5zM8 9h8M8 13h8M8 17h5"/></svg>
                My Reports
            </a>
        @endif

        @if ($canAdminDashboard || $canManageIssues || $canManageLocations || $canManageUsers)
            <p class="student-sidebar-label mt-3 mb-1">Granted admin features</p>
            @if ($canAdminDashboard && Route::has('admin.dashboard'))
                <a data-sidebar-link class="student-nav-link" href="{{ route('admin.dashboard') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 13h6V4H4zM14 20h6V11h-6zM4 20h6v-3H4zM14 7h6V4h-6z"/></svg>
                    Admin Dashboard
                </a>
            @endif
            @if ($canManageIssues && Route::has('admin.issues.index'))
                <a data-sidebar-link class="student-nav-link" href="{{ route('admin.issues.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 4h14v16H5zM8 9h8M8 13h8M8 17h5"/></svg>
                    Issue Management
                </a>
            @endif
            @if ($canManageLocations && Route::has('admin.locations.index'))
                <a data-sidebar-link class="student-nav-link" href="{{ route('admin.locations.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/></svg>
                    Buildings &amp; Rooms
                </a>
            @endif
            @if ($canManageUsers && Route::has('admin.users.index'))
                <a data-sidebar-link class="student-nav-link" href="{{ route('admin.users.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    Users &amp; Access
                </a>
            @endif
        @endif
    </nav>

    <div class="sidebar-profile">
        <span class="profile-avatar">{{ $initials ?: 'SU' }}</span>
        <span class="min-w-0">
            <strong class="text-truncate">{{ $name }}</strong>
            <small class="text-truncate">{{ $studentId }}</small>
        </span>
    </div>

    @if (Route::has('logout') && !$preview)
        <form action="{{ route('logout') }}" method="POST" class="mt-2 px-2">@csrf<button class="student-nav-link border-0 bg-transparent w-100" type="submit">Log out</button></form>
    @else
        <span class="student-nav-link mt-2 opacity-50" aria-disabled="true" title="Logout becomes available with authentication">Log out <span class="soon-badge">Soon</span></span>
    @endif
</div>
