<?php

namespace App\Http\Requests\Admin;

use App\Enums\FeaturePermission;
use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterAdminIssuesRequest extends FormRequest
{
    protected $redirectRoute = 'admin.issues.index';

    public function authorize(): bool
    {
        return $this->user()?->hasPermission(FeaturePermission::ManageIssues) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', Rule::in(IssueStatus::values())],
            'priority' => ['nullable', 'string', Rule::in(IssuePriority::values())],
            'category' => ['nullable', 'integer', 'exists:categories,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => array_values(array_filter([
                'nullable',
                'date',
                $this->filled('date_from') ? 'after_or_equal:date_from' : null,
            ])),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date_to.after_or_equal' => 'The end date must be on or after the start date.',
        ];
    }
}
