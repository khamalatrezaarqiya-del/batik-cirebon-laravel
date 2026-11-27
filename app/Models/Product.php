<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    public const MOTIF_OPTIONS = [
        'mega' => 'Mega Mendung',
        'singaan' => 'Singaan',
        'wadasan' => 'Wadasan',
        'truntum' => 'Truntum',
        'kawung' => 'Kawung',
        'lainnya' => 'Pesisiran Lainnya',
    ];

    public const BAHAN_OPTIONS = [
        'viscose' => 'Viscose',
        'doby' => 'Doby',
        'atbm' => 'ATBM (Tenun Bukan Mesin)',
        'katun' => 'Katun',
    ];

    public const TEKNIK_OPTIONS = [
        'tulis' => 'Batik Tulis Murni',
        'cap' => 'Batik Cap',
        'kombinasi' => 'Kombinasi Cap & Tulis',
    ];

    public const GENDER_OPTIONS = [
        'pria' => 'Pria',
        'wanita' => 'Wanita',
        'unisex' => 'Unisex / Kain',
    ];

    protected $fillable = [
        'category_id', 'nama', 'slug', 'harga', 'motif',
        'bahan', 'teknik', 'gender',
        'c1', 'c2', 'foto', 'deskripsi', 'stok', 'is_active',
        'is_best_seller', 'is_new',
    ];

    protected $casts = [
        'harga' => 'integer',
        'stok' => 'integer',
        'is_active' => 'boolean',
        'is_best_seller' => 'boolean',
        'is_new' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if (blank($product->slug)) {
                $product->slug = static::uniqueSlug($product->nama, $product->id);
            }
        });
    }

    public static function uniqueSlug(string $nama, ?int $ignoreId = null): string
    {
        $base = Str::slug($nama) ?: 'produk';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
