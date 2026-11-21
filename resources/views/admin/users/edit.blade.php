@extends('layouts.admin')

@section('title', 'Manage Access — '.$managedUser->name)
@section('page_heading', 'Manage User Access')
@section('page_description', $managedUser->name.' ('.$managedUser->email.')')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users &amp; Access</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $managedUser->name }}</li>
@endsection

@section('page_actions')
    <a class="btn btn-outline-primary" href="{{ route('admin.users.index') }}">← Back to Users</a>
@endsection

@section('content')
    <form class="app-card overflow-hidden" action="{{ route('admin.users.update', $managedUser) }}" method="POST" data-validate novalidate>
        @csrf
        @method('PATCH')
        @include('admin.users._form', ['submitLabel' => 'Save Access Changes'])
    </form>
@endsection
