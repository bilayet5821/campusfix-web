@extends('layouts.auth')

@section('title', 'Student Login')

@php
    $preview = request()->routeIs('preview.*');
    $canSubmit = Route::has('login.store') && !$preview;
    $formAction = $canSubmit ? route('login.store') : url()->current();
    $registerUrl = $preview ? route('preview.register') : (Route::has('register') ? route('register') : '#');
@endphp

@section('content')
    <div class="auth-form-wrap is-narrow">
        <div class="d-lg-none mb-5"><x-brand /></div>
        <a class="auth-link small d-inline-flex align-items-center gap-1 mb-4" href="{{ route('home') }}">← Return to home</a>
        <h1 class="auth-heading mb-2">Welcome back</h1>
        <p class="text-muted-cf mb-4">Sign in to manage your campus issue reports.</p>

        @if ($preview || !$canSubmit)
            <div class="preview-notice mb-4" role="status"><strong>Frontend preview:</strong> authentication is not connected, so this form cannot sign you in.</div>
        @endif
        @if (session('status'))<x-alert type="success">{{ session('status') }}</x-alert>@endif
        @if ($errors->any())<x-alert type="danger">The provided credentials could not be validated. Please review the form.</x-alert>@endif

        <div class="auth-card">
            <form action="{{ $formAction }}" method="POST" data-validate novalidate>
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="email">Email address <span class="required-mark" aria-hidden="true">*</span></label>
                    <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus aria-describedby="email-error">
                    <x-form-error field="email" id="email-error" />
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label" for="password">Password <span class="required-mark" aria-hidden="true">*</span></label>
                        <span class="text-muted-cf small" title="Password recovery will be connected by the backend team">Forgot password?</span>
                    </div>
                    <div class="input-group">
                        <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" autocomplete="current-password" required aria-describedby="password-error">
                        <button class="btn password-toggle" type="button" data-password-toggle="password" aria-controls="password" aria-pressed="false"><span data-toggle-label>Show</span></button>
                    </div>
                    <x-form-error field="password" id="password-error" />
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))>
                    <label class="form-check-label small" for="remember">Keep me signed in on this device</label>
                </div>
                <button class="btn btn-primary w-100" type="submit" @disabled(!$canSubmit)>Sign In</button>
            </form>
        </div>

        @if ($preview)
            <p class="text-center text-muted-cf small mt-4 mb-0">New to CampusFix? <a class="auth-link {{ $registerUrl === '#' ? 'pe-none opacity-50' : '' }}" href="{{ $registerUrl }}">Create a student account</a></p>
        @else
            <p class="text-center text-muted-cf small mt-4 mb-0">Need an account? Accounts are created and managed by the CampusFix Administrator.</p>
        @endif
    </div>
@endsection
