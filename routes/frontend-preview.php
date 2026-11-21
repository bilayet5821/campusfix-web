<?php

use Illuminate\Support\Facades\Route;

$student = (object) [
    'name' => 'Ariana Rahman',
    'student_id' => 'Preview Student',
    'email' => 'student.preview@example.test',
];

$admin = (object) [
    'id' => 'preview-admin',
    'name' => 'Nadia Karim',
    'email' => 'admin.preview@example.test',
    'role' => 'admin',
];

$assignees = collect([
    $admin,
    (object) ['id' => 'preview-admin-2', 'name' => 'Facilities Coordinator', 'email' => 'facilities.preview@example.test', 'role' => 'admin'],
    (object) ['id' => 'preview-admin-3', 'name' => 'IT Support Coordinator', 'email' => 'it.preview@example.test', 'role' => 'admin'],
]);

$departments = collect([
    (object) ['id' => 'preview-cse', 'name' => 'Computer Science & Engineering'],
    (object) ['id' => 'preview-eee', 'name' => 'Electrical & Electronic Engineering'],
    (object) ['id' => 'preview-bba', 'name' => 'Business Administration'],
]);

$categories = collect([
    (object) ['id' => 'preview-facilities', 'name' => 'Classroom Facilities'],
    (object) ['id' => 'preview-network', 'name' => 'IT & Network'],
    (object) ['id' => 'preview-electrical', 'name' => 'Electrical'],
    (object) ['id' => 'preview-cleanliness', 'name' => 'Cleanliness'],
    (object) ['id' => 'preview-safety', 'name' => 'Safety'],
    (object) ['id' => 'preview-maintenance', 'name' => 'Maintenance'],
]);

$buildings = collect([
    (object) ['id' => 'preview-b-main', 'name' => 'Main Building', 'code' => 'MB'],
    (object) ['id' => 'preview-b-lib', 'name' => 'Central Library', 'code' => 'LIB'],
    (object) ['id' => 'preview-b-sc', 'name' => 'Student Center', 'code' => 'SC'],
]);

$locations = collect([
    (object) ['id' => 'preview-main-402', 'building_id' => 'preview-b-main', 'name' => 'Main Building — Room 402', 'room_or_area' => 'Room 402', 'floor' => '4th Floor'],
    (object) ['id' => 'preview-lab', 'building_id' => 'preview-b-main', 'name' => 'Main Building — Computer Lab', 'room_or_area' => 'Computer Lab', 'floor' => '3rd Floor'],
    (object) ['id' => 'preview-library', 'building_id' => 'preview-b-lib', 'name' => 'Central Library — Reading Area', 'room_or_area' => 'Reading Area', 'floor' => '2nd Floor'],
    (object) ['id' => 'preview-cafeteria', 'building_id' => 'preview-b-sc', 'name' => 'Student Center — Cafeteria', 'room_or_area' => 'Cafeteria', 'floor' => '1st Floor'],
]);

$sampleIssues = function () use ($student, $categories, $locations) {
    return collect([
        (object) [
            'id' => 'preview-1',
            'reference_number' => 'CFX-PREVIEW-1042',
            'title' => 'Projector is not displaying in Room 402',
            'description' => "The classroom projector powers on, but it does not display input from the lectern computer. The issue was noticed before the morning class and remained after reconnecting the HDMI cable.",
            'status' => 'in_progress',
            'priority' => 'high',
            'category' => $categories->firstWhere('id', 'preview-facilities'),
            'location' => $locations->firstWhere('id', 'preview-main-402'),
            'reporter' => $student,
            'assignee' => (object) ['id' => 'preview-admin-2', 'name' => 'Facilities Coordinator', 'email' => 'facilities.preview@example.test'],
            'attachments' => collect([
                (object) ['original_name' => 'projector-screen.jpg', 'mime_type' => 'image/jpeg', 'url' => null],
                (object) ['original_name' => 'lectern-connection.jpg', 'mime_type' => 'image/jpeg', 'url' => null],
            ]),
            'admin_note' => 'The facilities team has inspected the room and is checking the display connection.',
            'resolution_note' => null,
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subHours(5),
        ],
        (object) [
            'id' => 'preview-2',
            'reference_number' => 'CFX-PREVIEW-1037',
            'title' => 'Unstable Wi-Fi near the computer lab',
            'description' => 'The campus network disconnects repeatedly near the computer lab entrance.',
            'status' => 'under_review',
            'priority' => 'medium',
            'category' => $categories->firstWhere('id', 'preview-network'),
            'location' => $locations->firstWhere('id', 'preview-lab'),
            'reporter' => $student,
            'assignee' => null,
            'attachments' => collect(),
            'created_at' => now()->subDays(6),
            'updated_at' => now()->subDays(2),
        ],
        (object) [
            'id' => 'preview-3',
            'reference_number' => 'CFX-PREVIEW-1028',
            'title' => 'Reading-area light requires replacement',
            'description' => 'A ceiling light in the east reading area is not working.',
            'status' => 'resolved',
            'priority' => 'low',
            'category' => $categories->firstWhere('id', 'preview-electrical'),
            'location' => $locations->firstWhere('id', 'preview-library'),
            'reporter' => $student,
            'assignee' => (object) ['id' => 'preview-admin-2', 'name' => 'Facilities Coordinator', 'email' => 'facilities.preview@example.test'],
            'attachments' => collect(),
            'resolution_note' => 'The failed light was replaced and the fixture was tested.',
            'created_at' => now()->subDays(12),
            'updated_at' => now()->subDays(8),
        ],
    ]);
};

