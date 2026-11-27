<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Katalog awal Batik Daffa — motif pesisiran khas Cirebon (Mega Mendung, Singaan, Wadasan, dll),
     * 4 jenis bahan utama (Viscose, Doby, ATBM, Katun), dan 3 teknik (Tulis, Cap, Kombinasi).
     */
    private const RAW = [
        ['Batik Mega Mendung Sogan', 'kain', 250000, '#6b3d1f', '#e6b871', 'mega', 'katun', 'kombinasi', 'unisex'],
        ['Batik Mega Mendung Indigo', 'kain', 275000, '#1f3a5f', '#8fb8e0', 'mega', 'katun', 'kombinasi', 'unisex'],
        ['Batik Mega Mendung Merah', 'kain', 285000, '#8c2f24', '#f2b98a', 'mega', 'doby', 'kombinasi', 'unisex'],
        ['Batik Singaan Pesisir Sogan', 'kain', 340000, '#5a3418', '#e8c48a', 'singaan', 'doby', 'kombinasi', 'unisex'],
        ['Kemeja Mega Mendung Pria', 'pakaian', 350000, '#3f4a2a', '#c4d39a', 'mega', 'katun', 'kombinasi', 'pria'],
        ['Kemeja Singaan Cirebon Pria', 'pakaian', 365000, '#5c2b1c', '#dfa869', 'singaan', 'doby', 'kombinasi', 'pria'],
        ['Selendang Viscose Cirebonan', 'aksesoris', 75000, '#7a3c58', '#e0b9c8', 'mega', 'viscose', 'cap', 'wanita'],
        ['Selendang Sogan Klasik', 'aksesoris', 95000, '#7a4a1e', '#e8c48a', 'wadasan', 'viscose', 'cap', 'wanita'],
        ['Sarung Batik Panembahan', 'pakaian', 200000, '#7b241c', '#f0c39a', 'mega', 'katun', 'cap', 'pria'],
        ['Sarung Singaan Sogan', 'pakaian', 225000, '#4a2f18', '#d8a86a', 'singaan', 'katun', 'kombinasi', 'pria'],
        ['Kain Doby Kawung Sogan', 'kain', 290000, '#6d4520', '#e5c191', 'kawung', 'doby', 'kombinasi', 'unisex'],
        ['Kain Katun Kawung Indigo', 'kain', 245000, '#25374f', '#9ab4cc', 'kawung', 'katun', 'cap', 'unisex'],
        ['Batik Wadasan Klasik', 'kain', 310000, '#3d4a3a', '#bcd0a8', 'wadasan', 'katun', 'kombinasi', 'unisex'],
        ['Batik Pesisir Pantai Viscose', 'kain', 320000, '#1f4b4a', '#a8d8cf', 'mega', 'viscose', 'kombinasi', 'unisex'],
        ['Batik Taman Arum Cirebon', 'kain', 335000, '#5b2a4a', '#e3b6cf', 'truntum', 'doby', 'kombinasi', 'unisex'],
        ['Batik Singaan Doby Eksklusif', 'kain', 650000, '#4b3a22', '#d4b184', 'singaan', 'doby', 'tulis', 'unisex'],
        ['Batik Siger Pesisir', 'kain', 330000, '#7a3f1d', '#f0c68a', 'wadasan', 'katun', 'kombinasi', 'unisex'],
        ['Blouse Viscose Wanita', 'pakaian', 275000, '#6a2f3d', '#e7b9c2', 'mega', 'viscose', 'kombinasi', 'wanita'],
        ['Dress Batik Viscose Modern', 'pakaian', 425000, '#3a2a4a', '#c9b3dd', 'singaan', 'viscose', 'kombinasi', 'wanita'],
        ['Hijab Batik Cirebon', 'aksesoris', 65000, '#7d4a28', '#efd0a6', 'truntum', 'viscose', 'cap', 'wanita'],
        ['Ikat Kepala Batik Cap', 'aksesoris', 55000, '#5a3a20', '#e0bd8c', 'mega', 'katun', 'cap', 'unisex'],
        ['Tas Kain Batik Doby', 'aksesoris', 140000, '#4a3428', '#d3b48f', 'wadasan', 'doby', 'cap', 'unisex'],
        ['Kain Tulis ATBM Mega Mendung', 'kain', 2850000, '#5a3418', '#e9c79b', 'mega', 'atbm', 'tulis', 'unisex'],
        ['Kain Tulis ATBM Singaan Eksklusif', 'kain', 4500000, '#22344a', '#9db9d6', 'singaan', 'atbm', 'tulis', 'unisex'],
        ['Syal Viscose Batik Tulis', 'aksesoris', 185000, '#7a3a2a', '#eec3a6', 'truntum', 'viscose', 'kombinasi', 'wanita'],
        ['Set Kemeja & Sarung Doby', 'pakaian', 560000, '#4a3520', '#dbb887', 'wadasan', 'doby', 'kombinasi', 'pria'],
    ];

    private const CATEGORIES = [
        'kain' => 'Kain',
        'pakaian' => 'Pakaian',
        'aksesoris' => 'Aksesoris',
    ];

    public function run(): void
    {
        $map = [];

        foreach (self::CATEGORIES as $slug => $nama) {
            $map[$slug] = Category::firstOrCreate(['slug' => $slug], ['nama' => $nama]);
        }

        $total = count(self::RAW);

        foreach (self::RAW as $idx => [$nama, $kategori, $harga, $c1, $c2, $motif, $bahan, $teknik, $gender]) {
            Product::firstOrCreate(
                ['nama' => $nama],
                [
                    'category_id' => $map[$kategori]->id,
                    'harga' => $harga,
                    'c1' => $c1,
                    'c2' => $c2,
                    'motif' => $motif,
                    'bahan' => $bahan,
                    'teknik' => $teknik,
                    'gender' => $gender,
                    'stok' => 10,
                    'is_active' => true,
                    'is_best_seller' => $idx < 8,
                    'is_new' => $idx >= $total - 4,
                ],
            );
        }
    }
}
