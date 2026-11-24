@extends('layouts.admin')

@section('title', 'Manage '.data_get($issue, 'reference_number', 'Issue'))
@section('page_heading', 'Manage Issue')
@section('page_description', data_get($issue, 'reference_number').' - '.data_get($issue, 'title'))

@php
    $preview = request()->routeIs('preview.*');
    $canUpdate = Route::has('admin.issues.update') && !$preview;
    $formAction = $canUpdate ? route('admin.issues.update', data_get($issue, 'id')) : url()->current();
    $showUrl = $preview ? route('preview.admin.issues.show') : (Route::has('admin.issues.show') ? route('admin.issues.show', data_get($issue, 'id')) : '#');
    $issuesUrl = $preview ? route('preview.admin.issues.index') : (Route::has('admin.issues.index') ? route('admin.issues.index') : '#');
    $assigneeOptions = $assignees ?? collect();
@endphp

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ $issuesUrl }}">Issues</a></li>
    <li class="breadcrumb-item"><a href="{{ $showUrl }}">{{ data_get($issue, 'reference_number') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Manage</li>
@endsection
@section('page_actions')<a class="btn btn-outline-primary" href="{{ $showUrl }}">Cancel and Return</a>@endsection

@section('content')
    @if ($preview || !$canUpdate)<x-alert type="warning" title="Preview only">Management controls are displayed for integration review, but no changes can be submitted until the real update route is available.</x-alert>@endif

    <div class="row g-4">
        <div class="col-xl-8">
            <form class="app-card" id="issue-management-form" action="{{ $formAction }}" method="POST" data-validate novalidate>
                @csrf
                @method('PATCH')
                <div class="report-form-section">
                    <h2 class="h5 mb-4">Workflow and assignment</h2>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label" for="status">Status <span class="required-mark">*</span></label><select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>@foreach (['submitted' => 'Submitted', 'under_review' => 'Under Review', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed', 'rejected' => 'Rejected'] as $value => $label)<option value="{{ $value }}" @selected(old('status', data_get($issue, 'status')) === $value)>{{ $label }}</option>@endforeach</select><x-form-error field="status" /></div>
                        <div class="col-md-6"><label class="form-label" for="priority">Priority <span class="required-mark">*</span></label><select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>@foreach (['low', 'medium', 'high', 'urgent'] as $value)<option value="{{ $value }}" @selected(old('priority', data_get($issue, 'priority')) === $value)>{{ ucfirst($value) }}</option>@endforeach</select><x-form-error field="priority" /></div>
                        <div class="col-12"><label class="form-label" for="assigned_to">Responsible administrator / staff</label><select class="form-select @error('assigned_to') is-invalid @enderror" id="assigned_to" name="assigned_to"><option value="">Unassigned</option>@foreach ($assigneeOptions as $assignee)<option value="{{ data_get($assignee, 'id') }}" @selected((string) old('assigned_to', data_get($issue, 'assignee.id')) === (string) data_get($assignee, 'id'))>{{ data_get($assignee, 'name') }}@if(data_get($assignee, 'email')) - {{ data_get($assignee, 'email') }}@endif</option>@endforeach</select><x-form-error field="assigned_to" /></div>
                    </div>
                </div>
                <div class="report-form-section">
                    <h2 class="h5 mb-4">Administrative notes</h2>
                    <div class="mb-3"><label class="form-label" for="admin_note">Admin note</label><textarea class="form-control @error('admin_note') is-invalid @enderror" id="admin_note" name="admin_note" rows="4" maxlength="2000" data-character-count="admin-note-count">{{ old('admin_note', data_get($issue, 'admin_note')) }}</textarea><div class="d-flex justify-content-between mt-1"><span class="form-hint">Internal only. Students never see this note.</span><span class="form-hint" id="admin-note-count">0 / 2000</span></div><x-form-error field="admin_note" /></div>
                    <div><label class="form-label" for="resolution_note">Resolution note</label><textarea class="form-control @error('resolution_note') is-invalid @enderror" id="resolution_note" name="resolution_note" rows="4" maxlength="2000" data-character-count="resolution-note-count">{{ old('resolution_note', data_get($issue, 'resolution_note')) }}</textarea><div class="d-flex justify-content-between mt-1"><span class="form-hint">Explain the completed action when resolving or closing an issue.</span><span class="form-hint" id="resolution-note-count">0 / 2000</span></div><x-form-error field="resolution_note" /></div>
                    <div class="form-sticky-actions d-flex justify-content-end gap-2"><a class="btn btn-outline-primary" href="{{ $showUrl }}">Cancel</a><button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#confirmIssueUpdate" @disabled(!$canUpdate)>Review and Save</button></div>
                </div>
            </form>
        </div>
        <div class="col-xl-4">
            <aside class="management-summary position-sticky" style="top: 6rem;"><h2 class="mb-3">Current issue summary</h2><dl class="detail-list mb-0"><div class="detail-item"><dt>Status</dt><dd><x-status-badge :status="data_get($issue, 'status')" /></dd></div><div class="detail-item"><dt>Priority</dt><dd><x-priority-badge :priority="data_get($issue, 'priority')" /></dd></div><div class="detail-item"><dt>Reporter</dt><dd>{{ data_get($issue, 'reporter.name', '-') }}</dd></div><div class="detail-item"><dt>Category</dt><dd>{{ data_get($issue, 'category.name', '-') }}</dd></div><div class="detail-item"><dt>Location</dt><dd>{{ data_get($issue, 'location.name', '-') }}</dd></div></dl></aside>
        </div>
    </div>

    <x-confirmation-modal id="confirmIssueUpdate" title="Confirm issue update" confirm-label="Save Changes" form-id="issue-management-form" :disabled="!$canUpdate">
        <p class="mb-2">You are about to update this issue's workflow information.</p><p class="small text-muted-cf mb-0">Confirm that the selected status, priority, assignment, and notes are accurate.</p>
    </x-confirmation-modal>
@endsection
