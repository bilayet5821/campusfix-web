@extends('layouts.auth')

@section('title', 'Student Registration')

@php
    $preview = request()->routeIs('preview.*');
    $canSubmit = Route::has('register.store') && !$preview;
    $formAction = $canSubmit ? route('register.store') : url()->current();
    $loginUrl = $preview ? route('preview.login') : (Route::has('login') ? route('login') : '#');
    $departmentOptions = $departments ?? collect();
@endphp

@section('content')
    <div class="auth-form-wrap">
        <div class="d-lg-none mb-5"><x-brand /></div>
        <a class="auth-link small d-inline-flex align-items-center gap-1 mb-4" href="{{ route('home') }}">← Return to home</a>
        <h1 class="auth-heading mb-2">Create your student account</h1>
        <p class="text-muted-cf mb-4">Use your student details to prepare for secure campus reporting.</p>

        @if ($preview || !$canSubmit)
            <div class="preview-notice mb-4" role="status"><strong>Frontend preview:</strong> registration is not connected. Your information will not be submitted.</div>
        @endif

        <div class="auth-card">
            <form action="{{ $formAction }}" method="POST" data-validate novalidate>
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="name">Full name <span class="required-mark">*</span></label>
                        <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" autocomplete="name" required autofocus>
                        <x-form-error field="name" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="student_id">Student ID <span class="required-mark">*</span></label>
                        <input class="form-control @error('student_id') is-invalid @enderror" id="student_id" name="student_id" value="{{ old('student_id') }}" autocomplete="off" required>
                        <x-form-error field="student_id" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email address <span class="required-mark">*</span></label>
                        <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                        <x-form-error field="email" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="department_id">Department <span class="required-mark">*</span></label>
                        <select class="form-select @error('department_id') is-invalid @enderror" id="department_id" name="department_id" required>
                            <option value="">Select department</option>
                            @foreach ($departmentOptions as $department)
                                <option value="{{ data_get($department, 'id') }}" @selected((string) old('department_id') === (string) data_get($department, 'id'))>{{ data_get($department, 'name') }}</option>
                            @endforeach
                        </select>
                        <x-form-error field="department_id" />
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="phone">Phone number <span class="text-muted-cf fw-normal">(optional)</span></label>
                        <input class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel">
                        <x-form-error field="phone" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="password">Password <span class="required-mark">*</span></label>
                        <div class="input-group">
                            <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" autocomplete="new-password" minlength="8" required aria-describedby="password-help">
                            <button class="btn password-toggle" type="button" data-password-toggle="password" aria-controls="password" aria-pressed="false"><span data-toggle-label>Show</span></button>
                        </div>
                        <div class="form-hint mt-1" id="password-help">Use at least 8 characters with a mix of letters, numbers, and symbols.</div>
                        <x-form-error field="password" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="password_confirmation">Confirm password <span class="required-mark">*</span></label>
                        <div class="input-group">
                            <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                            <button class="btn password-toggle" type="button" data-password-toggle="password_confirmation" aria-controls="password_confirmation" aria-pressed="false"><span data-toggle-label>Show</span></button>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input @error('terms') is-invalid @enderror" id="terms" name="terms" type="checkbox" value="1" required @checked(old('terms'))>
                            <label class="form-check-label small" for="terms">I confirm that the information provided is accurate and agree to use CampusFix responsibly.</label>
                            <x-form-error field="terms" />
                        </div>
                    </div>
                    <div class="col-12"><button class="btn btn-primary w-100" type="submit" @disabled(!$canSubmit)>Create Student Account</button></div>
                </div>
            </form>
        </div>
        <p class="text-center text-muted-cf small mt-4 mb-0">Already have an account? <a class="auth-link {{ $loginUrl === '#' ? 'pe-none opacity-50' : '' }}" href="{{ $loginUrl }}">Sign in</a></p>
    </div>
@endsection
