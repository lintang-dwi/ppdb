<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpdbFormField extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'ppdb_form_fields';

    public const STEPS = [
        'biodata' => 'Data Pribadi Siswa',
        'orang_tua' => 'Data Orang Tua / Wali',
        'asal_sekolah' => 'Data Sekolah Asal',
        'tambahan' => 'Informasi Tambahan',
        'berkas' => 'Dokumen & Lampiran',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'order_index' => 'integer',
    ];

    public function wave(): BelongsTo
    {
        return $this->belongsTo(PpdbWave::class, 'wave_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForStep(Builder $query, string $step): Builder
    {
        return $query->where('step', $step);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order_index')->orderBy('id');
    }
}
