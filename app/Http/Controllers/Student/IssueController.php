<?php

namespace App\Http\Controllers\Student;

use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\FilterStudentIssuesRequest;
use App\Http\Requests\Student\StoreIssueRequest;
use App\Models\Building;
use App\Models\Category;
use App\Models\Issue;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class IssueController extends Controller
{
    public function index(FilterStudentIssuesRequest $request): View
    {
        $student = $request->user();
        $filters = $request->validated();
        $categories = Category::active()->orderBy('name')->get();

        $issues = Issue::query()
            ->where('reporter_id', $student->id)
            ->with(['category', 'location', 'assignee'])
            ->filter($filters)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('student.issues.index', compact('student', 'issues', 'filters', 'categories'));
    }

    public function create(Request $request): View
    {
        $student = $request->user();
        $categories = Category::active()->orderBy('name')->get();
        $buildings = Building::active()
            ->with(['activeLocations'])
            ->orderBy('name')
            ->get();
        $locations = Location::active()
            ->orderBy('room_or_area')
            ->orderBy('name')
            ->get();

        return view('student.issues.create', compact('student', 'categories', 'buildings', 'locations'));
    }

    public function store(StoreIssueRequest $request): RedirectResponse
    {
        $student = $request->user();
        $validated = $request->validated();
        $storedPaths = [];

        try {
            $issue = DB::transaction(function () use ($student, $validated, $request, &$storedPaths): Issue {
                $issue = Issue::create([
                    'reporter_id' => $student->id,
                    'category_id' => $validated['category_id'],
                    'location_id' => $validated['location_id'],
                    'title' => $validated['title'],
                    'description' => $validated['description'],
                    'priority' => $validated['priority'] ?: IssuePriority::Medium->value,
                    'status' => IssueStatus::Submitted->value,
                ]);

                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $file) {
                        $path = $file->store("issues/{$issue->id}", 'local');
                        $storedPaths[] = $path;

                        $issue->attachments()->create([
                            'uploaded_by' => $student->id,
                            'original_name' => $file->getClientOriginalName(),
                            'disk' => 'local',
                            'file_path' => $path,
                            'mime_type' => $file->getMimeType() ?: $file->getClientMimeType() ?: 'application/octet-stream',
                            'size' => $file->getSize() ?: 0,
                        ]);
                    }
                }

                $issue->statusHistories()->create([
                    'actor_id' => $student->id,
                    'old_status' => null,
                    'status' => IssueStatus::Submitted->value,
                    'title' => 'Report submitted',
                    'note' => 'The issue report was received and queued for administrative review.',
                ]);

                return $issue;
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk('local')->delete($path);
            }

            throw $exception;
        }

        return redirect()
            ->route('student.issues.show', $issue)
            ->with('success', "Your issue report ({$issue->reference_number}) has been submitted successfully.");
    }

    public function show(Request $request, Issue $issue): View
    {
        Gate::authorize('view', $issue);

        $student = $request->user();

        $issue->load([
            'category',
            'location',
            'reporter',
            'assignee',
            'attachments',
            'statusHistories.actor',
        ]);

        $resolutionNote = trim((string) $issue->resolution_note);

        $statusHistory = $issue->statusHistories
            ->reject(function ($history) use ($resolutionNote): bool {
                if ($history->title !== 'Administrative notes updated') {
                    return false;
                }

                return $resolutionNote === '' || ! str_contains((string) $history->note, $resolutionNote);
            })
            ->map(function ($history) use ($issue, $resolutionNote) {
                if ($history->actor_id !== $issue->reporter_id) {
                    $publicNote = $resolutionNote !== '' && str_contains((string) $history->note, $resolutionNote)
                        ? $resolutionNote
                        : null;

                    $history->setAttribute('note', $publicNote);

                    if ($history->title === 'Administrative notes updated' && $publicNote !== null) {
                        $history->setAttribute('title', 'Resolution update');
                    }
                }

                return $history;
            })
            ->values();

        $issue->setRelation('statusHistories', $statusHistory);
        unset($issue->admin_note);

        return view('student.issues.show', compact('student', 'issue', 'statusHistory'));
    }
}
