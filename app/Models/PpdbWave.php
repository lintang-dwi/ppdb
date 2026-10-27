<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PpdbWave extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'ppdb_waves';

    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'announcement_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function applicants(): HasMany
    {
        return $this->hasMany(PpdbApplicant::class, 'wave_id');
    }

    public function formFields(): HasMany
    {
        return $this->hasMany(PpdbFormField::class, 'wave_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isOpen(): bool
    {
        $now = now();

        return $this->is_active && $now->between($this->start_date, $this->end_date);
    }
}
