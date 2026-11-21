<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FeaturePermission;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $admin = $request->user();
        $filters = $request->only(['search', 'role', 'status']);

        $users = User::query()
            ->with('department')
            ->when(! empty($filters['search']), function (Builder $query) use ($filters): void {
                $term = trim((string) $filters['search']);
                $query->where(function (Builder $sub) use ($term): void {
                    $sub->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('student_id', 'like', "%{$term}%");
                });
            })
            ->when(
                ! empty($filters['role']) && in_array($filters['role'], UserRole::values(), true),
                fn (Builder $query) => $query->where('role', $filters['role'])
            )
            ->when(
                ($filters['status'] ?? '') === 'active',
                fn (Builder $query) => $query->where('is_active', true)
            )
            ->when(
                ($filters['status'] ?? '') === 'inactive',
                fn (Builder $query) => $query->where('is_active', false)
            )
            ->orderByRaw("CASE role WHEN 'admin' THEN 1 WHEN 'staff' THEN 2 ELSE 3 END")
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $roles = UserRole::cases();
        $permissions = FeaturePermission::cases();

        return view('admin.users.index', compact('admin', 'users', 'filters', 'roles', 'permissions'));
    }

    public function create(Request $request): View
    {
        $admin = $request->user();
        $departments = Department::active()->orderBy('name')->get();
        $roles = UserRole::cases();
        $permissions = FeaturePermission::cases();
        $roleDefaults = FeaturePermission::defaultsMap();

        return view('admin.users.create', compact(
            'admin',
            'departments',
            'roles',
            'permissions',
            'roleDefaults'
        ));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $permissions = array_values(array_unique($validated['permissions'] ?? []));

        $created = User::create([
            'name' => $validated['name'],
            'role' => $validated['role'],
            'email' => $validated['email'],
            'student_id' => $validated['student_id'],
            'department_id' => $validated['department_id'],
            'phone' => $validated['phone'],
            'password' => $validated['password'],
            'permissions' => $permissions,
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'email_verified_at' => now(),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Account for {$created->name} ({$created->roleLabel()}) has been created.");
    }

    public function edit(Request $request, User $user): View
    {
        $admin = $request->user();
        $managedUser = $user;
        $departments = Department::active()->orderBy('name')->get();
        $roles = UserRole::cases();
        $permissions = FeaturePermission::cases();
        $roleDefaults = FeaturePermission::defaultsMap();

        return view('admin.users.edit', compact(
            'admin',
            'managedUser',
            'departments',
            'roles',
            'permissions',
            'roleDefaults'
        ));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $attributes = [
            'name' => $validated['name'],
            'role' => $validated['role'],
            'email' => $validated['email'],
            'student_id' => $validated['student_id'],
            'department_id' => $validated['department_id'],
            'phone' => $validated['phone'],
            'permissions' => array_values(array_unique($validated['permissions'] ?? [])),
            'is_active' => (bool) $validated['is_active'],
        ];

        if (! empty($validated['password'])) {
            $attributes['password'] = $validated['password'];
        }

        $user->update($attributes);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Access and account settings for {$user->name} have been updated.");
    }
}
