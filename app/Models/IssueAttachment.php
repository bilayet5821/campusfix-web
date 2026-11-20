<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Route;

class IssueAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'issue_id',
        'uploaded_by',
        'original_name',
        'disk',
        'file_path',
        'mime_type',
        'size',
    ];

    protected $hidden = [
        'disk',
        'file_path',
    ];

    protected $appends = [
        'url',
    ];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): ?string
    {
        if (! $this->exists || ! Route::has('attachments.show')) {
            return null;
        }

        return route('attachments.show', $this);
    }
}
