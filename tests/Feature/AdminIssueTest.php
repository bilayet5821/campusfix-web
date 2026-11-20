<?php

namespace Tests\Feature;

use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use App\Models\Category;
use App\Models\Issue;
use App\Models\IssueAttachment;
use App\Models\Location;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminIssueTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_handles_empty_state_and_accurate_distributions(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create();
        $catA = Category::factory()->create(['name' => 'Electrical']);
        $catB = Category::factory()->create(['name' => 'IT & Network']);
        $location = Location::factory()->create();

        // Empty state (no divide-by-zero)
        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('No issue submissions')
            ->assertViewHas('stats', [
                'total' => 0,
                'submitted' => 0,
                'under_review' => 0,
                'in_progress' => 0,
                'resolved' => 0,
                'closed_rejected' => 0,
            ]);

        Issue::factory()->create(['reporter_id' => $student->id, 'category_id' => $catA->id, 'location_id' => $location->id, 'status' => IssueStatus::Submitted->value, 'priority' => IssuePriority::Urgent->value]);
        Issue::factory()->create(['reporter_id' => $student->id, 'category_id' => $catA->id, 'location_id' => $location->id, 'status' => IssueStatus::UnderReview->value, 'priority' => IssuePriority::High->value]);
        Issue::factory()->create(['reporter_id' => $student->id, 'category_id' => $catB->id, 'location_id' => $location->id, 'status' => IssueStatus::InProgress->value, 'priority' => IssuePriority::Medium->value]);
        Issue::factory()->create(['reporter_id' => $student->id, 'category_id' => $catB->id, 'location_id' => $location->id, 'status' => IssueStatus::Resolved->value, 'priority' => IssuePriority::Low->value]);
        Issue::factory()->create(['reporter_id' => $student->id, 'category_id' => $catB->id, 'location_id' => $location->id, 'status' => IssueStatus::Closed->value, 'priority' => IssuePriority::Low->value]);
        Issue::factory()->create(['reporter_id' => $student->id, 'category_id' => $catB->id, 'location_id' => $location->id, 'status' => IssueStatus::Rejected->value, 'priority' => IssuePriority::Low->value]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('stats', [
                'total' => 6,
                'submitted' => 1,
                'under_review' => 1,
                'in_progress' => 1,
                'resolved' => 1,
                'closed_rejected' => 2,
            ])
            ->assertSee('Electrical')
            ->assertSee('IT & Network');
    }

    public function test_admin_cannot_access_student_only_routes(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('student.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('student.issues.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('student.issues.create'))->assertForbidden();
    }

    public function test_admin_can_filter_issues_including_single_sided_date_filters_and_pagination(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create();
        $cat = Category::factory()->create();
        $location = Location::factory()->create();

        $oldIssue = Issue::factory()->create([
            'reporter_id' => $student->id,
            'category_id' => $cat->id,
            'location_id' => $location->id,
            'title' => 'Old HVAC failure',
            'status' => IssueStatus::Resolved->value,
            'priority' => IssuePriority::Low->value,
            'created_at' => now()->subDays(15),
        ]);

        $recentIssue = Issue::factory()->create([
            'reporter_id' => $student->id,
            'category_id' => $cat->id,
            'location_id' => $location->id,
            'title' => 'Urgent lab power outage',
            'status' => IssueStatus::Submitted->value,
            'priority' => IssuePriority::Urgent->value,
            'created_at' => now()->subDay(),
        ]);

        // Only date_to supplied (edge case: must NOT fail after_or_equal:date_from)
        $this->actingAs($admin)
            ->get(route('admin.issues.index', ['date_to' => now()->subDays(5)->toDateString()]))
            ->assertOk()
            ->assertSee($oldIssue->reference_number)
            ->assertDontSee($recentIssue->reference_number);

        // Only date_from supplied
        $this->actingAs($admin)
            ->get(route('admin.issues.index', ['date_from' => now()->subDays(3)->toDateString()]))
            ->assertOk()
            ->assertSee($recentIssue->reference_number)
            ->assertDontSee($oldIssue->reference_number);

        // Invalid range (date_to < date_from) redirects cleanly to admin.issues.index
        $this->actingAs($admin)
            ->get(route('admin.issues.index', [
                'date_from' => '2026-11-20',
                'date_to' => '2026-11-10',
            ]))
            ->assertRedirect(route('admin.issues.index'))
            ->assertSessionHasErrors('date_to');

        // Priority + status filter
        $this->actingAs($admin)
            ->get(route('admin.issues.index', [
                'priority' => IssuePriority::Urgent->value,
                'status' => IssueStatus::Submitted->value,
            ]))
            ->assertOk()
            ->assertSee($recentIssue->reference_number)
            ->assertDontSee($oldIssue->reference_number);
    }

    public function test_admin_can_manage_issue_and_record_status_history_transactionally(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Lead Admin']);
        $assignee = User::factory()->admin()->create(['name' => 'Facilities Lead']);
        $student = User::factory()->student()->create(['name' => 'Student Reporter']);
        $issue = Issue::factory()->create([
            'reporter_id' => $student->id,
            'status' => IssueStatus::Submitted->value,
            'priority' => IssuePriority::Medium->value,
        ]);

        // Edit page lists admins only (never students) in assignees
        $this->actingAs($admin)
            ->get(route('admin.issues.edit', $issue))
            ->assertOk()
            ->assertSee('Facilities Lead')
            ->assertViewHas('assignees', fn ($assignees) => $assignees->every(fn ($u) => $u->isAdmin()));

        // Update workflow -> resolved
        $this->actingAs($admin)
            ->patch(route('admin.issues.update', $issue), [
                'status' => IssueStatus::Resolved->value,
                'priority' => IssuePriority::High->value,
                'assigned_to' => $assignee->id,
                'admin_note' => 'Verified with electrical team.',
                'resolution_note' => 'Replaced faulty breaker and tested circuit.',
            ])
            ->assertRedirect(route('admin.issues.show', $issue))
            ->assertSessionHas('success');

        $issue->refresh();
        $this->assertSame(IssueStatus::Resolved->value, $issue->status);
        $this->assertSame(IssuePriority::High->value, $issue->priority);
        $this->assertSame($assignee->id, $issue->assigned_to);
        $this->assertNotNull($issue->resolved_at);
        $this->assertDatabaseHas('issue_status_histories', [
            'issue_id' => $issue->id,
            'actor_id' => $admin->id,
            'old_status' => IssueStatus::Submitted->value,
            'status' => IssueStatus::Resolved->value,
        ]);

        // Saving again with identical values should NOT spam duplicate history entries
        $historyCount = $issue->statusHistories()->count();
        $this->actingAs($admin)
            ->patch(route('admin.issues.update', $issue), [
                'status' => IssueStatus::Resolved->value,
                'priority' => IssuePriority::High->value,
                'assigned_to' => $assignee->id,
                'admin_note' => 'Verified with electrical team.',
                'resolution_note' => 'Replaced faulty breaker and tested circuit.',
            ])
            ->assertRedirect(route('admin.issues.show', $issue));
        $this->assertSame($historyCount, $issue->statusHistories()->count());

        // Reopening clears resolved_at and allows unassigning
        $this->actingAs($admin)
            ->patch(route('admin.issues.update', $issue), [
                'status' => IssueStatus::InProgress->value,
                'priority' => IssuePriority::High->value,
                'assigned_to' => '',
                'admin_note' => 'Issue recurred; reopening.',
                'resolution_note' => '',
            ])
            ->assertRedirect(route('admin.issues.show', $issue));

        $issue->refresh();
        $this->assertNull($issue->resolved_at);
        $this->assertNull($issue->assigned_to);

        // Cannot assign a student as assignee
        $this->actingAs($admin)
            ->patch(route('admin.issues.update', $issue), [
                'status' => IssueStatus::UnderReview->value,
                'priority' => IssuePriority::Medium->value,
                'assigned_to' => $student->id,
            ])
            ->assertSessionHasErrors('assigned_to');
    }

    public function test_soft_deleted_issue_and_its_attachment_are_not_accessible(): void
    {
        Storage::fake('local');

        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create();
        $issue = Issue::factory()->create(['reporter_id' => $student->id]);

        Storage::disk('local')->put("issues/{$issue->id}/test.jpg", 'fake-bytes');

        $attachment = IssueAttachment::create([
            'issue_id' => $issue->id,
            'uploaded_by' => $student->id,
            'original_name' => 'test.jpg',
            'disk' => 'local',
            'file_path' => "issues/{$issue->id}/test.jpg",
            'mime_type' => 'image/jpeg',
            'size' => 10,
        ]);

        $issue->delete();

        $this->actingAs($student)->get(route('student.issues.show', $issue->id))->assertNotFound();
        $this->actingAs($admin)->get(route('admin.issues.show', $issue->id))->assertNotFound();
        $this->actingAs($student)->get(route('attachments.show', $attachment))->assertForbidden();
        $this->actingAs($admin)->get(route('attachments.show', $attachment))->assertForbidden();
    }

    public function test_database_seeder_is_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $firstCounts = [
            'users' => User::count(),
            'issues' => Issue::count(),
        ];

        $this->seed(DatabaseSeeder::class);
        $this->assertSame($firstCounts['users'], User::count());
        $this->assertSame($firstCounts['issues'], Issue::count());
        $this->assertGreaterThanOrEqual(5, $firstCounts['users']);
        $this->assertGreaterThanOrEqual(9, $firstCounts['issues']);
    }
}
