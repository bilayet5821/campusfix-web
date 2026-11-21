@extends('layouts.admin')

@section('title', 'Buildings & Rooms')
@section('page_heading', 'Buildings & Rooms')
@section('page_description', 'Manage the official campus buildings and rooms available in student issue reports.')
@section('breadcrumbs')<li class="breadcrumb-item active" aria-current="page">Locations</li>@endsection

@php
    $buildingList = $buildings ?? collect();
@endphp

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <section class="app-card h-100" aria-labelledby="add-building-title">
                <div class="app-card-header">
                    <h2 id="add-building-title">Add new building</h2>
                </div>
                <div class="app-card-body">
                    <form action="{{ route('admin.buildings.store') }}" method="POST" data-validate novalidate>
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="name">Building name <span class="required-mark">*</span></label>
                            <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" type="text" value="{{ old('name') }}" maxlength="100" placeholder="Example: Innovation Building" required>
                            <x-form-error field="name" />
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="code">Building code <span class="text-muted-cf fw-normal">(optional)</span></label>
                            <input class="form-control @error('code') is-invalid @enderror" id="code" name="code" type="text" value="{{ old('code') }}" maxlength="20" placeholder="Example: IB">
                            <div class="form-hint mt-1">Short identifier using letters, numbers, or hyphens.</div>
                            <x-form-error field="code" />
                        </div>
                        <button class="btn btn-primary" type="submit">Add Building</button>
                    </form>
                </div>
            </section>
        </div>

        <div class="col-lg-6">
            <section class="app-card h-100" aria-labelledby="add-room-title">
                <div class="app-card-header">
                    <h2 id="add-room-title">Add new room / area</h2>
                </div>
                <div class="app-card-body">
                    <form action="{{ route('admin.locations.store') }}" method="POST" data-validate novalidate>
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="building_id">Building <span class="required-mark">*</span></label>
                            <select class="form-select @error('building_id') is-invalid @enderror" id="building_id" name="building_id" required>
                                <option value="">Choose a building</option>
                                @foreach ($buildingList as $building)
                                    <option value="{{ $building->id }}" @selected((string) old('building_id') === (string) $building->id)>
                                        {{ $building->name }}@if($building->code) ({{ $building->code }})@endif
                                    </option>
                                @endforeach
                            </select>
                            <x-form-error field="building_id" />
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-7">
                                <label class="form-label" for="room_or_area">Room or area <span class="required-mark">*</span></label>
                                <input class="form-control @error('room_or_area') is-invalid @enderror" id="room_or_area" name="room_or_area" type="text" value="{{ old('room_or_area') }}" maxlength="100" placeholder="Example: Room 405 or Robotics Lab" required>
                                <x-form-error field="room_or_area" />
                            </div>
                            <div class="col-md-5">
                                <label class="form-label" for="floor">Floor <span class="text-muted-cf fw-normal">(optional)</span></label>
                                <input class="form-control @error('floor') is-invalid @enderror" id="floor" name="floor" type="text" value="{{ old('floor') }}" maxlength="40" placeholder="Example: 4th Floor">
                                <x-form-error field="floor" />
                            </div>
                        </div>
                        <button class="btn btn-primary" type="submit" @disabled($buildingList->isEmpty())>Add Room</button>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <section class="app-card overflow-hidden" aria-labelledby="campus-directory-title">
        <div class="app-card-header">
            <h2 id="campus-directory-title">Campus buildings &amp; rooms directory</h2>
            <span class="small text-muted-cf">{{ $buildingList->count() }} buildings</span>
        </div>

        @if ($buildingList->isEmpty())
            <x-empty-state title="No buildings added yet" description="Add your first campus building above to start organizing rooms and areas." />
        @else
            <div class="table-responsive">
                <table class="table table-hover table-issues mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Building</th>
                            <th scope="col">Code</th>
                            <th scope="col">Rooms / Areas</th>
                            <th scope="col" class="text-end">Total Rooms</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($buildingList as $building)
                            <tr>
                                <td><strong>{{ $building->name }}</strong></td>
                                <td>
                                    @if ($building->code)
                                        <span class="badge text-bg-light border">{{ $building->code }}</span>
                                    @else
                                        <span class="text-muted-cf">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($building->locations->isEmpty())
                                        <span class="text-muted-cf small">No rooms added yet</span>
                                    @else
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach ($building->locations as $location)
                                                <span class="badge text-bg-secondary bg-opacity-10 text-dark border">
                                                    {{ $location->room_or_area ?: $location->name }}@if($location->floor) · {{ $location->floor }}@endif
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end"><strong>{{ $building->locations_count }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
