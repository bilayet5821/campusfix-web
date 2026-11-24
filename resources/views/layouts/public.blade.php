<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', 'CampusFix helps students report campus issues, follow progress, and stay informed from submission to resolution.')">
    <meta name="theme-color" content="#07182f">

    <title>@yield('title', 'CampusFix') — Smart Campus Issue Management</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
    <a class="skip-link" href="#main-content">Skip to main content</a>

    <x-navigation.public-navbar />

    <main id="main-content">
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-6">
                    <x-brand light />
                    <p class="footer-copy mt-3 mb-0">
                        A clear, student-friendly way to report campus concerns and follow every update from submission to resolution.
                    </p>
                </div>
                <div class="col-6 col-lg-3">
                    <h2 class="footer-heading">Explore</h2>
                    <ul class="footer-links">
                        <li><a href="#features">Features</a></li>
                        <li><a href="#how-it-works">How it works</a></li>
                        <li><a href="#categories">Issue categories</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-3">
                    <h2 class="footer-heading">CampusFix</h2>
                    <ul class="footer-links">
                        <li><a href="#about">About the platform</a></li>
                        <li><a href="#get-started">Student access</a></li>
                        <li><a href="#home">Back to top</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between gap-2">
                <span>&copy; {{ date('Y') }} CampusFix. All rights reserved.</span>
                <span>Designed for clearer campus communication.</span>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
