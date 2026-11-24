@extends('layouts.admin')

@section('title', data_get($issue, 'reference_number', 'Issue Details'))
@section('page_heading', data_get($issue, 'title', 'Issue Details'))
@section('page_description', data_get($issue, 'reference_number', 'Report reference pending'))

@php
    $preview = request()->routeIs('preview.*');
    $issuesUrl = $preview ? route('preview.admin.issues.index') : (Route::has('admin.issues.index') ? route('admin.issues.index') : '#');
    $editUrl = $preview ? route('preview.admin.issues.edit') : (Route::has('admin.issues.edit') ? route('admin.issues.edit', data_get($issue, 'id')) : '#');
    $createdAt = data_get($issue, 'created_at');
    $attachments = data_get($issue, 'attachments', collect());
    $history = $statusHistory ?? data_get($issue, 'status_history', collect());
@endphp

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ $issuesUrl }}">Issues</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ data_get($issue, 'reference_number', 'Details') }}</li>
@endsection
@section('page_actions')<div class="d-flex gap-2"><a class="btn btn-outline-primary" href="{{ $issuesUrl }}">Back to Issues</a><a class="btn btn-primary" href="{{ $editUrl }}">Manage Issue</a></div>@endsection

@section('content')
    <div class="row g-4">
        <div class="col-xl-8">
            <article class="app-card mb-4">
                <div class="app-card-header flex-wrap gap-2"><h2>Report details</h2><div class="d-flex gap-2"><x-status-badge :status="data_get($issue, 'status')" /><x-priority-badge :priority="data_get($issue, 'priority')" /></div></div>
                <div class="app-card-body">
                    <h3 class="h6 mb-3">Description</h3><p class="description-copy mb-4">{{ data_get($issue, 'description', 'No description was provided.') }}</p>
                    @if (collect($attachments)->isNotEmpty())<h3 class="h6 mb-3">Attachments</h3><x-attachment-gallery :attachments="$attachments" />@endif
                </div>
            </article>

            <div class="row g-3 mb-4">
                <div class="col-md-6"><section class="app-card h-100"><div class="app-card-header"><h2>Admin note</h2></div><div class="app-card-body"><p class="small mb-0 {{ data_get($issue, 'admin_note') ? '' : 'text-muted-cf' }}">{{ data_get($issue, 'admin_note', 'No administrative note has been added.') }}</p></div></section></div>
                <div class="col-md-6"><section class="app-card h-100"><div class="app-card-header"><h2>Resolution note</h2></div><div class="app-card-body"><p class="small mb-0 {{ data_get($issue, 'resolution_note') ? '' : 'text-muted-cf' }}">{{ data_get($issue, 'resolution_note', 'No resolution note has been added.') }}</p></div></section></div>
            </div>

            <section class="app-card"><div class="app-card-header"><h2>Status history</h2></div><div class="app-card-body"><x-status-timeline :history="$history" /></div></section>
        </div>

        <div class="col-xl-4">
            <aside class="app-card position-sticky" style="top: 6rem;">
                <div class="app-card-header"><h2>Issue information</h2></div>
                <div class="app-card-body">
                    <dl class="detail-list mb-0">
                        <div class="detail-item"><dt>Reference</dt><dd>{{ data_get($issue, 'reference_number', '-') }}</dd></div>
                        <div class="detail-item"><dt>Reporter</dt><dd>{{ data_get($issue, 'reporter.name', '-') }}</dd></div>
                        <div class="detail-item"><dt>Student ID</dt><dd>{{ data_get($issue, 'reporter.student_id', '-') }}</dd></div>
                        <div class="detail-item"><dt>Email</dt><dd>{{ data_get($issue, 'reporter.email', '-') }}</dd></div>
                        <div class="detail-item"><dt>Category</dt><dd>{{ data_get($issue, 'category.name', '-') }}</dd></div>
                        <div class="detail-item"><dt>Location</dt><dd>{{ data_get($issue, 'location.name', '-') }}</dd></div>
                        <div class="detail-item"><dt>Assigned to</dt><dd>{{ data_get($issue, 'assignee.name', 'Unassigned') }}</dd></div>
                        <div class="detail-item"><dt>Submitted</dt><dd>{{ $createdAt instanceof \DateTimeInterface ? $createdAt->format('M j, Y \a\t g:i A') : $createdAt }}</dd></div>
                    </dl>
                </div>
            </aside>
        </div>
    </div>
@endsection
