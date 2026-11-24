@extends('layouts.admin')

@section('title', 'Issue Management')
@section('page_heading', 'Issue Management')
@section('page_description', 'Search, prioritize, assign, and review campus issue reports.')
@section('breadcrumbs')<li class="breadcrumb-item active" aria-current="page">Issues</li>@endsection

@php
    $preview = request()->routeIs('preview.*');
    $indexUrl = $preview ? route('preview.admin.issues.index') : (Route::has('admin.issues.index') ? route('admin.issues.index') : url()->current());
    $reportItems = $issues ?? collect();
    $activeFilters = $filters ?? request()->only(['search', 'status', 'priority', 'category', 'date_from', 'date_to']);
    $categoryOptions = $categories ?? collect();
@endphp

@section('content')
    <form class="filter-bar mb-4" method="GET" action="{{ $indexUrl }}" aria-label="Filter issue reports">
        <div class="row g-3 align-items-end">
            <div class="col-lg-4"><label class="form-label" for="search">Search</label><input class="form-control" id="search" name="search" type="search" value="{{ data_get($activeFilters, 'search') }}" placeholder="Reference or report title"></div>
            <div class="col-6 col-lg-2"><label class="form-label" for="status">Status</label><select class="form-select" id="status" name="status"><option value="">All statuses</option>@foreach (['submitted' => 'Submitted', 'under_review' => 'Under Review', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed', 'rejected' => 'Rejected'] as $value => $label)<option value="{{ $value }}" @selected(data_get($activeFilters, 'status') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-6 col-lg-2"><label class="form-label" for="priority">Priority</label><select class="form-select" id="priority" name="priority"><option value="">All priorities</option>@foreach (['low', 'medium', 'high', 'urgent'] as $value)<option value="{{ $value }}" @selected(data_get($activeFilters, 'priority') === $value)>{{ ucfirst($value) }}</option>@endforeach</select></div>
            <div class="col-md-6 col-lg-2"><label class="form-label" for="category">Category</label><select class="form-select" id="category" name="category"><option value="">All categories</option>@foreach ($categoryOptions as $category)<option value="{{ data_get($category, 'id') }}" @selected((string) data_get($activeFilters, 'category') === (string) data_get($category, 'id'))>{{ data_get($category, 'name') }}</option>@endforeach</select></div>
            <div class="col-md-3 col-lg-2"><label class="form-label" for="date_from">From</label><input class="form-control" id="date_from" name="date_from" type="date" value="{{ data_get($activeFilters, 'date_from') }}"></div>
            <div class="col-md-3 col-lg-2"><label class="form-label" for="date_to">To</label><input class="form-control" id="date_to" name="date_to" type="date" value="{{ data_get($activeFilters, 'date_to') }}"></div>
            <div class="col-lg-4 d-flex gap-2"><button class="btn btn-primary flex-grow-1" type="submit">Apply Filters</button><a class="btn btn-outline-primary" href="{{ $indexUrl }}">Clear</a></div>
        </div>
    </form>

    <section class="app-card overflow-hidden" aria-labelledby="admin-issues-title">
        <div class="app-card-header"><h2 id="admin-issues-title">All issue reports</h2><span class="small text-muted-cf">{{ method_exists($reportItems, 'total') ? $reportItems->total() : $reportItems->count() }} total</span></div>
        @if ($reportItems->isEmpty())
            <x-empty-state title="No issues found" description="No issue reports match the current filter selection." />
        @else
            <div class="table-responsive d-none d-xl-block">
                <table class="table table-hover table-issues mb-0">
                    <thead><tr><th>Issue</th><th>Reporter</th><th>Category / Location</th><th>Assignee</th><th>Status</th><th>Priority</th><th>Created</th><th><span class="visually-hidden">Actions</span></th></tr></thead>
                    <tbody>
                        @foreach ($reportItems as $issue)
                            @php
                                $createdAt = data_get($issue, 'created_at');
                                $showUrl = $preview ? route('preview.admin.issues.show') : (Route::has('admin.issues.show') ? route('admin.issues.show', data_get($issue, 'id')) : '#');
                                $editUrl = $preview ? route('preview.admin.issues.edit') : (Route::has('admin.issues.edit') ? route('admin.issues.edit', data_get($issue, 'id')) : '#');
                                $reporterName = data_get($issue, 'reporter.name', 'Unknown reporter');
                            @endphp
                            <tr>
                                <td><span class="issue-reference">{{ data_get($issue, 'reference_number') }}</span><div class="issue-title">{{ data_get($issue, 'title') }}</div></td>
                                <td><div class="reporter-cell"><span class="profile-avatar">{{ collect(explode(' ', $reporterName))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->join('') }}</span><span><strong>{{ $reporterName }}</strong><small>{{ data_get($issue, 'reporter.student_id', data_get($issue, 'reporter.email', 'Student')) }}</small></span></div></td>
                                <td><strong class="d-block">{{ data_get($issue, 'category.name', '-') }}</strong><span class="text-muted-cf">{{ data_get($issue, 'location.name', '-') }}</span></td>
                                <td>{{ data_get($issue, 'assignee.name', 'Unassigned') }}</td>
                                <td><x-status-badge :status="data_get($issue, 'status')" /></td>
                                <td><x-priority-badge :priority="data_get($issue, 'priority')" /></td>
                                <td>{{ $createdAt instanceof \DateTimeInterface ? $createdAt->format('M j, Y') : $createdAt }}</td>
                                <td><div class="d-flex gap-2"><a class="btn btn-sm btn-outline-primary" href="{{ $showUrl }}">View</a><a class="btn btn-sm btn-primary" href="{{ $editUrl }}">Manage</a></div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-xl-none">
                @foreach ($reportItems as $issue)
                    @php
                        $showUrl = $preview ? route('preview.admin.issues.show') : (Route::has('admin.issues.show') ? route('admin.issues.show', data_get($issue, 'id')) : '#');
                        $editUrl = $preview ? route('preview.admin.issues.edit') : (Route::has('admin.issues.edit') ? route('admin.issues.edit', data_get($issue, 'id')) : '#');
                    @endphp
                    <div class="issue-card">
                        <x-issue-card :issue="$issue" />
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3"><span class="small text-muted-cf">Reporter: {{ data_get($issue, 'reporter.name', 'Unknown') }} | Assignee: {{ data_get($issue, 'assignee.name', 'Unassigned') }}</span><span class="d-flex gap-2"><a class="btn btn-sm btn-outline-primary" href="{{ $showUrl }}">View</a><a class="btn btn-sm btn-primary" href="{{ $editUrl }}">Manage</a></span></div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
    <x-pagination :paginator="$reportItems" />
@endsection
