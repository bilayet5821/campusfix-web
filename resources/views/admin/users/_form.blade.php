@php
    $isEdit = isset($managedUser);
    $selectedRole = old('role', $isEdit ? $managedUser->role : 'student');
    $selectedPermissions = old('permissions', $isEdit ? ($managedUser->permissions ?? []) : ($roleDefaults[$selectedRole] ?? []));
    $isActiveChecked = old('is_active', $isEdit ? ($managedUser->is_active ? '1' : '0') : '1');
    $groupedPermissions = collect($permissions)->groupBy(fn ($perm) => $perm->group());
@endphp

<div class="report-form-section">
    <h2 class="h5 mb-4">Account details</h2>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="name">Full name <span class="required-mark">*</span></label>
            <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" type="text" value="{{ old('name', $isEdit ? $managedUser->name : '') }}" maxlength="120" required>
            <x-form-error field="name" />
        </div>

        <div class="col-md-6">
            <label class="form-label" for="role">Account type <span class="required-mark">*</span></label>
            <select
                class="form-select @error('role') is-invalid @enderror"
                id="role"
                name="role"
                data-role-defaults='@json($roleDefaults)'
                data-auto-apply-defaults="{{ $isEdit ? 'false' : 'true' }}"
                required
            >
                @foreach ($roles as $role)
                    <option value="{{ $role->value }}" @selected($selectedRole === $role->value)>{{ $role->label() }}</option>
                @endforeach
            </select>
            <x-form-error field="role" />
        </div>

        <div class="col-md-6">
            <label class="form-label" for="email">Email address <span class="required-mark">*</span></label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email', $isEdit ? $managedUser->email : '') }}" required>
            <x-form-error field="email" />
        </div>

        <div class="col-md-6">
            <label class="form-label" for="student_id">
                Student ID
                <span class="required-mark {{ $selectedRole === 'student' ? '' : 'd-none' }}" data-student-id-required>*</span>
            </label>
            <input class="form-control @error('student_id') is-invalid @enderror" id="student_id" name="student_id" type="text" value="{{ old('student_id', $isEdit ? $managedUser->student_id : '') }}" maxlength="40" placeholder="Required for student accounts">
            <x-form-error field="student_id" />
        </div>

        <div class="col-md-6">
            <label class="form-label" for="department_id">Department <span class="text-muted-cf fw-normal">(optional)</span></label>
            <select class="form-select @error('department_id') is-invalid @enderror" id="department_id" name="department_id">
                <option value="">No department</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}" @selected((string) old('department_id', $isEdit ? $managedUser->department_id : '') === (string) $department->id)>
                        {{ $department->name }}
                    </option>
                @endforeach
            </select>
            <x-form-error field="department_id" />
        </div>

        <div class="col-md-6">
            <label class="form-label" for="phone">Phone number <span class="text-muted-cf fw-normal">(optional)</span></label>
            <input class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" type="tel" value="{{ old('phone', $isEdit ? $managedUser->phone : '') }}" maxlength="30">
            <x-form-error field="phone" />
        </div>

        <div class="col-md-6">
            <label class="form-label" for="password">
                {{ $isEdit ? 'New password' : 'Password' }}
                @unless ($isEdit)<span class="required-mark">*</span>@else<span class="text-muted-cf fw-normal">(leave blank to keep current)</span>@endunless
            </label>
            <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" autocomplete="new-password" minlength="8" @required(!$isEdit)>
            <div class="form-hint mt-1">Minimum 8 characters.</div>
            <x-form-error field="password" />
        </div>

        <div class="col-md-6">
            <label class="form-label" for="password_confirmation">
                Confirm password
                @unless ($isEdit)<span class="required-mark">*</span>@endunless
            </label>
            <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" @required(!$isEdit)>
        </div>

        <div class="col-12">
            <div class="form-check form-switch">
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input @error('is_active') is-invalid @enderror" id="is_active" name="is_active" type="checkbox" value="1" @checked((string) $isActiveChecked === '1')>
                <label class="form-check-label" for="is_active">Account is active (uncheck to suspend login access)</label>
                <x-form-error field="is_active" />
            </div>
        </div>
    </div>
</div>

<div class="report-form-section">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h2 class="h5 mb-1">Feature access permissions</h2>
            <p class="text-muted-cf small mb-0">Grant or revoke specific CampusFix features for this user, regardless of account type.</p>
        </div>
        <button class="btn btn-sm btn-outline-primary" type="button" data-apply-role-defaults>Apply Role Defaults</button>
    </div>

    @error('permissions')
        <div class="text-danger small mb-3">{{ $message }}</div>
    @enderror

    @foreach ($groupedPermissions as $groupName => $groupItems)
        <div class="mb-4">
            <h3 class="h6 text-muted-cf text-uppercase small fw-bold mb-2">{{ $groupName }}</h3>
            <div class="row g-3">
                @foreach ($groupItems as $permission)
                    <div class="col-md-6">
                        <label class="d-flex align-items-start gap-2 p-3 border rounded-3 h-100 bg-light-subtle" for="perm-{{ $permission->value }}" style="cursor: pointer;">
                            <input
                                class="form-check-input mt-1 flex-shrink-0"
                                id="perm-{{ $permission->value }}"
                                name="permissions[]"
                                type="checkbox"
                                value="{{ $permission->value }}"
                                @checked(in_array($permission->value, $selectedPermissions, true))
                            >
                            <span>
                                <strong class="d-block small">{{ $permission->label() }}</strong>
                                <span class="text-muted-cf small">{{ $permission->description() }}</span>
                            </span>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="d-flex justify-content-end gap-2 pt-2">
        <a class="btn btn-outline-primary" href="{{ route('admin.users.index') }}">Cancel</a>
        <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
    </div>
</div>
