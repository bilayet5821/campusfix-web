<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#07182f">
    <title>@yield('title', 'Student Access') — CampusFix</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
    <a class="skip-link" href="#auth-content">Skip to form</a>
    <main class="auth-shell" id="auth-content">
        <aside class="auth-showcase" aria-label="About CampusFix">
            <div class="auth-showcase-inner">
                <x-brand light />
                <div class="my-auto py-5">
                    <p class="text-uppercase small fw-bold mb-3" style="color: #83e8dd; letter-spacing: .12em;">Student issue reporting</p>
                    <h1>Campus concerns, clearly connected to action.</h1>
                    <p class="fs-5 mt-4">Report what needs attention, follow progress, and receive resolution updates through one organized platform.</p>
                    <div class="d-grid gap-3 mt-5">
                        @foreach (['Structured, easy-to-follow reports', 'Visible progress and status updates', 'A clear record from submission to resolution'] as $benefit)
                            <div class="auth-benefit">
                                <span class="auth-benefit-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m20 6-11 11-5-5"/></svg></span>
                                <span>{{ $benefit }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <small style="color: #8295ad;">CampusFix — Smart campus issue management</small>
            </div>
        </aside>
        <section class="auth-form-pane">
            @yield('content')
        </section>
    </main>
</body>
</html>
