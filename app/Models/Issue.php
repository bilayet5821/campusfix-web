<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Issue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference_number',
        'reporter_id',
        'category_id',
        'location_id',
        'assigned_to',
        'title',
        'description',
        'priority',
        'status',
        'admin_note',
        'resolution_note',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Issue $issue): void {
            if (empty($issue->reference_number)) {
                $issue->reference_number = self::generateReferenceNumber();
            }
        });
    }

    public static function generateReferenceNumber(): string
    {
        $year = now()->format('Y');

        do {
            $suffix = strtoupper(Str::random(4));
            $numeric = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
            $candidate = "CFX-{$year}-{$numeric}";
            if (self::withTrashed()->where('reference_number', $candidate)->exists()) {
                $candidate = "CFX-{$year}-{$suffix}{$numeric}";
            }
        } while (self::withTrashed()->where('reference_number', $candidate)->exists());

        return $candidate;
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(IssueAttachment::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(IssueStatusHistory::class)->orderByDesc('created_at')->orderByDesc('id');
    }

    /**
     * Alias matching the contract's `status_history` fallback name.
     */
    public function statusHistory(): HasMany
    {
        return $this->statusHistories();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $term = trim((string) $filters['search']);
            $query->where(function (Builder $sub) use ($term): void {
                $sub->where('reference_number', 'like', "%{$term}%")
                    ->orWhere('title', 'like', "%{$term}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['category'])) {
            $query->where('category_id', (int) $filters['category']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query;
    }
}
