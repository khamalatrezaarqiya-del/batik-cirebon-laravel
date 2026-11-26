<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /** Data mentah dipindahkan apa adanya dari src/data/products.js (26 produk). */
    private const RAW = [
            ['Batik Mega Mendung Sogan', 'kain', 250000, '#6b3d1f', '#e6b871', 'mega'],
            ['Batik Mega Mendung Indigo', 'kain', 275000, '#1f3a5f', '#8fb8e0', 'mega'],
            ['Batik Mega Mendung Merah', 'kain', 285000, '#8c2f24', '#f2b98a', 'mega'],
            ['Kemeja Mega Mendung', 'pakaian', 350000, '#3f4a2a', '#c4d39a', 'mega'],
            ['Kemeja Batik Trusmi', 'pakaian', 365000, '#5c2b1c', '#dfa869', 'truntum'],
            ['Selendang Cirebonan', 'aksesoris', 150000, '#7a3c58', '#e0b9c8', 'truntum'],
            ['Selendang Sogan Klasik', 'aksesoris', 165000, '#7a4a1e', '#e8c48a', 'kawung'],
            ['Sarung Batik Trusmi', 'pakaian', 200000, '#7b241c', '#f0c39a', 'parang'],
            ['Sarung Parang Sogan', 'pakaian', 215000, '#4a2f18', '#d8a86a', 'parang'],
            ['Kain Kawung Sogan', 'kain', 240000, '#6d4520', '#e5c191', 'kawung'],
            ['Kain Kawung Indigo', 'kain', 245000, '#25374f', '#9ab4cc', 'kawung'],
            ['Batik Wadasan Klasik', 'kain', 310000, '#3d4a3a', '#bcd0a8', 'wadasan'],
            ['Batik Pesisir Pantai', 'kain', 300000, '#1f4b4a', '#a8d8cf', 'mega'],
            ['Batik Taman Arum', 'kain', 320000, '#5b2a4a', '#e3b6cf', 'truntum'],
            ['Batik Lereng Cirebon', 'kain', 295000, '#4b3a22', '#d4b184', 'parang'],
            ['Batik Siger Pesisir', 'kain', 330000, '#7a3f1d', '#f0c68a', 'truntum'],
            ['Blouse Batik Wanita', 'pakaian', 275000, '#6a2f3d', '#e7b9c2', 'kawung'],
            ['Dress Batik Modern', 'pakaian', 425000, '#3a2a4a', '#c9b3dd', 'mega'],
            ['Hijab Batik Cirebon', 'aksesoris', 120000, '#7d4a28', '#efd0a6', 'truntum'],
            ['Ikat Kepala Batik', 'aksesoris', 85000, '#5a3a20', '#e0bd8c', 'parang'],
            ['Tas Kain Batik', 'aksesoris', 140000, '#4a3428', '#d3b48f', 'kawung'],
            ['Dompet Batik Sogan', 'aksesoris', 95000, '#63401f', '#e6c79a', 'parang'],
            ['Kain Panjang Sogan', 'kain', 460000, '#5a3418', '#e9c79b', 'kawung'],
            ['Kain Panjang Indigo', 'kain', 475000, '#22344a', '#9db9d6', 'mega'],
            ['Syal Batik Premium', 'aksesoris', 185000, '#7a3a2a', '#eec3a6', 'truntum'],
            ['Set Kemeja & Sarung', 'pakaian', 560000, '#4a3520', '#dbb887', 'parang']
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

        foreach (self::RAW as $idx => [$nama, $kategori, $harga, $c1, $c2, $motif]) {
            Product::firstOrCreate(
                ['nama' => $nama],
                [
                    'category_id' => $map[$kategori]->id,
                    'harga' => $harga,
                    'c1' => $c1,
                    'c2' => $c2,
                    'motif' => $motif,
                    'stok' => 10,
                    'is_active' => true,
                    'is_best_seller' => $idx < 8,
                    'is_new' => $idx >= $total - 4,
                ],
            );
        }
    }
}
