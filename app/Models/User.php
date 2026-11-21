<?php

namespace App\Models;

use App\Enums\FeaturePermission;
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
        'permissions',
        'is_active',
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
            'permissions' => 'array',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if ($user->role === null) {
                $user->role = UserRole::Student->value;
            }

            if ($user->permissions === null) {
                $user->permissions = FeaturePermission::defaultsForRole($user->role);
            }

            if ($user->is_active === null) {
                $user->is_active = true;
            }
        });
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

    public function scopeAssignable(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $sub): void {
                $sub->whereIn('role', [UserRole::Admin->value, UserRole::Staff->value])
                    ->orWhere('permissions', 'like', '%"'.FeaturePermission::ManageIssues->value.'"%');
            });
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin->value;
    }

    public function isStaff(): bool
    {
        return $this->role === UserRole::Staff->value;
    }

    public function isTeacher(): bool
    {
        return $this->role === UserRole::Teacher->value;
    }

    public function isStudent(): bool
    {
        return $this->role === UserRole::Student->value;
    }

    public function hasPermission(FeaturePermission|string $permission): bool
    {
        $key = $permission instanceof FeaturePermission ? $permission->value : $permission;

        return in_array($key, $this->permissions ?? [], true);
    }

    /**
     * @param  list<FeaturePermission|string>  $permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function roleLabel(): string
    {
        return UserRole::tryFrom((string) $this->role)?->label() ?? ucfirst((string) $this->role);
    }

    public function dashboardRouteName(): string
    {
        if ($this->isAdmin() || $this->isStaff()) {
            return match (true) {
                $this->hasPermission(FeaturePermission::ViewAdminDashboard) => 'admin.dashboard',
                $this->hasPermission(FeaturePermission::ManageIssues) => 'admin.issues.index',
                $this->hasPermission(FeaturePermission::ManageLocations) => 'admin.locations.index',
                $this->hasPermission(FeaturePermission::ManageUsers) => 'admin.users.index',
                $this->hasPermission(FeaturePermission::ViewStudentPortal) => 'student.dashboard',
                $this->hasPermission(FeaturePermission::CreateIssues) => 'student.issues.create',
                default => 'home',
            };
        }

        return match (true) {
            $this->hasPermission(FeaturePermission::ViewStudentPortal) => 'student.dashboard',
            $this->hasPermission(FeaturePermission::CreateIssues) => 'student.issues.create',
            $this->hasPermission(FeaturePermission::ViewAdminDashboard) => 'admin.dashboard',
            $this->hasPermission(FeaturePermission::ManageIssues) => 'admin.issues.index',
            $this->hasPermission(FeaturePermission::ManageLocations) => 'admin.locations.index',
            $this->hasPermission(FeaturePermission::ManageUsers) => 'admin.users.index',
            default => 'home',
        };
    }
}