Route::prefix('preview')->name('preview.')->group(function () use ($student, $admin, $assignees, $departments, $categories, $buildings, $locations, $sampleIssues) {
    Route::get('/login', fn () => view('auth.login'))->name('login');
    Route::get('/register', fn () => view('auth.register', ['departments' => $departments]))->name('register');

    Route::get('/student/dashboard', function () use ($student, $sampleIssues) {
        $recentIssues = $sampleIssues();
        $stats = ['total' => 6, 'pending_review' => 2, 'in_progress' => 1, 'resolved' => 3];
        $recentActivity = collect([
            (object) ['title' => 'Report moved to In Progress', 'description' => 'Projector is not displaying in Room 402', 'created_at' => now()->subHours(5)],
            (object) ['title' => 'Review started', 'description' => 'Unstable Wi-Fi near the computer lab', 'created_at' => now()->subDays(2)],
            (object) ['title' => 'Issue resolved', 'description' => 'Reading-area light requires replacement', 'created_at' => now()->subDays(8)],
        ]);

        return view('student.dashboard', compact('student', 'stats', 'recentIssues', 'recentActivity'));
    })->name('student.dashboard');

    Route::get('/student/issues', function () use ($student, $categories, $sampleIssues) {
        $issues = $sampleIssues();
        $filters = request()->only(['search', 'status', 'category']);

        if ($search = data_get($filters, 'search')) {
            $issues = $issues->filter(fn ($issue) => str_contains(strtolower($issue->title.' '.$issue->reference_number), strtolower($search)))->values();
        }
        if ($status = data_get($filters, 'status')) {
            $issues = $issues->where('status', $status)->values();
        }
        if ($category = data_get($filters, 'category')) {
            $issues = $issues->filter(fn ($issue) => data_get($issue, 'category.id') === $category)->values();
        }

        return view('student.issues.index', compact('student', 'issues', 'filters', 'categories'));
    })->name('student.issues.index');

    Route::get('/student/issues/create', fn () => view('student.issues.create', [
        'student' => $student,
        'categories' => $categories,
        'buildings' => $buildings,
        'locations' => $locations,
    ]))->name('student.issues.create');

    Route::get('/student/issues/sample', function () use ($student, $sampleIssues) {
        $issue = $sampleIssues()->first();
        $statusHistory = collect([
            (object) ['status' => 'in_progress', 'title' => 'Work started', 'note' => 'The facilities team is inspecting the display connection.', 'created_at' => now()->subHours(5)],
            (object) ['status' => 'under_review', 'title' => 'Report reviewed', 'note' => 'The report was reviewed and assigned to the facilities team.', 'created_at' => now()->subDays(2)],
            (object) ['status' => 'submitted', 'title' => 'Report submitted', 'note' => 'The issue report was received successfully.', 'created_at' => now()->subDays(3)],
        ]);

        return view('student.issues.show', compact('student', 'issue', 'statusHistory'));
    })->name('student.issues.show');

    Route::get('/admin/dashboard', function () use ($admin, $sampleIssues) {
        $recentIssues = $sampleIssues();
        $stats = ['total' => 18, 'submitted' => 4, 'under_review' => 3, 'in_progress' => 5, 'resolved' => 4, 'closed_rejected' => 2];
        $statusDistribution = collect([
            (object) ['label' => 'Submitted', 'count' => 4, 'percentage' => 22],
            (object) ['label' => 'Under Review', 'count' => 3, 'percentage' => 17],
            (object) ['label' => 'In Progress', 'count' => 5, 'percentage' => 28],
            (object) ['label' => 'Resolved', 'count' => 4, 'percentage' => 22],
        ]);
        $priorityDistribution = collect([
            (object) ['label' => 'Urgent', 'count' => 2, 'percentage' => 11],
            (object) ['label' => 'High', 'count' => 5, 'percentage' => 28],
            (object) ['label' => 'Medium', 'count' => 8, 'percentage' => 44],
            (object) ['label' => 'Low', 'count' => 3, 'percentage' => 17],
        ]);
        $categoryDistribution = collect([
            (object) ['label' => 'Facilities', 'count' => 7, 'percentage' => 39],
            (object) ['label' => 'IT & Network', 'count' => 5, 'percentage' => 28],
            (object) ['label' => 'Electrical', 'count' => 3, 'percentage' => 17],
            (object) ['label' => 'Other', 'count' => 3, 'percentage' => 17],
        ]);
        $recentActivity = collect([
            (object) ['title' => 'Issue assigned', 'description' => 'CFX-PREVIEW-1042 was assigned to Facilities Coordinator.', 'created_at' => now()->subHours(2)],
            (object) ['title' => 'Priority updated', 'description' => 'A network report was marked as high priority.', 'created_at' => now()->subHours(6)],
            (object) ['title' => 'Issue resolved', 'description' => 'The library lighting report was resolved.', 'created_at' => now()->subDay()],
        ]);

        return view('admin.dashboard', compact('admin', 'stats', 'recentIssues', 'statusDistribution', 'priorityDistribution', 'categoryDistribution', 'recentActivity'));
    })->name('admin.dashboard');

    Route::get('/admin/issues', function () use ($admin, $categories, $sampleIssues) {
        $issues = $sampleIssues();
        $filters = request()->only(['search', 'status', 'priority', 'category', 'date_from', 'date_to']);

        if ($search = data_get($filters, 'search')) {
            $issues = $issues->filter(fn ($issue) => str_contains(strtolower($issue->title.' '.$issue->reference_number), strtolower($search)))->values();
        }
        foreach (['status', 'priority'] as $field) {
            if ($value = data_get($filters, $field)) $issues = $issues->where($field, $value)->values();
        }
        if ($category = data_get($filters, 'category')) {
            $issues = $issues->filter(fn ($issue) => data_get($issue, 'category.id') === $category)->values();
        }
        if ($from = data_get($filters, 'date_from')) {
            $issues = $issues->filter(fn ($issue) => $issue->created_at->toDateString() >= $from)->values();
        }
        if ($to = data_get($filters, 'date_to')) {
            $issues = $issues->filter(fn ($issue) => $issue->created_at->toDateString() <= $to)->values();
        }

        return view('admin.issues.index', compact('admin', 'issues', 'filters', 'categories'));
    })->name('admin.issues.index');

    Route::get('/admin/issues/sample', function () use ($admin, $sampleIssues) {
        $issue = $sampleIssues()->first();
        $statusHistory = collect([
            (object) ['status' => 'in_progress', 'title' => 'Work started', 'note' => 'Facilities staff began inspecting the display connection.', 'actor' => $admin, 'created_at' => now()->subHours(5)],
            (object) ['status' => 'under_review', 'title' => 'Assigned for review', 'note' => 'The issue was assigned to the facilities coordinator.', 'actor' => $admin, 'created_at' => now()->subDays(2)],
            (object) ['status' => 'submitted', 'title' => 'Report submitted', 'note' => 'The student report entered the review queue.', 'created_at' => now()->subDays(3)],
        ]);

        return view('admin.issues.show', compact('admin', 'issue', 'statusHistory'));
    })->name('admin.issues.show');

    Route::get('/admin/issues/sample/edit', function () use ($admin, $assignees, $sampleIssues) {
        $issue = $sampleIssues()->first();
        return view('admin.issues.edit', compact('admin', 'assignees', 'issue'));
    })->name('admin.issues.edit');
});
