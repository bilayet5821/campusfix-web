<?php

namespace App\Http\Controllers\Admin;

use App\Enums\IssueStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterAdminIssuesRequest;
use App\Http\Requests\Admin\UpdateIssueRequest;
use App\Models\Category;
use App\Models\Issue;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class IssueController extends Controller
{
    public function index(FilterAdminIssuesRequest $request): View
    {
        $admin = $request->user();
        $filters = $request->validated();
        $categories = Category::active()->orderBy('name')->get();

        $issues = Issue::query()
            ->with(['category', 'location', 'reporter', 'assignee'])
            ->filter($filters)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.issues.index', compact('admin', 'issues', 'filters', 'categories'));
    }

    public function show(Request $request, Issue $issue): View
    {
        $admin = $request->user();

        $issue->load([
            'category',
            'location',
            'reporter',
            'assignee',
            'attachments',
            'statusHistories.actor',
        ]);

        $statusHistory = $issue->statusHistories;

        return view('admin.issues.show', compact('admin', 'issue', 'statusHistory'));
    }

    public function edit(Request $request, Issue $issue): View
    {
        $admin = $request->user();

        $issue->load(['category', 'location', 'reporter', 'assignee']);

        $assignees = User::admins()
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        return view('admin.issues.edit', compact('admin', 'issue', 'assignees'));
    }

    public function update(UpdateIssueRequest $request, Issue $issue): RedirectResponse
    {
        $admin = $request->user();
        $validated = $request->validated();

        DB::transaction(function () use ($admin, $issue, $validated): void {
            $oldStatus = $issue->status;
            $oldPriority = $issue->priority;
            $oldAssignedTo = $issue->assigned_to;
            $oldAdminNote = $issue->admin_note;
            $oldResolutionNote = $issue->resolution_note;

            $newStatus = $validated['status'];
            $newPriority = $validated['priority'];
            $newAssignedTo = $validated['assigned_to'] !== null ? (int) $validated['assigned_to'] : null;

            $issue->fill([
                'status' => $newStatus,
                'priority' => $newPriority,
                'assigned_to' => $newAssignedTo,
                'admin_note' => $validated['admin_note'],
                'resolution_note' => $validated['resolution_note'],
            ]);

            if (in_array($newStatus, [IssueStatus::Resolved->value, IssueStatus::Closed->value], true)) {
                $issue->resolved_at ??= now();
            } else {
                $issue->resolved_at = null;
            }

            $issue->save();

            $statusChanged = $oldStatus !== $newStatus;
            $assignmentChanged = $oldAssignedTo !== $newAssignedTo;
            $priorityChanged = $oldPriority !== $newPriority;
            $notesChanged = $oldAdminNote !== $issue->admin_note || $oldResolutionNote !== $issue->resolution_note;

            if ($statusChanged || $assignmentChanged || $priorityChanged || $notesChanged) {
                $statusEnum = IssueStatus::from($newStatus);

                $title = match (true) {
                    $statusChanged => $statusEnum->defaultHistoryTitle(),
                    $assignmentChanged => $newAssignedTo ? 'Issue assigned' : 'Assignment cleared',
                    $priorityChanged => 'Priority updated to '.ucfirst($newPriority),
                    default => 'Administrative notes updated',
                };

                $noteParts = [];

                if ($statusChanged) {
                    $fromLabel = IssueStatus::tryFrom($oldStatus)?->label() ?? ucfirst($oldStatus);
                    $noteParts[] = "Status changed from {$fromLabel} to {$statusEnum->label()}.";
                }

                if ($assignmentChanged) {
                    $assigneeName = $newAssignedTo
                        ? User::find($newAssignedTo)?->name
                        : null;
                    $noteParts[] = $assigneeName
                        ? "Assigned to {$assigneeName}."
                        : 'Removed current assignee.';
                }

                if ($priorityChanged) {
                    $noteParts[] = 'Priority changed from '.ucfirst($oldPriority).' to '.ucfirst($newPriority).'.';
                }

                if (in_array($newStatus, [IssueStatus::Resolved->value, IssueStatus::Closed->value, IssueStatus::Rejected->value], true)
                    && filled($issue->resolution_note)
                    && ($statusChanged || $oldResolutionNote !== $issue->resolution_note)) {
                    $noteParts[] = $issue->resolution_note;
                } elseif (filled($issue->admin_note) && ($statusChanged || $oldAdminNote !== $issue->admin_note)) {
                    $noteParts[] = $issue->admin_note;
                }

                $issue->statusHistories()->create([
                    'actor_id' => $admin->id,
                    'old_status' => $oldStatus,
                    'status' => $newStatus,
                    'title' => $title,
                    'note' => implode(' ', $noteParts) ?: null,
                ]);
            }
        });

        return redirect()
            ->route('admin.issues.show', $issue)
            ->with('success', "Issue {$issue->reference_number} has been updated.");
    }
}
