<?php

namespace App\Http\Controllers\Admin;

use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Issue;
use App\Models\IssueStatusHistory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $admin = $request->user();

        $statusCounts = Issue::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $total = (int) $statusCounts->sum();

        $stats = [
            'total' => $total,
            'submitted' => (int) ($statusCounts[IssueStatus::Submitted->value] ?? 0),
            'under_review' => (int) ($statusCounts[IssueStatus::UnderReview->value] ?? 0),
            'in_progress' => (int) ($statusCounts[IssueStatus::InProgress->value] ?? 0),
            'resolved' => (int) ($statusCounts[IssueStatus::Resolved->value] ?? 0),
            'closed_rejected' => (int) ($statusCounts[IssueStatus::Closed->value] ?? 0)
                + (int) ($statusCounts[IssueStatus::Rejected->value] ?? 0),
        ];

        $recentIssues = Issue::query()
            ->with(['category', 'location', 'reporter', 'assignee'])
            ->latest()
            ->limit(5)
            ->get();

        $statusDistribution = collect(IssueStatus::cases())->map(function (IssueStatus $status) use ($statusCounts, $total): object {
            $count = (int) ($statusCounts[$status->value] ?? 0);

            return (object) [
                'label' => $status->label(),
                'count' => $count,
                'percentage' => $total > 0 ? (int) round(($count / $total) * 100) : 0,
            ];
        })->filter(fn (object $item): bool => $item->count > 0 || $total === 0)->values();

        if ($total === 0) {
            $statusDistribution = collect();
        }

        $priorityCounts = Issue::query()
            ->selectRaw('priority, COUNT(*) as aggregate')
            ->groupBy('priority')
            ->pluck('aggregate', 'priority');

        $priorityOrder = [
            IssuePriority::Urgent,
            IssuePriority::High,
            IssuePriority::Medium,
            IssuePriority::Low,
        ];

        $priorityDistribution = $total === 0
            ? collect()
            : collect($priorityOrder)->map(function (IssuePriority $priority) use ($priorityCounts, $total): object {
                $count = (int) ($priorityCounts[$priority->value] ?? 0);

                return (object) [
                    'label' => $priority->label(),
                    'count' => $count,
                    'percentage' => (int) round(($count / $total) * 100),
                ];
            })->filter(fn (object $item): bool => $item->count > 0)->values();

        $categoryDistribution = $total === 0
            ? collect()
            : Category::query()
                ->whereHas('issues')
                ->withCount('issues')
                ->orderByDesc('issues_count')
                ->limit(6)
                ->get()
                ->map(fn (Category $category): object => (object) [
                    'label' => $category->name,
                    'count' => (int) $category->issues_count,
                    'percentage' => (int) round(($category->issues_count / $total) * 100),
                ]);

        $recentActivity = IssueStatusHistory::query()
            ->with(['issue:id,reference_number,title', 'actor:id,name'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get()
            ->map(function (IssueStatusHistory $entry): object {
                $statusLabel = IssueStatus::tryFrom($entry->status)?->label()
                    ?? str($entry->status)->replace('_', ' ')->title()->toString();
                $ref = $entry->issue?->reference_number ?? 'Issue';
                $actorName = $entry->actor?->name;

                $description = $entry->note
                    ? "{$ref}: {$entry->note}"
                    : "{$ref} status updated to {$statusLabel}.";

                if ($actorName) {
                    $description .= " ({$actorName})";
                }

                return (object) [
                    'title' => $entry->title ?: $statusLabel,
                    'description' => $description,
                    'created_at' => $entry->created_at,
                ];
            });

        return view('admin.dashboard', compact(
            'admin',
            'stats',
            'recentIssues',
            'statusDistribution',
            'priorityDistribution',
            'categoryDistribution',
            'recentActivity'
        ));
    }
}
