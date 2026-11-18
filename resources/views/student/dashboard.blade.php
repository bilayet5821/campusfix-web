@extends('layouts.app')

@section('title', 'Student Dashboard')
@section('page_heading', 'Welcome back, '.str(data_get($student, 'name', 'Student'))->before(' '))
@section('page_description', 'Here is the latest progress across your campus issue reports.')
@section('breadcrumbs')<li class="breadcrumb-item active" aria-current="page">Dashboard</li>@endsection

@php
    $preview = request()->routeIs('preview.*');
    $createUrl = $preview ? route('preview.student.issues.create') : (Route::has('student.issues.create') ? route('student.issues.create') : '#');
    $issuesUrl = $preview ? route('preview.student.issues.index') : (Route::has('student.issues.index') ? route('student.issues.index') : '#');
    $dashboardStats = $stats ?? [];
    $dashboardIssues = $recentIssues ?? collect();
    $activities = $recentActivity ?? collect();
@endphp

@section('page_actions')<a class="btn btn-primary" href="{{ $createUrl }}">+ New Report</a>@endsection

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><x-stat-card label="Total Reports" :value="data_get($dashboardStats, 'total', 0)" /></div>
        <div class="col-6 col-xl-3"><x-stat-card label="Pending Review" :value="data_get($dashboardStats, 'pending_review', 0)" tone="amber" icon="clock" /></div>
        <div class="col-6 col-xl-3"><x-stat-card label="In Progress" :value="data_get($dashboardStats, 'in_progress', 0)" tone="teal" icon="progress" /></div>
        <div class="col-6 col-xl-3"><x-stat-card label="Resolved" :value="data_get($dashboardStats, 'resolved', 0)" tone="green" icon="check" /></div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <section class="app-card" aria-labelledby="recent-reports-title">
                <div class="app-card-header">
                    <h2 id="recent-reports-title">Recent reports</h2>
                    <a class="auth-link small" href="{{ $issuesUrl }}">View all</a>
                </div>
                @forelse ($dashboardIssues as $issue)
                    @php
                        $viewUrl = $preview ? route('preview.student.issues.show') : (Route::has('student.issues.show') ? route('student.issues.show', data_get($issue, 'id')) : null);
                    @endphp
                    <x-issue-card :issue="$issue" :view-url="$viewUrl" />
                @empty
                    <x-empty-state title="No reports yet" description="When you submit your first campus issue, its progress will appear here." :action-url="$createUrl" action-label="Report an Issue" />
                @endforelse
            </section>
        </div>

        <div class="col-xl-4">
            <section class="app-card mb-4" aria-labelledby="activity-title">
                <div class="app-card-header"><h2 id="activity-title">Recent activity</h2></div>
                <div class="app-card-body">
                    @forelse ($activities as $activity)
                        @php $activityDate = data_get($activity, 'created_at'); @endphp
                        <div class="activity-item">
                            <span class="activity-dot" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m20 6-11 11-5-5"/></svg></span>
                            <div><strong>{{ data_get($activity, 'title', 'Report updated') }}</strong><p>{{ data_get($activity, 'description') }}</p><time>{{ $activityDate instanceof \DateTimeInterface ? $activityDate->diffForHumans() : $activityDate }}</time></div>
                        </div>
                    @empty
                        <p class="text-muted-cf small mb-0">No recent updates are available.</p>
                    @endforelse
                </div>
            </section>

            <aside class="app-card tip-panel">
                <div class="app-card-body">
                    <h2 class="h6">Tips for a useful report</h2>
                    <ul class="tip-list">
                        @foreach (['Use a specific, descriptive title.', 'Include the building and exact room or area.', 'Add a clear photo when it helps explain the issue.'] as $tip)
                            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="m20 6-11 11-5-5"/></svg>{{ $tip }}</li>
                        @endforeach
                    </ul>
                </div>
            </aside>
        </div>
    </div>
@endsection
