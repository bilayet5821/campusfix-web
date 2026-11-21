<?php

namespace Tests\Feature;

use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use App\Models\Category;
use App\Models\Issue;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentIssueTest extends TestCase
{
    use RefreshDatabase;

    private function fakePng(string $name = 'evidence.png', int $padBytes = 0): UploadedFile
    {
        // Minimal valid 1x1 transparent PNG
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
        );

        if ($padBytes > 0) {
            $png .= str_repeat("\0", $padBytes);
        }

        return UploadedFile::fake()->createWithContent($name, $png);
    }

    private function fakeJpeg(string $name = 'evidence.jpg'): UploadedFile
    {
        // Minimal valid 1x1 JPEG
        $jpeg = base64_decode(
            '/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA='
        );

        return UploadedFile::fake()->createWithContent($name, $jpeg);
    }

    private function fakeWebp(string $name = 'evidence.webp'): UploadedFile
    {
        // Minimal valid 1x1 lossless WebP
        $webp = base64_decode(
            'UklGRh4AAABXRUJQVlA4TBEAAAAvAAAAAAfQ//73v/+BiOh/AAA='
        );

        return UploadedFile::fake()->createWithContent($name, $webp);
    }

    public function test_student_dashboard_renders_empty_and_populated_stats(): void
    {
        $student = User::factory()->student()->create();
        $otherStudent = User::factory()->student()->create();
        $category = Category::factory()->create();
        $location = Location::factory()->create();

        // Empty state
        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('No reports yet')
            ->assertViewHas('stats', [
                'total' => 0,
                'pending_review' => 0,
                'in_progress' => 0,
                'resolved' => 0,
            ]);

        // Populate issues across statuses + another student's issue (must not be counted)
        Issue::factory()->create(['reporter_id' => $student->id, 'category_id' => $category->id, 'location_id' => $location->id, 'status' => IssueStatus::Submitted->value]);
        Issue::factory()->create(['reporter_id' => $student->id, 'category_id' => $category->id, 'location_id' => $location->id, 'status' => IssueStatus::UnderReview->value]);
        Issue::factory()->create(['reporter_id' => $student->id, 'category_id' => $category->id, 'location_id' => $location->id, 'status' => IssueStatus::InProgress->value]);
        Issue::factory()->create(['reporter_id' => $student->id, 'category_id' => $category->id, 'location_id' => $location->id, 'status' => IssueStatus::Resolved->value]);
        Issue::factory()->create(['reporter_id' => $student->id, 'category_id' => $category->id, 'location_id' => $location->id, 'status' => IssueStatus::Closed->value]);
        Issue::factory()->create(['reporter_id' => $otherStudent->id, 'category_id' => $category->id, 'location_id' => $location->id, 'status' => IssueStatus::Submitted->value]);

        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertViewHas('stats', [
                'total' => 5,
                'pending_review' => 2,
                'in_progress' => 1,
                'resolved' => 2,
            ]);
    }

    public function test_student_cannot_access_admin_routes(): void
    {
        $student = User::factory()->student()->create();
        $issue = Issue::factory()->create(['reporter_id' => $student->id]);

        $this->actingAs($student)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($student)->get(route('admin.issues.index'))->assertForbidden();
        $this->actingAs($student)->get(route('admin.issues.show', $issue))->assertForbidden();
        $this->actingAs($student)->get(route('admin.issues.edit', $issue))->assertForbidden();
        $this->actingAs($student)->patch(route('admin.issues.update', $issue), [
            'status' => IssueStatus::Resolved->value,
            'priority' => IssuePriority::Low->value,
        ])->assertForbidden();
    }

    public function test_student_can_create_issue_with_images_and_authorized_attachment_access(): void
    {
        Storage::fake('local');

        $student = User::factory()->student()->create();
        $otherStudent = User::factory()->student()->create();
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create(['name' => 'Computer Lab']);
        $location = Location::factory()->create(['room_or_area' => 'Lab C-302']);
        $building = $location->campusBuilding;

        $this->actingAs($student)
            ->get(route('student.issues.create'))
            ->assertOk()
            ->assertSee('Computer Lab')
            ->assertSee($building->name)
            ->assertSee('Lab C-302');

        $response = $this->actingAs($student)->post(route('student.issues.store'), [
            'title' => 'Lab PC-09 fails to boot into OS',
            'category_id' => $category->id,
            'building_id' => $building->id,
            'location_id' => $location->id,
            'description' => 'Desktop computer PC-09 turns on with fan noise but remains stuck on a black screen before BIOS.',
            'priority' => '',
            'status' => IssueStatus::Resolved->value, // Must be ignored
            'attachments' => [
                $this->fakeJpeg('screen.jpg'),
                $this->fakePng('tower.png'),
            ],
            'accuracy_confirmed' => '1',
        ]);

        $issue = Issue::firstOrFail();

        $response->assertRedirect(route('student.issues.show', $issue))
            ->assertSessionHas('success');

        $this->assertSame($student->id, $issue->reporter_id);
        $this->assertSame(IssueStatus::Submitted->value, $issue->status);
        $this->assertSame(IssuePriority::Medium->value, $issue->priority);
        $this->assertStringStartsWith('CFX-', $issue->reference_number);
        $this->assertCount(2, $issue->attachments);
        $this->assertCount(1, $issue->statusHistories);

        $attachment = $issue->attachments->first();
        Storage::disk('local')->assertExists($attachment->file_path);

        // Detail page renders safely without leaking raw filesystem path
        $this->actingAs($student)
            ->get(route('student.issues.show', $issue))
            ->assertOk()
            ->assertSee($issue->reference_number)
            ->assertSee('screen.jpg')
            ->assertSee($attachment->url)
            ->assertDontSee($attachment->file_path);

        // Owner can download attachment
        $this->actingAs($student)
            ->get($attachment->url)
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        // Admin can download attachment
        $this->actingAs($admin)
            ->get($attachment->url)
            ->assertOk();

        // Another student is blocked from viewing issue AND downloading attachment
        $this->actingAs($otherStudent)
            ->get(route('student.issues.show', $issue))
            ->assertForbidden();

        $this->actingAs($otherStudent)
            ->get($attachment->url)
            ->assertForbidden();
    }

    public function test_issue_creation_rejects_invalid_fields_and_unsafe_or_excess_uploads(): void
    {
        Storage::fake('local');

        $student = User::factory()->student()->create();
        $category = Category::factory()->create();
        $location = Location::factory()->create();
        $inactiveCategory = Category::factory()->create(['is_active' => false]);

        // Basic field validation
        $this->actingAs($student)->post(route('student.issues.store'), [
            'title' => 'Hi',
            'category_id' => $inactiveCategory->id,
            'building_id' => 99999,
            'location_id' => 99999,
            'description' => 'Too short',
            'priority' => 'super_urgent',
        ])->assertSessionHasErrors([
            'title',
            'category_id',
            'building_id',
            'location_id',
            'description',
            'priority',
            'accuracy_confirmed',
        ]);

        // Reject non-image upload (.php / .pdf)
        $this->actingAs($student)->post(route('student.issues.store'), [
            'title' => 'Valid issue title here',
            'category_id' => $category->id,
            'building_id' => $location->building_id,
            'location_id' => $location->id,
            'description' => 'This description is long enough to satisfy the twenty character minimum requirement.',
            'attachments' => [
                UploadedFile::fake()->create('exploit.php', 10, 'application/x-httpd-php'),
            ],
            'accuracy_confirmed' => '1',
        ])->assertSessionHasErrors('attachments.0');

        // Reject oversized image (> 5 MB)
        $this->actingAs($student)->post(route('student.issues.store'), [
            'title' => 'Valid issue title here',
            'category_id' => $category->id,
            'building_id' => $location->building_id,
            'location_id' => $location->id,
            'description' => 'This description is long enough to satisfy the twenty character minimum requirement.',
            'attachments' => [
                $this->fakePng('huge.png', 5_300_000),
            ],
            'accuracy_confirmed' => '1',
        ])->assertSessionHasErrors('attachments.0');

        // Reject more than 5 attachments
        $sixImages = collect(range(1, 6))
            ->map(fn ($i) => $this->fakePng("img{$i}.png"))
            ->all();

        $this->actingAs($student)->post(route('student.issues.store'), [
            'title' => 'Valid issue title here',
            'category_id' => $category->id,
            'building_id' => $location->building_id,
            'location_id' => $location->id,
            'description' => 'This description is long enough to satisfy the twenty character minimum requirement.',
            'attachments' => $sixImages,
            'accuracy_confirmed' => '1',
        ])->assertSessionHasErrors('attachments');

        $this->assertDatabaseCount('issues', 0);
    }

    public function test_student_show_page_displays_admin_note_when_resolution_note_is_null(): void
    {
        $student = User::factory()->student()->create();
        $issue = Issue::factory()->create([
            'reporter_id' => $student->id,
            'admin_note' => 'Facilities team scheduled room inspection at 2 PM.',
            'resolution_note' => null,
        ]);

        $this->actingAs($student)
            ->get(route('student.issues.show', $issue))
            ->assertOk()
            ->assertSee('Administrator note')
            ->assertSee('Facilities team scheduled room inspection at 2 PM.');
    }

    public function test_student_issue_list_filters_paginates_and_isolates_other_students(): void
    {
        $student = User::factory()->student()->create();
        $otherStudent = User::factory()->student()->create();
        $catElectrical = Category::factory()->create(['name' => 'Electrical']);
        $catNetwork = Category::factory()->create(['name' => 'IT & Network']);
        $location = Location::factory()->create();

        $match = Issue::factory()->create([
            'reporter_id' => $student->id,
            'category_id' => $catElectrical->id,
            'location_id' => $location->id,
            'title' => 'Broken corridor switchboard',
            'status' => IssueStatus::InProgress->value,
        ]);

        $nonMatch = Issue::factory()->create([
            'reporter_id' => $student->id,
            'category_id' => $catNetwork->id,
            'location_id' => $location->id,
            'title' => 'Slow Wi-Fi on 4th floor',
            'status' => IssueStatus::Submitted->value,
        ]);

        $otherIssue = Issue::factory()->create([
            'reporter_id' => $otherStudent->id,
            'category_id' => $catElectrical->id,
            'location_id' => $location->id,
            'title' => 'Broken corridor switchboard private',
            'status' => IssueStatus::InProgress->value,
        ]);

        // Combined filter matches only the student's own matching issue
        $this->actingAs($student)
            ->get(route('student.issues.index', [
                'search' => 'switchboard',
                'status' => IssueStatus::InProgress->value,
                'category' => $catElectrical->id,
            ]))
            ->assertOk()
            ->assertSee($match->reference_number)
            ->assertDontSee($nonMatch->reference_number)
            ->assertDontSee($otherIssue->reference_number);

        // Search by reference number
        $this->actingAs($student)
            ->get(route('student.issues.index', ['search' => $nonMatch->reference_number]))
            ->assertOk()
            ->assertSee($nonMatch->title)
            ->assertDontSee($match->title);

        // Invalid filter rejected and redirects cleanly to student.issues.index (no redirect loop)
        $this->actingAs($student)
            ->get(route('student.issues.index', ['status' => 'not_a_status']))
            ->assertRedirect(route('student.issues.index'))
            ->assertSessionHasErrors('status');
    }

    public function test_spoofed_image_extension_is_rejected_and_webp_is_accepted(): void
    {
        Storage::fake('local');

        $student = User::factory()->student()->create();
        $category = Category::factory()->create();
        $location = Location::factory()->create();

        // Spoofed .jpg containing PHP code must fail image/mimetype validation
        $this->actingAs($student)->post(route('student.issues.store'), [
            'title' => 'Spoofed upload security test',
            'category_id' => $category->id,
            'building_id' => $location->building_id,
            'location_id' => $location->id,
            'description' => 'Testing that a PHP file renamed with a .jpg extension is rejected on the server.',
            'attachments' => [
                UploadedFile::fake()->createWithContent('shell.jpg', '<?php echo "pwned"; ?>'),
            ],
            'accuracy_confirmed' => '1',
        ])->assertSessionHasErrors('attachments.0');

        // Valid WebP image succeeds
        $this->actingAs($student)->post(route('student.issues.store'), [
            'title' => 'Valid WebP evidence upload test',
            'category_id' => $category->id,
            'building_id' => $location->building_id,
            'location_id' => $location->id,
            'description' => 'Uploading a genuine WebP image file should be accepted and stored safely.',
            'attachments' => [
                $this->fakeWebp('photo.webp'),
            ],
            'accuracy_confirmed' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('issue_attachments', [
            'original_name' => 'photo.webp',
        ]);
    }

    public function test_xss_payloads_are_escaped_and_sql_like_search_is_safe(): void
    {
        $student = User::factory()->student()->create();
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $location = Location::factory()->create();

        $xss = '<script>alert("xss")</script>';

        $this->actingAs($student)->post(route('student.issues.store'), [
            'title' => "Broken switch {$xss}",
            'category_id' => $category->id,
            'building_id' => $location->building_id,
            'location_id' => $location->id,
            'description' => "Detailed issue report containing unsafe HTML {$xss} to verify Blade escaping.",
            'accuracy_confirmed' => '1',
        ])->assertRedirect();

        $issue = Issue::firstOrFail();

        $this->actingAs($student)
            ->get(route('student.issues.show', $issue))
            ->assertOk()
            ->assertDontSee($xss, false)
            ->assertSee(e($xss), false);

        $this->actingAs($admin)
            ->get(route('admin.issues.show', $issue))
            ->assertOk()
            ->assertDontSee($xss, false)
            ->assertSee(e($xss), false);

        // SQL-injection-style search must not error or leak rows
        $this->actingAs($student)
            ->get(route('student.issues.index', ['search' => "' OR '1'='1"]))
            ->assertOk()
            ->assertSee('No reports found')
            ->assertDontSee($issue->reference_number);
    }

    public function test_pagination_retains_query_string_and_handles_out_of_range_pages(): void
    {
        $student = User::factory()->student()->create();
        $category = Category::factory()->create();
        $location = Location::factory()->create();

        Issue::factory()->count(12)->create([
            'reporter_id' => $student->id,
            'category_id' => $category->id,
            'location_id' => $location->id,
            'status' => IssueStatus::Submitted->value,
        ]);

        $this->actingAs($student)
            ->get(route('student.issues.index', ['status' => IssueStatus::Submitted->value]))
            ->assertOk()
            ->assertSee('Page 1 of 2')
            ->assertSee('status=submitted');

        $this->actingAs($student)
            ->get(route('student.issues.index', ['status' => IssueStatus::Submitted->value, 'page' => 2]))
            ->assertOk()
            ->assertSee('Page 2 of 2');

        $this->actingAs($student)
            ->get(route('student.issues.index', ['page' => 999]))
            ->assertOk()
            ->assertSee('No reports found');
    }

    public function test_missing_attachment_file_on_disk_returns_404(): void
    {
        Storage::fake('local');

        $student = User::factory()->student()->create();
        $issue = Issue::factory()->create(['reporter_id' => $student->id]);
        $attachment = $issue->attachments()->create([
            'uploaded_by' => $student->id,
            'original_name' => 'missing.png',
            'disk' => 'local',
            'file_path' => "issues/{$issue->id}/does-not-exist.png",
            'mime_type' => 'image/png',
            'size' => 128,
        ]);

        $this->actingAs($student)
            ->get(route('attachments.show', $attachment))
            ->assertNotFound();
    }
}
