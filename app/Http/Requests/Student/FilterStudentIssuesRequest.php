<?php

namespace App\Http\Requests\Student;

use App\Enums\IssueStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterStudentIssuesRequest extends FormRequest
{
    protected $redirectRoute = 'student.issues.index';

    public function authorize(): bool
    {
        return $this->user()?->isStudent() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', Rule::in(IssueStatus::values())],
            'category' => ['nullable', 'integer', 'exists:categories,id'],
        ];
    }
}
