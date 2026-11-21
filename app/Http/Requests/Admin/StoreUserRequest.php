<?php

namespace App\Http\Requests\Admin;

use App\Enums\FeaturePermission;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
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
            'is_active' => $this->boolean('is_active', true),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'role' => ['required', 'string', Rule::in(UserRole::values())],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'student_id' => [
                'nullable',
                'required_if:role,'.UserRole::Student->value,
                'string',
                'max:40',
                'unique:users,student_id',
            ],
            'department_id' => [
                'nullable',
                'integer',
                Rule::exists('departments', 'id')->where('is_active', true),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(FeaturePermission::values())],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_id.required_if' => 'Student ID is required when creating a student account.',
            'permissions.*.in' => 'One or more selected feature permissions are invalid.',
        ];
    }
}
