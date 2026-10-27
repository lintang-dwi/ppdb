<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class School extends Model
{
    use HasFactory;

    protected $table = 'schools';

    protected $primaryKey = 'school_code';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'custom_domain_verified' => 'boolean',
    ];

    public function setGoogleDriveRefreshTokenAttribute(?string $value): void
    {
        $this->attributes['google_drive_refresh_token'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getGoogleDriveRefreshTokenDecrypted(): ?string
    {
        $raw = $this->attributes['google_drive_refresh_token'] ?? null;
        if (! $raw) {
            return null;
        }

        try {
            return Crypt::decryptString($raw);
        } catch (Throwable) {
            return $raw;
        }
    }

    public function hasGoogleDriveConnected(): bool
    {
        return ! empty($this->attributes['google_drive_refresh_token']);
    }

    public function getLogoUrlAttribute(): ?string
    {
        $logo = $this->attributes['logo'] ?? null;
        if (! $logo) {
            return null;
        }

        if (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://')) {
            return $logo;
        }

        return asset('storage/' . ltrim($logo, '/'));
    }

    public function getFaviconUrlAttribute(): string
    {
        $fav = $this->attributes['favicon'] ?? null;
        if ($fav) {
            if (str_starts_with($fav, 'http://') || str_starts_with($fav, 'https://') || str_starts_with($fav, 'data:')) {
                return $fav;
            }
            return asset('storage/' . ltrim($fav, '/'));
        }

        $initial = strtoupper(substr($this->name ?: 'P', 0, 1));
        $svg = "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='24' fill='%230f766e'/><text x='50%' y='55%' dominant-baseline='middle' text-anchor='middle' font-family='sans-serif' font-size='56' font-weight='800' fill='%23ffffff'>{$initial}</text></svg>";

        return 'data:image/svg+xml,' . $svg;
    }
}
