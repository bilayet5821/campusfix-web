@php
    $previewLogin = app()->environment('local') && Route::has('preview.login') ? route('preview.login') : null;
    $loginUrl = Route::has('login') ? route('login') : $previewLogin;
    $portalUrl = auth()->check() ? route(auth()->user()->dashboardRouteName()) : null;
@endphp

<nav class="navbar navbar-expand-lg fixed-top site-navbar" data-site-navbar aria-label="Primary navigation">
    <div class="container">
        <x-brand />

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#publicNavigation" aria-controls="publicNavigation" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="publicNavigation">
            <ul class="navbar-nav mx-auto align-items-lg-center">
                <li class="nav-item"><a class="nav-link" data-navbar-link href="#home">Home</a></li>
                <li class="nav-item"><a class="nav-link" data-navbar-link href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link" data-navbar-link href="#how-it-works">How It Works</a></li>
                <li class="nav-item"><a class="nav-link" data-navbar-link href="#about">About</a></li>
            </ul>

            <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-2 mt-3 mt-lg-0">
                @if ($portalUrl)
                    <a class="btn btn-primary" href="{{ $portalUrl }}">Open Portal</a>
                @elseif ($loginUrl)
                    <a class="btn btn-primary" href="{{ $loginUrl }}">Sign In</a>
                @else
                    <span class="auth-placeholder" aria-disabled="true" title="Login will be available when authentication is connected">
                        Login <span class="soon-badge">Soon</span>
                    </span>
                @endif
            </div>
        </div>
    </div>
</nav>
