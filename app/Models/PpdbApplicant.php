<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PpdbApplicant extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'ppdb_applicants';

    protected $guarded = ['id'];

    protected $casts = [
        'birth_date' => 'date',
        'custom_fields' => 'array',
        'verified_at' => 'datetime',
    ];

    public const STATUS_LABELS = [
        'submitted' => 'Terdaftar',
        'need_revision' => 'Perlu Perbaikan',
        'verified' => 'Berkas Lengkap',
        'accepted' => 'Diterima',
        'completed' => 'Selesai Daftar Ulang',
        'cancelled' => 'Dibatalkan',
    ];

    public function wave(): BelongsTo
    {
        return $this->belongsTo(PpdbWave::class, 'wave_id');
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(PpdbTrack::class, 'track_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PpdbDocument::class, 'applicant_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}
