<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\HeroSlide;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class StoreVisualSeeder extends Seeder
{
    public function run(): void
    {
        if (HeroSlide::count() === 0) {
            HeroSlide::insert([
                [
                    'title' => 'Warisan Mega Mendung',
                    'description' => 'Kain batik tulis, cap, dan kombinasi pesisiran Cirebon karya pengrajin Panembahan, Plered sejak 2000-an.',
                    'cta_label' => 'Jelajahi Koleksi Kain',
                    'cta_url' => '/katalog?cat=kain',
                    'image' => '/images/campaigns/hero-1.jpg',
                    'focal_point' => '72% 45%',
                    'sort_order' => 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'Busana Batik Kontemporer',
                    'description' => 'Pilihan kemeja pria, blouse, dan dress berbahan Viscose, Doby, Katun, hingga ATBM eksklusif.',
                    'cta_label' => 'Lihat Koleksi Pakaian',
                    'cta_url' => '/katalog?cat=pakaian',
                    'image' => '/images/campaigns/hero-2.jpg',
                    'focal_point' => '72% 20%',
                    'sort_order' => 2,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'Ketelitian di Setiap Canting',
                    'description' => 'Dikerjakan langsung oleh tim pengrajin lokal Batik Daffa di Panembahan, Cirebon untuk ritel hingga grosir.',
                    'cta_label' => 'Belanja Sekarang',
                    'cta_url' => '/katalog',
                    'image' => '/images/campaigns/hero-3.jpg',
                    'focal_point' => '68% 48%',
                    'sort_order' => 3,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        $defaults = [
            'featured_label' => 'Koleksi Pilihan',
            'featured_title' => 'Harmoni Mega Mendung & Singaan',
            'featured_description' => 'Perpaduan corak pesisiran khas Cirebon pada bahan Viscose, Doby, Katun, dan ATBM untuk keseharian, seragam, hingga koleksi istimewa.',
            'featured_cta_label' => 'Lihat Koleksi Mega Mendung',
            'featured_cta_url' => '/katalog?motif=mega',
            'featured_image' => '/images/campaigns/featured-collection.jpg',
            'pay_transfer_enabled' => '1',
            'pay_dp50_enabled' => '1',
            'pay_qris_enabled' => '0',
            'pay_cod_enabled' => '0',
            'pay_bank_name' => 'Bank BCA',
            'pay_bank_account' => '1340 5829 10',
            'pay_bank_holder' => 'Batik Daffa Cirebon',
            'pay_bank_note' => 'Transfer m-Banking sesuai nominal total pesanan. Admin Batik Daffa akan memverifikasi mutasi dan menghubungi Anda untuk pengiriman.',
            'pay_dp50_note' => 'Khusus pesanan bernominal besar, seragam, atau custom. Transfer DP 50% terlebih dahulu dan pelunasan dilakukan sebelum pengiriman.',
            'pay_qris_image' => '/images/qris-default.svg',
            'pay_qris_merchant' => 'Batik Daffa · Plered, Cirebon',
            'pay_ewallet_info' => 'Scan QRIS melalui GoPay, OVO, DANA, ShopeePay, atau mobile banking.',
            'pay_cod_note' => 'Bayar tunai langsung ke kurir saat paket tiba. Admin akan menghubungi Anda terlebih dahulu sebelum paket dikirim.',
        ];

        foreach ($defaults as $key => $value) {
            SiteSetting::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        $catDefaults = [
            'kain' => '/images/categories/kain.jpg',
            'pakaian' => '/images/categories/pakaian.jpg',
            'aksesoris' => '/images/categories/aksesoris.jpg',
        ];

        foreach ($catDefaults as $slug => $img) {
            Category::where('slug', $slug)
                ->whereNull('image')
                ->update(['image' => $img]);
        }
    }
}
