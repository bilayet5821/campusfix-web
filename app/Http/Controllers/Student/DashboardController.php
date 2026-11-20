<?php

namespace App\Http\Controllers\Student;

use App\Enums\IssueStatus;
use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\IssueStatusHistory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $student = $request->user();

        $statusCounts = Issue::query()
            ->where('reporter_id', $student->id)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $stats = [
            'total' => (int) $statusCounts->sum(),
            'pending_review' => (int) ($statusCounts[IssueStatus::Submitted->value] ?? 0)
                + (int) ($statusCounts[IssueStatus::UnderReview->value] ?? 0),
            'in_progress' => (int) ($statusCounts[IssueStatus::InProgress->value] ?? 0),
            'resolved' => (int) ($statusCounts[IssueStatus::Resolved->value] ?? 0)
                + (int) ($statusCounts[IssueStatus::Closed->value] ?? 0),
        ];

        $recentIssues = Issue::query()
            ->where('reporter_id', $student->id)
            ->with(['category', 'location', 'assignee'])
            ->latest()
            ->limit(5)
            ->get();

        $recentActivity = IssueStatusHistory::query()
            ->whereHas('issue', fn ($query) => $query->where('reporter_id', $student->id))
            ->with(['issue:id,reference_number,title', 'actor:id,name'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get()
            ->map(function (IssueStatusHistory $entry): object {
                $statusLabel = IssueStatus::tryFrom($entry->status)?->label()
                    ?? str($entry->status)->replace('_', ' ')->title()->toString();
                $ref = $entry->issue?->reference_number ?? 'Issue';

                return (object) [
                    'title' => $entry->title ?: $statusLabel,
                    'description' => $entry->note
                        ? "{$ref}: {$entry->note}"
                        : "{$ref} moved to {$statusLabel}.",
                    'created_at' => $entry->created_at,
                ];
            });

        return view('student.dashboard', compact('student', 'stats', 'recentIssues', 'recentActivity'));
    }
}
