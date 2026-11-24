<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id',
        'name',
        'building',
        'floor',
        'room_or_area',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function campusBuilding(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'building_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getRoomLabelAttribute(): string
    {
        $room = $this->room_or_area ?: $this->name;

        return $this->floor ? "{$room} ({$this->floor})" : $room;
    }
}
