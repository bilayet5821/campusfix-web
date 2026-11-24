<?php

namespace App\Http\Requests\Admin;

use App\Enums\FeaturePermission;
use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIssueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission(FeaturePermission::ManageIssues) ?? false;
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
                function (string $attribute, mixed $value, Closure $fail): void {
                    if ($value === null || $value === '') {
                        return;
                    }

                    $exists = User::assignable()->whereKey((int) $value)->exists();
                    if (! $exists) {
                        $fail('Issues can only be assigned to an active administrator, staff member, or authorized issue manager.');
                    }
                },
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
