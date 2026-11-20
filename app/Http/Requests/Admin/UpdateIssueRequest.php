<?php

namespace App\Http\Requests\Admin;

use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIssueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'assigned_to' => $this->filled('assigned_to') ? $this->input('assigned_to') : null,
            'admin_note' => $this->filled('admin_note') ? trim((string) $this->input('admin_note')) : null,
            'resolution_note' => $this->filled('resolution_note') ? trim((string) $this->input('resolution_note')) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(IssueStatus::values())],
            'priority' => ['required', 'string', Rule::in(IssuePriority::values())],
            'assigned_to' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('role', UserRole::Admin->value),
            ],
            'admin_note' => ['nullable', 'string', 'max:2000'],
            'resolution_note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'assigned_to.exists' => 'Issues can only be assigned to an authorized administrator or staff member.',
        ];
    }
}
