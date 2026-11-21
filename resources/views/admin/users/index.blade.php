@extends('layouts.admin')

@section('title', 'Users & Access Control')
@section('page_heading', 'Users & Access Control')
@section('page_description', 'Create user accounts of any type and grant or revoke access to specific CampusFix features.')
@section('breadcrumbs')<li class="breadcrumb-item active" aria-current="page">Users &amp; Access</li>@endsection

@section('page_actions')
    <a class="btn btn-primary" href="{{ route('admin.users.create') }}">+ Create User Account</a>
@endsection

@section('content')
    <form class="filter-bar mb-4" method="GET" action="{{ route('admin.users.index') }}" aria-label="Filter user accounts">
        <div class="row g-3 align-items-end">
            <div class="col-lg-5">
                <label class="form-label" for="search">Search users</label>
                <input class="form-control" id="search" name="search" type="search" value="{{ data_get($filters, 'search') }}" placeholder="Name, email, or student ID">
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label" for="role">Account type</label>
                <select class="form-select" id="role" name="role">
                    <option value="">All types</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->value }}" @selected(data_get($filters, 'role') === $role->value)>{{ $role->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label" for="status">Account status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All statuses</option>
                    <option value="active" @selected(data_get($filters, 'status') === 'active')>Active</option>
                    <option value="inactive" @selected(data_get($filters, 'status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="col-lg-3 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1" type="submit">Apply</button>
                <a class="btn btn-outline-primary" href="{{ route('admin.users.index') }}">Clear</a>
            </div>
        </div>
    </form>

    <section class="app-card overflow-hidden" aria-labelledby="users-directory-title">
        <div class="app-card-header">
            <h2 id="users-directory-title">Registered accounts</h2>
            <span class="small text-muted-cf">{{ $users->total() }} total</span>
        </div>

        @if ($users->isEmpty())
            <x-empty-state title="No matching accounts" description="No user accounts match the current filter selection." :action-url="route('admin.users.create')" action-label="Create User Account" />
        @else
            <div class="table-responsive">
                <table class="table table-hover table-issues mb-0">
                    <thead>
                        <tr>
                            <th scope="col">User</th>
                            <th scope="col">Type</th>
                            <th scope="col">Department / ID</th>
                            <th scope="col">Feature Access</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $account)
                            <tr>
                                <td>
                                    <div class="reporter-cell">
                                        <span class="profile-avatar">{{ collect(explode(' ', $account->name))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->join('') }}</span>
                                        <span>
                                            <strong>{{ $account->name }}</strong>
                                            <small>{{ $account->email }}</small>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $account->isAdmin() ? 'text-bg-primary' : ($account->isStaff() ? 'text-bg-info text-dark' : 'text-bg-light border text-dark') }}">
                                        {{ $account->roleLabel() }}
                                    </span>
                                </td>
                                <td>
                                    <strong class="d-block">{{ $account->department?->name ?? '—' }}</strong>
                                    <span class="text-muted-cf small">{{ $account->student_id ?? 'No student ID' }}</span>
                                </td>
                                <td>
                                    @php
                                        $enabled = collect($permissions)->filter(fn ($perm) => $account->hasPermission($perm));
                                    @endphp
                                    @if ($enabled->isEmpty())
                                        <span class="text-muted-cf small">No features enabled</span>
                                    @else
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach ($enabled as $perm)
                                                <span class="badge text-bg-secondary bg-opacity-10 text-dark border">{{ $perm->label() }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if ($account->is_active)
                                        <span class="badge text-bg-success bg-opacity-10 text-success border border-success-subtle">Active</span>
                                    @else
                                        <span class="badge text-bg-danger bg-opacity-10 text-danger border border-danger-subtle">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.users.edit', $account) }}">Manage Access</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <x-pagination :paginator="$users" />
@endsection
