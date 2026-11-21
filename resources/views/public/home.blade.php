@extends('layouts.public')

@section('title', 'Report. Track. Resolve.')
@section('meta_description', 'CampusFix gives students one clear place to report campus issues, track progress, and receive transparent resolution updates.')

@php
    $previewAvailable = app()->environment('local') && Route::has('preview.student.issues.create');
    $reportUrl = Route::has('student.issues.create') ? route('student.issues.create') : ($previewAvailable ? route('preview.student.issues.create') : '#get-started');
    $ctaUrl = auth()->check()
        ? route(auth()->user()->dashboardRouteName())
        : (Route::has('login') ? route('login') : null);
@endphp

@section('content')
    <section class="hero-section" id="home" aria-labelledby="hero-title">
        <div class="hero-grid" aria-hidden="true"></div>
        <div class="container position-relative">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <div class="hero-eyebrow">
                        <span class="hero-eyebrow-dot" aria-hidden="true"></span>
                        A clearer path to a better campus
                    </div>
                    <h1 class="hero-title" id="hero-title">
                        Campus issues deserve a
                        <span class="hero-title-accent">clear resolution.</span>
                    </h1>
                    <p class="hero-copy mb-4">
                        CampusFix brings issue reporting, progress tracking, and resolution updates into one simple experience—so students always know what is happening next.
                    </p>
                    <div class="d-flex flex-column flex-sm-row gap-3">
                        <a class="btn btn-primary btn-lg btn-mobile-full" href="{{ $reportUrl }}">
                            Report an Issue
                            <svg class="ms-1" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                        </a>
                        <a class="btn btn-outline-primary btn-lg btn-mobile-full" href="#how-it-works">See How It Works</a>
                    </div>
                    <p class="hero-note mb-0">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m20 6-11 11-5-5"/></svg>
                        Built for structured reports, visible progress, and useful updates.
                    </p>
                </div>

                <div class="col-lg-5">
                    <div class="dashboard-visual" role="img" aria-label="Illustration of a CampusFix student dashboard showing issue summaries and recent reports">
                        <div class="visual-toolbar">
                            <div class="visual-dots" aria-hidden="true"><span></span><span></span><span></span></div>
                            <span class="visual-toolbar-label">Student dashboard preview</span>
                        </div>
                        <div class="visual-body">
                            <div class="visual-sidebar" aria-hidden="true">
                                <span class="visual-side-item"></span><span class="visual-side-item"></span><span class="visual-side-item"></span><span class="visual-side-item"></span>
                            </div>
                            <div class="visual-content">
                                <div class="visual-heading">
                                    <strong>My reports</strong>
                                    <span class="visual-button">+ New report</span>
                                </div>
                                <div class="visual-stats">
                                    <div class="visual-stat"><span>Open reports</span><strong>2</strong></div>
                                    <div class="visual-stat"><span>In progress</span><strong>1</strong></div>
                                    <div class="visual-stat"><span>Resolved</span><strong>3</strong></div>
                                </div>
                                <div class="visual-list">
                                    <div class="visual-row">
                                        <span class="visual-row-icon"></span>
                                        <span class="visual-row-copy"><span>Projector needs attention</span><small>Classroom facilities</small></span>
                                        <span class="visual-status">Under review</span>
                                    </div>
                                    <div class="visual-row">
                                        <span class="visual-row-icon"></span>
                                        <span class="visual-row-copy"><span>Wi-Fi connection issue</span><small>IT &amp; network</small></span>
                                        <span class="visual-status is-progress">In progress</span>
                                    </div>
                                    <div class="visual-row">
                                        <span class="visual-row-icon"></span>
                                        <span class="visual-row-copy"><span>Hallway light repair</span><small>Electrical</small></span>
                                        <span class="visual-status">Submitted</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="floating-card">
                            <span class="floating-card-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m20 6-11 11-5-5"/></svg>
                            </span>
                            <span><strong>Update received</strong><small>Your report is moving forward</small></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-space" id="features" aria-labelledby="features-title">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-label">A simpler reporting experience</span>
                <h2 class="section-title" id="features-title">From concern to resolution, all in one place</h2>
                <p class="section-intro">CampusFix keeps each report organized and understandable, giving students a consistent way to communicate what needs attention.</p>
            </div>

            <div class="row g-4">
                @foreach ([
                    ['Report Issues', 'Share the right details, category, location, and supporting context through a guided report.', 'M4 4h16v16H4z M8 9h8 M8 13h5'],
                    ['Track Progress', 'Follow the current status of every report without losing updates across scattered channels.', 'M4 19V9 M10 19V5 M16 19v-7 M22 19H2'],
                    ['Transparent Updates', 'See meaningful status changes and notes that make the next step easy to understand.', 'M4 4h16v13H7l-3 3V4z M8 9h8 M8 13h5'],
                    ['Efficient Resolution', 'Help campus teams receive structured information they can review, assign, and resolve.', 'M12 3v4 M12 17v4 M3 12h4 M17 12h4 M5.6 5.6l2.8 2.8 M15.6 15.6l2.8 2.8 M18.4 5.6l-2.8 2.8 M8.4 15.6l-2.8 2.8'],
                ] as [$title, $copy, $path])
                    <div class="col-md-6 col-xl-3">
                        <article class="feature-card">
                            <span class="icon-box" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $path }}"/></svg>
                            </span>
                            <h3>{{ $title }}</h3>
                            <p>{{ $copy }}</p>
                        </article>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section-space section-soft" id="how-it-works" aria-labelledby="steps-title">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-label">How it works</span>
                <h2 class="section-title" id="steps-title">Three clear steps, one connected process</h2>
                <p class="section-intro">A straightforward workflow keeps students informed while giving the responsible team the context needed to act.</p>
            </div>

            <div class="row g-5 steps-wrap">
                <div class="col-md-4 step-card">
                    <span class="step-number">01</span>
                    <div><h3>Submit a Report</h3><p>Describe the issue, choose a category and location, and add supporting evidence when helpful.</p></div>
                </div>
                <div class="col-md-4 step-card">
                    <span class="step-number">02</span>
                    <div><h3>Track Updates</h3><p>Return to one organized view to follow status changes and administrative updates.</p></div>
                </div>
                <div class="col-md-4 step-card">
                    <span class="step-number">03</span>
                    <div><h3>Get Resolution</h3><p>Receive a clear outcome and a record of how the campus concern was addressed.</p></div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-space" id="categories" aria-labelledby="categories-title">
        <div class="container">
            <div class="row align-items-end mb-5 g-3">
                <div class="col-lg-7">
                    <span class="section-label">Issue categories</span>
                    <h2 class="section-title mb-0" id="categories-title">The right destination for every campus concern</h2>
                </div>
                <div class="col-lg-5">
                    <p class="text-muted-cf mb-0">Clear categories make reports easier to understand and help direct them to the appropriate campus team.</p>
                </div>
            </div>

            <div class="row g-3">
                @foreach ([
                    ['Classroom Facilities', 'Furniture, projectors, boards, and room facilities', 'M3 5h18v12H3z M8 21h8 M12 17v4'],
                    ['IT & Network', 'Connectivity, lab equipment, and digital services', 'M5 12.5a10 10 0 0 1 14 0 M8 16a6 6 0 0 1 8 0 M11 19.5a2 2 0 0 1 2 0'],
                    ['Electrical', 'Lighting, outlets, fans, and electrical concerns', 'M13 2 4.5 13H11l-1 9 8.5-11H12z'],
                    ['Cleanliness', 'Waste, sanitation, and shared-space cleanliness', 'M4 7h16 M9 7V4h6v3 M7 7l1 14h8l1-14'],
                    ['Safety', 'Hazards, access concerns, and urgent safety observations', 'M12 3 4 6v5c0 5 3.5 8.5 8 10 4.5-1.5 8-5 8-10V6z M12 8v5 M12 17h.01'],
                    ['Maintenance', 'Doors, plumbing, fixtures, and general repairs', 'M14.7 6.3a4 4 0 0 0-5 5L3 18l3 3 6.7-6.7a4 4 0 0 0 5-5l-2.5 2.5-3-3z'],
                ] as [$title, $copy, $path])
                    <div class="col-md-6 col-xl-4">
                        <article class="category-card">
                            <span class="icon-box" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $path }}"/></svg></span>
                            <div><h3>{{ $title }}</h3><p>{{ $copy }}</p></div>
                        </article>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="pb-5 pb-lg-7" id="about" aria-labelledby="benefits-title">
        <div class="container">
            <div class="benefits-panel">
                <div class="row align-items-center g-5 position-relative" style="z-index: 1;">
                    <div class="col-lg-5">
                        <span class="section-label">Designed around students</span>
                        <h2 class="section-title" id="benefits-title">Less uncertainty. Better campus communication.</h2>
                        <p class="mb-0" style="color: #b9c8da;">CampusFix is designed to replace unclear, fragmented reporting with a process students can understand and confidently revisit.</p>
                    </div>
                    <div class="col-lg-7">
                        <div class="row g-4">
                            @foreach ([
                                ['One dependable record', 'Keep report details and later updates together in a single, easy-to-find place.'],
                                ['Clear status language', 'Understand whether a concern is submitted, under review, in progress, or resolved.'],
                                ['Better report quality', 'Guided fields encourage useful context that can reduce unnecessary follow-up.'],
                                ['Responsive by design', 'Report and check progress comfortably from a phone, tablet, or desktop.'],
                            ] as [$title, $copy])
                                <div class="col-md-6">
                                    <div class="benefit-item">
                                        <span class="benefit-check" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m20 6-11 11-5-5"/></svg></span>
                                        <div><h3>{{ $title }}</h3><p>{{ $copy }}</p></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-space" id="get-started" aria-labelledby="cta-title">
        <div class="container">
            <div class="cta-panel text-center">
                <div class="position-relative" style="z-index: 1;">
                    <span class="section-label">Ready for what comes next</span>
                    <h2 class="section-title" id="cta-title">A better way to speak up about campus issues</h2>
                    <p class="section-intro mb-4">Accounts and feature permissions are provisioned by the CampusFix Administrator to keep campus reporting secure.</p>
                    @if ($ctaUrl)
                        <a class="btn btn-primary btn-lg" href="{{ $ctaUrl }}">{{ auth()->check() ? 'Go to Your Portal' : 'Sign In to CampusFix' }}</a>
                    @else
                        <button class="btn btn-primary btn-lg" type="button" disabled aria-describedby="access-note">Campus Access Managed by Admin</button>
                        <p class="small text-muted-cf mt-3 mb-0" id="access-note">Contact the campus administrator to receive login credentials.</p>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
