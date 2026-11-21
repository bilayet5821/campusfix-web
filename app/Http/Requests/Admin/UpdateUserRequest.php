<?php

namespace App\Http\Requests\Admin;

use App\Enums\FeaturePermission;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission(FeaturePermission::ManageUsers) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => strtolower(trim((string) $this->input('email'))),
            'student_id' => $this->filled('student_id') ? trim((string) $this->input('student_id')) : null,
            'phone' => $this->filled('phone') ? trim((string) $this->input('phone')) : null,
            'department_id' => $this->filled('department_id') ? $this->input('department_id') : null,
            'password' => $this->filled('password') ? $this->input('password') : null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var User $managedUser */
        $managedUser = $this->route('user');

        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'role' => ['required', 'string', Rule::in(UserRole::values())],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($managedUser->id),
            ],
            'student_id' => [
                'nullable',
                'required_if:role,'.UserRole::Student->value,
                'string',
                'max:40',
                Rule::unique('users', 'student_id')->ignore($managedUser->id),
            ],
            'department_id' => [
                'nullable',
                'integer',
                Rule::exists('departments', 'id')->where('is_active', true),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(FeaturePermission::values())],
            'is_active' => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var User $managedUser */
            $managedUser = $this->route('user');
            $actor = $this->user();

            if (! $actor || $managedUser->id !== $actor->id) {
                return;
            }

            if (! $this->boolean('is_active')) {
                $validator->errors()->add('is_active', 'You cannot deactivate your own administrator account.');
            }

            $permissions = $this->input('permissions', []);
            if (! is_array($permissions) || ! in_array(FeaturePermission::ManageUsers->value, $permissions, true)) {
                $validator->errors()->add('permissions', 'You cannot revoke your own User & Access Management permission.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_id.required_if' => 'Student ID is required for a student account.',
            'permissions.*.in' => 'One or more selected feature permissions are invalid.',
        ];
    }
}
