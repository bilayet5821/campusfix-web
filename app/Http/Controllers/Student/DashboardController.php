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
            ->with(['issue:id,reference_number,title,reporter_id,resolution_note', 'actor:id,name'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(30)
            ->get()
            ->reject(function (IssueStatusHistory $entry): bool {
                $resolutionNote = trim((string) $entry->issue?->resolution_note);

                if ($entry->title !== 'Administrative notes updated') {
                    return false;
                }

                return $resolutionNote === '' || ! str_contains((string) $entry->note, $resolutionNote);
            })
            ->map(function (IssueStatusHistory $entry): object {
                $statusLabel = IssueStatus::tryFrom($entry->status)?->label()
                    ?? str($entry->status)->replace('_', ' ')->title()->toString();
                $ref = $entry->issue?->reference_number ?? 'Issue';
                $resolutionNote = trim((string) $entry->issue?->resolution_note);
                $note = $entry->actor_id === $entry->issue?->reporter_id
                    ? $entry->note
                    : ($resolutionNote !== '' && str_contains((string) $entry->note, $resolutionNote)
                        ? $resolutionNote
                        : null);

                return (object) [
                    'title' => $entry->title === 'Administrative notes updated'
                        ? 'Resolution update'
                        : ($entry->title ?: $statusLabel),
                    'description' => $note
                        ? "{$ref}: {$note}"
                        : "{$ref} moved to {$statusLabel}.",
                    'created_at' => $entry->created_at,
                ];
            })
            ->take(6)
            ->values();

        return view('student.dashboard', compact('student', 'stats', 'recentIssues', 'recentActivity'));
    }
}
