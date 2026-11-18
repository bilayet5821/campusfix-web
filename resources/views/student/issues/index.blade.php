@extends('layouts.app')

@section('title', 'My Reports')
@section('page_heading', 'My Reports')
@section('page_description', 'Search, filter, and follow the progress of your submitted campus issues.')
@section('breadcrumbs')<li class="breadcrumb-item active" aria-current="page">My Reports</li>@endsection

@php
    $preview = request()->routeIs('preview.*');
    $createUrl = $preview ? route('preview.student.issues.create') : (Route::has('student.issues.create') ? route('student.issues.create') : '#');
    $indexUrl = $preview ? route('preview.student.issues.index') : (Route::has('student.issues.index') ? route('student.issues.index') : url()->current());
    $reportItems = $issues ?? collect();
    $activeFilters = $filters ?? request()->only(['search', 'status', 'category']);
    $categoryOptions = $categories ?? collect();
@endphp

@section('page_actions')<a class="btn btn-primary" href="{{ $createUrl }}">+ Report Issue</a>@endsection

@section('content')
    <form class="filter-bar mb-4" method="GET" action="{{ $indexUrl }}" aria-label="Filter reports">
        <div class="row g-3 align-items-end">
            <div class="col-lg-5">
                <label class="form-label" for="search">Search reports</label>
                <input class="form-control" id="search" name="search" type="search" value="{{ data_get($activeFilters, 'search') }}" placeholder="Reference number or title">
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label" for="status">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All statuses</option>
                    @foreach (['submitted' => 'Submitted', 'under_review' => 'Under Review', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed', 'rejected' => 'Rejected'] as $value => $label)<option value="{{ $value }}" @selected(data_get($activeFilters, 'status') === $value)>{{ $label }}</option>@endforeach
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label" for="category">Category</label>
                <select class="form-select" id="category" name="category">
                    <option value="">All categories</option>
                    @foreach ($categoryOptions as $category)<option value="{{ data_get($category, 'id') }}" @selected((string) data_get($activeFilters, 'category') === (string) data_get($category, 'id'))>{{ data_get($category, 'name') }}</option>@endforeach
                </select>
            </div>
            <div class="col-lg-3 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1" type="submit">Apply Filters</button>
                <a class="btn btn-outline-primary" href="{{ $indexUrl }}">Clear</a>
            </div>
        </div>
    </form>

    <section class="app-card overflow-hidden" aria-labelledby="reports-list-title">
        <div class="app-card-header">
            <h2 id="reports-list-title">Issue reports</h2>
            <span class="small text-muted-cf">{{ method_exists($reportItems, 'total') ? $reportItems->total() : $reportItems->count() }} total</span>
        </div>

        @if ($reportItems->isEmpty())
            <x-empty-state title="No reports found" description="No issue reports match the current filters. Clear the filters or submit a new report." :action-url="$createUrl" action-label="Report an Issue" />
        @else
            <div class="table-responsive d-none d-lg-block">
                <table class="table table-hover table-issues mb-0">
                    <thead><tr><th scope="col">Issue</th><th scope="col">Category &amp; Location</th><th scope="col">Status</th><th scope="col">Priority</th><th scope="col">Submitted</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                    <tbody>
                        @foreach ($reportItems as $issue)
                            @php
                                $createdAt = data_get($issue, 'created_at');
                                $viewUrl = $preview ? route('preview.student.issues.show') : (Route::has('student.issues.show') ? route('student.issues.show', data_get($issue, 'id')) : '#');
                            @endphp
                            <tr>
                                <td><span class="issue-reference">{{ data_get($issue, 'reference_number') }}</span><div class="issue-title">{{ data_get($issue, 'title') }}</div></td>
                                <td><strong class="d-block">{{ data_get($issue, 'category.name', '—') }}</strong><span class="text-muted-cf">{{ data_get($issue, 'location.name', '—') }}</span></td>
                                <td><x-status-badge :status="data_get($issue, 'status')" /></td>
                                <td><x-priority-badge :priority="data_get($issue, 'priority')" /></td>
                                <td>{{ $createdAt instanceof \DateTimeInterface ? $createdAt->format('M j, Y') : $createdAt }}</td>
                                <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ $viewUrl }}">View Details</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-lg-none">
                @foreach ($reportItems as $issue)
                    @php $viewUrl = $preview ? route('preview.student.issues.show') : (Route::has('student.issues.show') ? route('student.issues.show', data_get($issue, 'id')) : '#'); @endphp
                    <x-issue-card :issue="$issue" :view-url="$viewUrl" />
                @endforeach
            </div>
        @endif
    </section>
    <x-pagination :paginator="$reportItems" />
@endsection
