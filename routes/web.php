<?php

use App\Enums\FeaturePermission;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\IssueController as AdminIssueController;
use App\Http\Controllers\Admin\LocationController as AdminLocationController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\IssueController as StudentIssueController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('public.home');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/attachments/{attachment}', [AttachmentController::class, 'show'])
        ->name('attachments.show');

    Route::prefix('student')
        ->name('student.')
        ->group(function () {
            Route::middleware('permission:'.FeaturePermission::ViewStudentPortal->value)->group(function () {
                Route::get('/dashboard', StudentDashboardController::class)->name('dashboard');
                Route::get('/issues', [StudentIssueController::class, 'index'])->name('issues.index');
            });

            Route::middleware('permission:'.FeaturePermission::CreateIssues->value)->group(function () {
                Route::get('/issues/create', [StudentIssueController::class, 'create'])->name('issues.create');
                Route::post('/issues', [StudentIssueController::class, 'store'])->name('issues.store');
            });

            Route::get('/issues/{issue}', [StudentIssueController::class, 'show'])
                ->middleware('permission:'.FeaturePermission::ViewStudentPortal->value.','.FeaturePermission::CreateIssues->value)
                ->name('issues.show');
        });

    Route::prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::get('/dashboard', AdminDashboardController::class)
                ->middleware('permission:'.FeaturePermission::ViewAdminDashboard->value)
                ->name('dashboard');

            Route::middleware('permission:'.FeaturePermission::ManageIssues->value)->group(function () {
                Route::get('/issues', [AdminIssueController::class, 'index'])->name('issues.index');
                Route::get('/issues/{issue}', [AdminIssueController::class, 'show'])->name('issues.show');
                Route::get('/issues/{issue}/edit', [AdminIssueController::class, 'edit'])->name('issues.edit');
                Route::patch('/issues/{issue}', [AdminIssueController::class, 'update'])->name('issues.update');
            });

            Route::middleware('permission:'.FeaturePermission::ManageLocations->value)->group(function () {
                Route::get('/locations', [AdminLocationController::class, 'index'])->name('locations.index');
                Route::post('/buildings', [AdminLocationController::class, 'storeBuilding'])->name('buildings.store');
                Route::post('/locations', [AdminLocationController::class, 'storeLocation'])->name('locations.store');
            });

            Route::middleware('permission:'.FeaturePermission::ManageUsers->value)->group(function () {
                Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
                Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
                Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
                Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
                Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
            });
        });
});

if (app()->environment('local')) {
    require __DIR__.'/frontend-preview.php';
}
