<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PpdbTrack extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'ppdb_tracks';

    protected $guarded = ['id'];

    protected $casts = [
        'quota' => 'integer',
        'is_active' => 'boolean',
        'order_index' => 'integer',
    ];

    public function applicants(): HasMany
    {
        return $this->hasMany(PpdbApplicant::class, 'track_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order_index')->orderBy('name');
    }
}
