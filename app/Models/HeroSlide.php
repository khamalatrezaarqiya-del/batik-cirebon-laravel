<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HeroSlide extends Model
{
    protected $fillable = [
        'title',
        'description',
        'cta_label',
        'cta_url',
        'image',
        'focal_point',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function resolvedImageUrl(): string
    {
        return self::resolveMediaUrl($this->image, '/images/campaigns/hero-1.jpg');
    }

    public static function resolveMediaUrl(?string $path, string $fallback): string
    {
        if (! $path) {
            return $fallback;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
