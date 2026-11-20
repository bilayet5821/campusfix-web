<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssueStatusHistory extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'issue_id',
        'actor_id',
        'old_status',
        'status',
        'title',
        'note',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
