<?php

namespace Database\Seeders;

use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use App\Models\Category;
use App\Models\Issue;
use App\Models\IssueStatusHistory;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoIssueSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('Demo issues were not seeded because the application is not running locally or in testing.');

            return;
        }

        $student1 = User::where('email', 'student@campusfix.edu')->first();
        $student2 = User::where('email', 'student2@campusfix.edu')->first();
        $leadAdmin = User::where('email', 'admin@campusfix.edu')->first();
        $facilitiesAdmin = User::where('email', 'facilities@campusfix.edu')->first();
        $itAdmin = User::where('email', 'itsupport@campusfix.edu')->first();

        if (! $student1 || ! $student2 || ! $leadAdmin) {
            return;
        }

        $categories = Category::pluck('id', 'name');
        $locations = Location::pluck('id', 'name');

        $demoIssues = [
            [
                'reference_number' => 'CFX-2026-1001',
                'reporter_id' => $student1->id,
                'category' => 'Classroom Facilities',
                'location' => 'Knowledge Tower — Room KT-402',
                'assigned_to' => $facilitiesAdmin?->id,
                'title' => 'Projector does not display HDMI input in Room KT-402',
                'description' => 'The ceiling-mounted projector powers on normally, but it shows "No Signal" when connected to the lectern desktop or faculty laptops via HDMI. Tested with two different cables before the 10:00 AM lecture.',
                'priority' => IssuePriority::High->value,
                'status' => IssueStatus::InProgress->value,
                'admin_note' => 'Facilities team inspected the lectern HDMI switch and ordered a replacement wall-plate adapter.',
                'resolution_note' => null,
                'days_ago' => 3,
                'history' => [
                    ['status' => IssueStatus::Submitted->value, 'title' => 'Report submitted', 'note' => 'Issue report submitted for administrative review.', 'actor' => $student1, 'hours_after' => 0],
                    ['status' => IssueStatus::UnderReview->value, 'title' => 'Under administrative review', 'note' => 'Verified lecture schedule and escalated to Facilities Support.', 'actor' => $leadAdmin, 'hours_after' => 6],
                    ['status' => IssueStatus::InProgress->value, 'title' => 'Work in progress', 'note' => 'Technician tested the ceiling unit and is replacing the HDMI wall coupler.', 'actor' => $facilitiesAdmin, 'hours_after' => 28],
                ],
            ],
            [
                'reference_number' => 'CFX-2026-1002',
                'reporter_id' => $student1->id,
                'category' => 'IT & Network',
                'location' => 'Knowledge Tower — Computer Lab C-302',
                'assigned_to' => $itAdmin?->id,
                'title' => 'Frequent Wi-Fi drops near Computer Lab C-302 entrance',
                'description' => 'Campus Wi-Fi disconnects every 5 to 10 minutes on the 3rd-floor corridor outside Computer Lab C-302, making it difficult to access online lab portals during lab sessions.',
                'priority' => IssuePriority::Medium->value,
                'status' => IssueStatus::UnderReview->value,
                'admin_note' => 'Assigned to IT Support to check access point AP-KT3-02 signal logs.',
                'resolution_note' => null,
                'days_ago' => 5,
                'history' => [
                    ['status' => IssueStatus::Submitted->value, 'title' => 'Report submitted', 'note' => 'Issue report submitted for administrative review.', 'actor' => $student1, 'hours_after' => 0],
                    ['status' => IssueStatus::UnderReview->value, 'title' => 'Under administrative review', 'note' => 'Assigned to IT Support Coordinator for access point diagnostics.', 'actor' => $leadAdmin, 'hours_after' => 14],
                ],
            ],
            [
                'reference_number' => 'CFX-2026-1003',
                'reporter_id' => $student1->id,
                'category' => 'Electrical',
                'location' => 'Central Library — East Reading Area',
                'assigned_to' => $facilitiesAdmin?->id,
                'title' => 'Flickering LED panel above east reading tables',
                'description' => 'Two overhead LED panels above the east study tables flicker continuously in the afternoon, causing eye strain for students preparing for exams.',
                'priority' => IssuePriority::Low->value,
                'status' => IssueStatus::Resolved->value,
                'admin_note' => 'Scheduled maintenance outside peak afternoon study hours.',
                'resolution_note' => 'Both faulty LED driver units were replaced and the lighting fixture was tested for stability.',
                'days_ago' => 10,
                'history' => [
                    ['status' => IssueStatus::Submitted->value, 'title' => 'Report submitted', 'note' => 'Issue report submitted for administrative review.', 'actor' => $student1, 'hours_after' => 0],
                    ['status' => IssueStatus::InProgress->value, 'title' => 'Work in progress', 'note' => 'Electrical maintenance team began replacing the LED drivers.', 'actor' => $facilitiesAdmin, 'hours_after' => 24],
                    ['status' => IssueStatus::Resolved->value, 'title' => 'Issue resolved', 'note' => 'Faulty LED drivers replaced and verified.', 'actor' => $facilitiesAdmin, 'hours_after' => 48],
                ],
            ],
            [
                'reference_number' => 'CFX-2026-1004',
                'reporter_id' => $student1->id,
                'category' => 'Computer Lab',
                'location' => 'Knowledge Tower — Software Lab C-508',
                'assigned_to' => null,
                'title' => 'Workstation PC-14 does not power on in Software Lab C-508',
                'description' => 'Desktop computer labeled PC-14 in the third row shows no power indicator light even though the wall socket works with the monitor.',
                'priority' => IssuePriority::Medium->value,
                'status' => IssueStatus::Submitted->value,
                'admin_note' => null,
                'resolution_note' => null,
                'days_ago' => 1,
                'history' => [
                    ['status' => IssueStatus::Submitted->value, 'title' => 'Report submitted', 'note' => 'Issue report submitted for administrative review.', 'actor' => $student1, 'hours_after' => 0],
                ],
            ],
            [
                'reference_number' => 'CFX-2026-1005',
                'reporter_id' => $student2->id,
                'category' => 'Safety & Maintenance',
                'location' => 'Academic Building 2 — EEE Lab AB2-305',
                'assigned_to' => $facilitiesAdmin?->id,
                'title' => 'Loose power strip bracket at bench 4 in EEE Lab AB2-305',
                'description' => 'The mounted power distribution strip along bench 4 has detached on one side and sparks slightly if moved while lab instruments are plugged in.',
                'priority' => IssuePriority::Urgent->value,
                'status' => IssueStatus::InProgress->value,
                'admin_note' => 'Bench 4 power isolated immediately; electrician dispatched for priority replacement.',
                'resolution_note' => null,
                'days_ago' => 2,
                'history' => [
                    ['status' => IssueStatus::Submitted->value, 'title' => 'Report submitted', 'note' => 'Issue report submitted for administrative review.', 'actor' => $student2, 'hours_after' => 0],
                    ['status' => IssueStatus::InProgress->value, 'title' => 'Urgent inspection started', 'note' => 'Marked urgent and assigned to Facilities Coordinator immediately.', 'actor' => $leadAdmin, 'hours_after' => 2],
                ],
            ],
            [
                'reference_number' => 'CFX-2026-1006',
                'reporter_id' => $student2->id,
                'category' => 'Water & Plumbing',
                'location' => 'Student Center — Main Dining Hall',
                'assigned_to' => $facilitiesAdmin?->id,
                'title' => 'Drinking water dispenser leaking near cafeteria washbasin',
                'description' => 'Water is slowly pooling beneath the left drinking water station during lunch hours, making the tiled floor slippery for students.',
                'priority' => IssuePriority::High->value,
                'status' => IssueStatus::Resolved->value,
                'admin_note' => 'Caution cone placed while plumbing seal was tightened.',
                'resolution_note' => 'Replaced worn inlet valve gasket and dried the surrounding floor.',
                'days_ago' => 7,
                'history' => [
                    ['status' => IssueStatus::Submitted->value, 'title' => 'Report submitted', 'note' => 'Issue report submitted for administrative review.', 'actor' => $student2, 'hours_after' => 0],
                    ['status' => IssueStatus::UnderReview->value, 'title' => 'Under administrative review', 'note' => 'Plumbing technician notified.', 'actor' => $leadAdmin, 'hours_after' => 3],
                    ['status' => IssueStatus::Resolved->value, 'title' => 'Issue resolved', 'note' => 'Valve gasket replaced; no further leakage observed.', 'actor' => $facilitiesAdmin, 'hours_after' => 20],
                ],
            ],
            [
                'reference_number' => 'CFX-2026-1007',
                'reporter_id' => $student2->id,
                'category' => 'Furniture',
                'location' => 'Academic Building 1 — Room AB1-204',
                'assigned_to' => $leadAdmin->id,
                'title' => 'Three broken desk armrests in back row of AB1-204',
                'description' => 'Three student chairs in the back row have loose or detached writing tablets, leaving fewer usable seats during large section classes.',
                'priority' => IssuePriority::Low->value,
                'status' => IssueStatus::Closed->value,
                'admin_note' => 'Verified replacement chairs with floor supervisor.',
                'resolution_note' => 'Swapped damaged chairs with refurbished units from storage and closed ticket.',
                'days_ago' => 14,
                'history' => [
                    ['status' => IssueStatus::Submitted->value, 'title' => 'Report submitted', 'note' => 'Issue report submitted for administrative review.', 'actor' => $student2, 'hours_after' => 0],
                    ['status' => IssueStatus::Resolved->value, 'title' => 'Issue resolved', 'note' => 'Replaced three damaged chairs.', 'actor' => $facilitiesAdmin, 'hours_after' => 36],
                    ['status' => IssueStatus::Closed->value, 'title' => 'Issue closed', 'note' => 'Verified in room inspection and closed.', 'actor' => $leadAdmin, 'hours_after' => 60],
                ],
            ],
            [
                'reference_number' => 'CFX-2026-1008',
                'reporter_id' => $student1->id,
                'category' => 'Cleanliness',
                'location' => 'Central Library — Ground Floor Lobby',
                'assigned_to' => $leadAdmin->id,
                'title' => 'Duplicate report for lobby recycling bin overflow',
                'description' => 'The recycling bin near the central library ground floor entrance needs emptying after the morning seminar.',
                'priority' => IssuePriority::Low->value,
                'status' => IssueStatus::Rejected->value,
                'admin_note' => 'Duplicate of an already completed morning housekeeping task.',
                'resolution_note' => 'Closed as duplicate; housekeeping already cleared the lobby bins at 11:30 AM.',
                'days_ago' => 8,
                'history' => [
                    ['status' => IssueStatus::Submitted->value, 'title' => 'Report submitted', 'note' => 'Issue report submitted for administrative review.', 'actor' => $student1, 'hours_after' => 0],
                    ['status' => IssueStatus::Rejected->value, 'title' => 'Report rejected', 'note' => 'Duplicate of an already completed housekeeping request.', 'actor' => $leadAdmin, 'hours_after' => 4],
                ],
            ],
            [
                'reference_number' => 'CFX-2026-1009',
                'reporter_id' => $student2->id,
                'category' => 'Classroom Facilities',
                'location' => 'Academic Building 4 — Room AB4-601',
                'assigned_to' => null,
                'title' => 'Air conditioning unit blowing warm air in Room AB4-601',
                'description' => 'The north-side split AC unit in Room AB4-601 stays on fan mode and does not cool the room during afternoon classes.',
                'priority' => IssuePriority::Medium->value,
                'status' => IssueStatus::Submitted->value,
                'admin_note' => null,
                'resolution_note' => null,
                'days_ago' => 1,
                'history' => [
                    ['status' => IssueStatus::Submitted->value, 'title' => 'Report submitted', 'note' => 'Issue report submitted for administrative review.', 'actor' => $student2, 'hours_after' => 0],
                ],
            ],
        ];

        foreach ($demoIssues as $item) {
            $categoryId = $categories[$item['category']] ?? $categories->first();
            $locationId = $locations[$item['location']] ?? $locations->first();

            if (! $categoryId || ! $locationId) {
                continue;
            }

            $createdAt = now()->subDays($item['days_ago']);
            $resolvedAt = in_array($item['status'], [IssueStatus::Resolved->value, IssueStatus::Closed->value], true)
                ? $createdAt->copy()->addDays(2)
                : null;

            $issue = Issue::updateOrCreate(
                ['reference_number' => $item['reference_number']],
                [
                    'reporter_id' => $item['reporter_id'],
                    'category_id' => $categoryId,
                    'location_id' => $locationId,
                    'assigned_to' => $item['assigned_to'],
                    'title' => $item['title'],
                    'description' => $item['description'],
                    'priority' => $item['priority'],
                    'status' => $item['status'],
                    'admin_note' => $item['admin_note'],
                    'resolution_note' => $item['resolution_note'],
                    'resolved_at' => $resolvedAt,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt->copy()->addHours(12),
                ]
            );

            if ($issue->statusHistories()->count() === 0) {
                $previousStatus = null;
                foreach ($item['history'] as $entry) {
                    IssueStatusHistory::create([
                        'issue_id' => $issue->id,
                        'actor_id' => $entry['actor']?->id,
                        'old_status' => $previousStatus,
                        'status' => $entry['status'],
                        'title' => $entry['title'],
                        'note' => $entry['note'],
                        'created_at' => $createdAt->copy()->addHours($entry['hours_after']),
                    ]);
                    $previousStatus = $entry['status'];
                }
            }
        }
    }
}
