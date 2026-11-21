@extends('layouts.app')

@section('title', 'Report an Issue')
@section('page_heading', 'Report an Issue')
@section('page_description', 'Give the campus team clear details so the concern can be reviewed efficiently.')

@php
    $preview = request()->routeIs('preview.*');
    $canSubmit = Route::has('student.issues.store') && !$preview;
    $formAction = $canSubmit ? route('student.issues.store') : url()->current();
    $issuesUrl = $preview ? route('preview.student.issues.index') : (Route::has('student.issues.index') ? route('student.issues.index') : '#');
    $categoryOptions = $categories ?? collect();
    $buildingOptions = $buildings ?? collect();
    $locationOptions = $locations ?? collect();
    $selectedBuildingId = (string) old('building_id', '');
    $selectedLocationId = (string) old('location_id', '');
@endphp

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ $issuesUrl }}">My Reports</a></li>
    <li class="breadcrumb-item active" aria-current="page">New Report</li>
@endsection

@section('content')
    @if ($preview || !$canSubmit)
        <x-alert type="warning" title="Preview only">Issue creation is not connected yet. You can explore the form and image previews, but submission is disabled.</x-alert>
    @endif

    <div class="row g-4">
        <div class="col-xl-8">
            <form class="app-card overflow-hidden" action="{{ $formAction }}" method="POST" enctype="multipart/form-data" data-validate novalidate>
                @csrf
                <section class="report-form-section" aria-labelledby="report-basics-title">
                    <div class="d-flex gap-3 mb-4"><span class="form-section-number">1</span><div><h2 class="h5 mb-1" id="report-basics-title">Issue details</h2><p class="text-muted-cf small mb-0">Start with a concise title and the most relevant category.</p></div></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="title">Issue title <span class="required-mark">*</span></label>
                            <input class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" maxlength="120" placeholder="Example: Projector is not displaying in Room 402" required>
                            <div class="form-hint mt-1">Be specific and describe one issue per report.</div><x-form-error field="title" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="category_id">Category <span class="required-mark">*</span></label>
                            <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                <option value="">Choose a category</option>
                                @foreach ($categoryOptions as $category)<option value="{{ data_get($category, 'id') }}" @selected((string) old('category_id') === (string) data_get($category, 'id'))>{{ data_get($category, 'name') }}</option>@endforeach
                            </select><x-form-error field="category_id" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="priority">Priority <span class="text-muted-cf fw-normal">(optional)</span></label>
                            <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority">
                                <option value="">Let the review team assess</option>
                                @foreach (['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'] as $value => $label)<option value="{{ $value }}" @selected(old('priority') === $value)>{{ $label }}</option>@endforeach
                            </select><div class="form-hint mt-1">Use urgent only for immediate safety or service concerns.</div><x-form-error field="priority" />
                        </div>
                    </div>
                </section>

                <section class="report-form-section" aria-labelledby="report-location-title">
                    <div class="d-flex gap-3 mb-4"><span class="form-section-number">2</span><div><h2 class="h5 mb-1" id="report-location-title">Location and description</h2><p class="text-muted-cf small mb-0">Select the building and room where the issue is located, then describe what you observed.</p></div></div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="building_id">Building <span class="required-mark">*</span></label>
                            <select class="form-select @error('building_id') is-invalid @enderror" id="building_id" name="building_id" data-building-select="location_id" required>
                                <option value="">Choose a building</option>
                                @foreach ($buildingOptions as $building)
                                    @php
                                        $bId = (string) data_get($building, 'id');
                                        $bCode = data_get($building, 'code');
                                    @endphp
                                    <option value="{{ $bId }}" @selected($selectedBuildingId === $bId)>{{ data_get($building, 'name') }}@if($bCode) ({{ $bCode }})@endif</option>
                                @endforeach
                            </select>
                            <x-form-error field="building_id" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="location_id">Room / Area <span class="required-mark">*</span></label>
                            <select class="form-select @error('location_id') is-invalid @enderror" id="location_id" name="location_id" data-selected-location="{{ $selectedLocationId }}" required>
                                <option value="">Select a building first</option>
                                @foreach ($locationOptions as $location)
                                    @php
                                        $locId = (string) data_get($location, 'id');
                                        $locBuildingId = (string) data_get($location, 'building_id');
                                        $roomName = data_get($location, 'room_or_area') ?: data_get($location, 'name');
                                        $floorName = data_get($location, 'floor');
                                    @endphp
                                    <option value="{{ $locId }}" data-building-id="{{ $locBuildingId }}" @selected($selectedLocationId === $locId)>{{ $roomName }}@if($floorName) ({{ $floorName }})@endif</option>
                                @endforeach
                            </select>
                            <x-form-error field="location_id" />
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between gap-3"><label class="form-label" for="description">Description <span class="required-mark">*</span></label><span class="form-hint" id="description-count">0 / 1500</span></div>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="7" minlength="20" maxlength="1500" data-character-count="description-count" placeholder="Describe what happened, when you noticed it, and any details that may help the campus team." required>{{ old('description') }}</textarea>
                        <div class="form-hint mt-1">Include room numbers or nearby landmarks when relevant.</div><x-form-error field="description" />
                    </div>
                </section>

                <section class="report-form-section" aria-labelledby="report-evidence-title">
                    <div class="d-flex gap-3 mb-4"><span class="form-section-number">3</span><div><h2 class="h5 mb-1" id="report-evidence-title">Supporting images</h2><p class="text-muted-cf small mb-0">Images are optional, but can help the reviewer understand the concern.</p></div></div>
                    <div class="upload-zone">
                        <label class="btn btn-outline-primary mb-2" for="attachments">Choose Images</label>
                        <input class="visually-hidden @error('attachments') is-invalid @enderror" id="attachments" name="attachments[]" type="file" accept="image/jpeg,image/png,image/webp" multiple data-image-input="attachment-previews" data-file-feedback="attachment-feedback" data-max-bytes="5242880">
                        <p class="form-hint mb-0">Up to 5 JPG, PNG, or WebP images. Maximum 5 MB each.</p>
                        <div class="form-hint mt-2" id="attachment-feedback" aria-live="polite"></div>
                    </div>
                    <div class="attachment-previews" id="attachment-previews" aria-live="polite"></div>
                    <x-form-error field="attachments" />
                    <x-form-error field="attachments.*" />
                </section>

                <section class="report-form-section">
                    <div class="form-check mb-4">
                        <input class="form-check-input @error('accuracy_confirmed') is-invalid @enderror" id="accuracy_confirmed" name="accuracy_confirmed" type="checkbox" value="1" required @checked(old('accuracy_confirmed'))>
                        <label class="form-check-label small" for="accuracy_confirmed">I confirm that this report is accurate to the best of my knowledge and relates to a genuine campus concern.</label>
                        <x-form-error field="accuracy_confirmed" />
                    </div>
                    <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                        <a class="btn btn-outline-primary" href="{{ $issuesUrl }}">Cancel</a>
                        <button class="btn btn-primary" type="submit" @disabled(!$canSubmit)>Submit Report</button>
                    </div>
                </section>
            </form>
        </div>
        <div class="col-xl-4">
            <aside class="app-card position-sticky" style="top: 6rem;">
                <div class="app-card-body">
                    <h2 class="h6">Before you submit</h2>
                    <ul class="tip-list text-muted-cf">
                        @foreach (['Check that the location is correct.', 'Avoid including private or sensitive information.', 'For immediate danger, contact the appropriate campus emergency channel.'] as $tip)<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="m20 6-11 11-5-5"/></svg>{{ $tip }}</li>@endforeach
                    </ul>
                </div>
            </aside>
        </div>
    </div>
@endsection
