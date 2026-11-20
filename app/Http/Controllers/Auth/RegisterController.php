<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        $departments = Department::active()->orderBy('name')->get();

        return view('auth.register', compact('departments'));
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'student_id' => $validated['student_id'],
            'email' => $validated['email'],
            'department_id' => $validated['department_id'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'role' => UserRole::Student->value,
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()
            ->route('student.dashboard')
            ->with('success', 'Your student account has been created. Welcome to CampusFix!');
    }
}
