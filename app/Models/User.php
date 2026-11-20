<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'student_id',
        'email',
        'password',
        'role',
        'department_id',
        'phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function reportedIssues(): HasMany
    {
        return $this->hasMany(Issue::class, 'reporter_id');
    }

    public function assignedIssues(): HasMany
    {
        return $this->hasMany(Issue::class, 'assigned_to');
    }

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('role', UserRole::Admin->value);
    }

    public function scopeStudents(Builder $query): Builder
    {
        return $query->where('role', UserRole::Student->value);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin->value;
    }

    public function isStudent(): bool
    {
        return $this->role === UserRole::Student->value;
    }

    public function dashboardRouteName(): string
    {
        return $this->isAdmin() ? 'admin.dashboard' : 'student.dashboard';
    }
}
