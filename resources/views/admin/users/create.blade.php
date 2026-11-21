@extends('layouts.admin')

@section('title', 'Create User Account')
@section('page_heading', 'Create User Account')
@section('page_description', 'Provision a new student, maintenance staff, or administrator account with custom feature access.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users &amp; Access</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create Account</li>
@endsection

@section('page_actions')
    <a class="btn btn-outline-primary" href="{{ route('admin.users.index') }}">← Back to Users</a>
@endsection

@section('content')
    <form class="app-card overflow-hidden" action="{{ route('admin.users.store') }}" method="POST" data-validate novalidate>
        @csrf
        @include('admin.users._form', ['submitLabel' => 'Create Account'])
    </form>
@endsection
