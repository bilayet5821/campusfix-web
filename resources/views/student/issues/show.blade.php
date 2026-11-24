@extends('layouts.app')

@section('title', data_get($issue, 'reference_number', 'Issue Details'))
@section('page_heading', data_get($issue, 'title', 'Issue Details'))
@section('page_description', data_get($issue, 'reference_number', 'Report reference pending'))

@php
    $preview = request()->routeIs('preview.*');
    $issuesUrl = $preview ? route('preview.student.issues.index') : (Route::has('student.issues.index') ? route('student.issues.index') : '#');
    $createdAt = data_get($issue, 'created_at');
    $attachments = data_get($issue, 'attachments', collect());
    $history = $statusHistory ?? data_get($issue, 'status_history', collect());
@endphp

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ $issuesUrl }}">My Reports</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ data_get($issue, 'reference_number', 'Details') }}</li>
@endsection
@section('page_actions')<a class="btn btn-outline-primary" href="{{ $issuesUrl }}">← Back to My Reports</a>@endsection

@section('content')
    <x-alert type="info"><strong>Read-only student view.</strong> Only authorized users should receive this report from the backend. Students cannot edit, delete, or change issue status here.</x-alert>

    <div class="row g-4">
        <div class="col-xl-8">
            <article class="app-card mb-4">
                <div class="app-card-header flex-wrap gap-2"><h2>Report details</h2><div class="d-flex gap-2"><x-status-badge :status="data_get($issue, 'status')" /><x-priority-badge :priority="data_get($issue, 'priority')" /></div></div>
                <div class="app-card-body">
                    <h3 class="h6 mb-3">Description</h3>
                    <p class="description-copy mb-4">{{ data_get($issue, 'description', 'No description was provided.') }}</p>

                    @if (collect($attachments)->isNotEmpty())
                        <h3 class="h6 mb-3">Attachments</h3>
                        <x-attachment-gallery :attachments="$attachments" />
                    @endif
                </div>
            </article>

            @if (data_get($issue, 'resolution_note'))
                <section class="resolution-note mb-4" aria-labelledby="resolution-note-title">
                    <h2 class="h6 mb-2" id="resolution-note-title">Resolution note</h2>
                    <p class="small mb-0">{{ data_get($issue, 'resolution_note') }}</p>
                </section>
            @endif

            <section class="app-card" aria-labelledby="timeline-title">
                <div class="app-card-header"><h2 id="timeline-title">Status timeline</h2></div>
                <div class="app-card-body">
                    <x-status-timeline :history="$history" />
                </div>
            </section>
        </div>

        <div class="col-xl-4">
            <aside class="app-card position-sticky" style="top: 6rem;">
                <div class="app-card-header"><h2>Report information</h2></div>
                <div class="app-card-body">
                    <dl class="detail-list mb-0">
                        <div class="detail-item"><dt>Reference</dt><dd>{{ data_get($issue, 'reference_number', '—') }}</dd></div>
                        <div class="detail-item"><dt>Category</dt><dd>{{ data_get($issue, 'category.name', '—') }}</dd></div>
                        <div class="detail-item"><dt>Location</dt><dd>{{ data_get($issue, 'location.name', '—') }}</dd></div>
                        <div class="detail-item"><dt>Reported by</dt><dd>{{ data_get($issue, 'reporter.name', '—') }}</dd></div>
                        <div class="detail-item"><dt>Submitted</dt><dd>{{ $createdAt instanceof \DateTimeInterface ? $createdAt->format('M j, Y \a\t g:i A') : $createdAt }}</dd></div>
                        <div class="detail-item"><dt>Assigned staff</dt><dd>{{ data_get($issue, 'assignee.name', 'Not assigned yet') }}</dd></div>
                    </dl>
                </div>
            </aside>
        </div>
    </div>
@endsection
