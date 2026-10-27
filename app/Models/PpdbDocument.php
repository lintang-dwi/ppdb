<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpdbDocument extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'ppdb_documents';

    protected $guarded = ['id'];

    protected $casts = [
        'is_physical_submitted' => 'boolean',
    ];

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(PpdbApplicant::class, 'applicant_id');
    }

    public function scopeValid(Builder $query): Builder
    {
        return $query->where('verification_status', 'valid');
    }

    public function scopeInvalid(Builder $query): Builder
    {
        return $query->where('verification_status', 'invalid');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('verification_status', 'pending');
    }
}
