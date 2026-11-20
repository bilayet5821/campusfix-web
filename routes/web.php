<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\IssueController as AdminIssueController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\IssueController as StudentIssueController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('public.home');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/attachments/{attachment}', [AttachmentController::class, 'show'])
        ->name('attachments.show');

    Route::prefix('student')
        ->name('student.')
        ->middleware('role:student')
        ->group(function () {
            Route::get('/dashboard', StudentDashboardController::class)->name('dashboard');
            Route::get('/issues', [StudentIssueController::class, 'index'])->name('issues.index');
            Route::get('/issues/create', [StudentIssueController::class, 'create'])->name('issues.create');
            Route::post('/issues', [StudentIssueController::class, 'store'])->name('issues.store');
            Route::get('/issues/{issue}', [StudentIssueController::class, 'show'])->name('issues.show');
        });

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:admin')
        ->group(function () {
            Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
            Route::get('/issues', [AdminIssueController::class, 'index'])->name('issues.index');
            Route::get('/issues/{issue}', [AdminIssueController::class, 'show'])->name('issues.show');
            Route::get('/issues/{issue}/edit', [AdminIssueController::class, 'edit'])->name('issues.edit');
            Route::patch('/issues/{issue}', [AdminIssueController::class, 'update'])->name('issues.update');
        });
});

if (app()->environment('local')) {
    require __DIR__.'/frontend-preview.php';
}
