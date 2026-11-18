@php
    $studentData = $student ?? (auth()->check() ? auth()->user() : null);
    $preview = request()->routeIs('preview.*');
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#07182f">
    @if ($preview)<meta name="robots" content="noindex, nofollow">@endif
    <title>@yield('title', 'Student Dashboard') — CampusFix</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="student-app">
    <a class="skip-link" href="#student-content">Skip to main content</a>

    <aside class="student-sidebar d-none d-lg-flex" aria-label="Student sidebar">
        <x-navigation.student-sidebar :student="$studentData" />
    </aside>

    <div class="offcanvas offcanvas-start student-mobile-nav" tabindex="-1" id="studentSidebarMobile" aria-labelledby="studentSidebarMobileLabel">
        <div class="offcanvas-header">
            <span class="visually-hidden" id="studentSidebarMobileLabel">Student navigation</span>
            <x-brand light />
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close navigation"></button>
        </div>
        <div class="offcanvas-body"><x-navigation.student-sidebar :student="$studentData" mobile /></div>
    </div>

    <div class="student-main">
        <header class="student-topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="mobile-menu-btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#studentSidebarMobile" aria-controls="studentSidebarMobile" aria-label="Open navigation">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>
                <span class="d-none d-sm-block small fw-semibold text-muted-cf">Student Portal</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if ($preview)<span class="badge text-bg-warning">Preview mode</span>@endif
                <span class="profile-avatar">{{ collect(explode(' ', data_get($studentData, 'name', 'Student')))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->join('') }}</span>
            </div>
        </header>

        <main class="student-content" id="student-content">
            <div class="container-fluid px-0">
                @hasSection('breadcrumbs')
                    <nav aria-label="Breadcrumb"><ol class="breadcrumb">@yield('breadcrumbs')</ol></nav>
                @endif
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
                    <div><h1 class="page-heading">@yield('page_heading')</h1><p class="text-muted-cf mb-0">@yield('page_description')</p></div>
                    <div class="page-actions">@yield('page_actions')</div>
                </div>

                @if (session('success'))<x-alert type="success" dismissible>{{ session('success') }}</x-alert>@endif
                @if (session('error'))<x-alert type="danger" dismissible>{{ session('error') }}</x-alert>@endif
                @if ($errors->any())
                    <x-alert type="danger" title="Please review the highlighted fields."><ul class="mb-0 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></x-alert>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
