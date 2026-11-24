@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page_heading', 'Operations Overview')
@section('page_description', 'Monitor issue volume, review progress, and move campus reports toward resolution.')
@section('breadcrumbs')<li class="breadcrumb-item active" aria-current="page">Dashboard</li>@endsection

@php
    $preview = request()->routeIs('preview.*');
    $issuesUrl = $preview ? route('preview.admin.issues.index') : (Route::has('admin.issues.index') ? route('admin.issues.index') : '#');
    $dashboardStats = $stats ?? [];
    $dashboardIssues = $recentIssues ?? collect();
    $statuses = $statusDistribution ?? collect();
    $priorities = $priorityDistribution ?? collect();
    $categoriesData = $categoryDistribution ?? collect();
    $activities = $recentActivity ?? collect();
@endphp

@section('page_actions')<a class="btn btn-primary" href="{{ $issuesUrl }}">Manage Issues</a>@endsection

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-2"><x-stat-card label="Total Reports" :value="data_get($dashboardStats, 'total', 0)" /></div>
        <div class="col-6 col-xl-2"><x-stat-card label="Submitted" :value="data_get($dashboardStats, 'submitted', 0)" tone="amber" icon="clock" /></div>
        <div class="col-6 col-xl-2"><x-stat-card label="Under Review" :value="data_get($dashboardStats, 'under_review', 0)" icon="progress" /></div>
        <div class="col-6 col-xl-2"><x-stat-card label="In Progress" :value="data_get($dashboardStats, 'in_progress', 0)" tone="teal" icon="progress" /></div>
        <div class="col-6 col-xl-2"><x-stat-card label="Resolved" :value="data_get($dashboardStats, 'resolved', 0)" tone="green" icon="check" /></div>
        <div class="col-6 col-xl-2"><x-stat-card label="Closed / Rejected" :value="data_get($dashboardStats, 'closed_rejected', 0)" icon="check" /></div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <section class="app-card h-100" aria-labelledby="recent-admin-issues">
                <div class="app-card-header"><h2 id="recent-admin-issues">Recent issue submissions</h2><a class="auth-link small" href="{{ $issuesUrl }}">View all</a></div>
                @forelse ($dashboardIssues as $issue)
                    @php $viewUrl = $preview ? route('preview.admin.issues.show') : (Route::has('admin.issues.show') ? route('admin.issues.show', data_get($issue, 'id')) : null); @endphp
                    <x-issue-card :issue="$issue" :view-url="$viewUrl" />
                @empty
                    <x-empty-state title="No issue submissions" description="New campus reports will appear here when they are submitted." />
                @endforelse
            </section>
        </div>
        <div class="col-xl-4">
            <section class="app-card h-100" aria-labelledby="admin-activity-title">
                <div class="app-card-header"><h2 id="admin-activity-title">Recent administrative activity</h2></div>
                <div class="app-card-body">
                    @forelse ($activities as $activity)
                        @php $activityDate = data_get($activity, 'created_at'); @endphp
                        <div class="activity-item"><span class="activity-dot" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m20 6-11 11-5-5"/></svg></span><div><strong>{{ data_get($activity, 'title', 'Issue updated') }}</strong><p>{{ data_get($activity, 'description') }}</p><time>{{ $activityDate instanceof \DateTimeInterface ? $activityDate->diffForHumans() : $activityDate }}</time></div></div>
                    @empty
                        <p class="text-muted-cf small mb-0">No recent administrative activity.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    <div class="row g-4">
        @foreach ([['Status distribution', $statuses, 'status'], ['Priority distribution', $priorities, 'priority'], ['Category distribution', $categoriesData, 'category']] as [$heading, $items, $type])
            <div class="col-lg-4">
                <section class="app-card h-100">
                    <div class="app-card-header"><h2>{{ $heading }}</h2></div>
                    <div class="app-card-body">
                        <div class="distribution-list">
                            @forelse ($items as $item)
                                @php
                                    $count = (int) data_get($item, 'count', 0);
                                    $percent = min(100, max(0, (float) data_get($item, 'percentage', 0)));
                                @endphp
                                <div class="distribution-row">
                                    <div class="distribution-meta"><span>{{ data_get($item, 'label', 'Other') }}</span><strong>{{ $count }}</strong></div>
                                    <div class="distribution-track" role="progressbar" aria-label="{{ data_get($item, 'label') }}" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"><div class="distribution-bar {{ $type === 'priority' ? 'is-amber' : ($type === 'category' ? 'is-teal' : '') }}" style="width: {{ $percent }}%"></div></div>
                                </div>
                            @empty
                                <p class="text-muted-cf small mb-0">No distribution data available.</p>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>
        @endforeach
    </div>

    <section class="app-card mt-4">
        <div class="app-card-header"><h2>Quick actions</h2></div>
        <div class="app-card-body"><div class="row g-3"><div class="col-md-6"><a class="quick-link" href="{{ $issuesUrl }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 4h14v16H5zM8 9h8M8 13h8"/></svg><span><strong class="d-block">Review all issues</strong><small class="text-muted-cf">Search, filter, and manage reports</small></span></a></div><div class="col-md-6"><a class="quick-link" href="{{ $issuesUrl }}?status=submitted"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg><span><strong class="d-block">Review submitted reports</strong><small class="text-muted-cf">Open the incoming review queue</small></span></a></div></div></div>
    </section>
@endsection
