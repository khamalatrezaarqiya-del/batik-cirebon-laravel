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
                    'description' => 'Kain batik tulis dan cap pilihan karya pengrajin Trusmi, Cirebon.',
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
                    'description' => 'Potongan rapi dan sejuk untuk keseharian maupun acara formal.',
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
                    'description' => 'Pewarnaan sogan klasik di atas katun primis dengan karakter motif yang otentik.',
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
            'featured_title' => 'Harmoni Sogan & Mega Mendung',
            'featured_description' => 'Perpaduan motif pesisir Cirebon dan nuansa sogan hangat dalam kain lembaran, kemeja, serta selendang untuk beragam momen.',
            'featured_cta_label' => 'Lihat Koleksi Mega Mendung',
            'featured_cta_url' => '/katalog?motif=mega',
            'featured_image' => '/images/campaigns/featured-collection.jpg',
            'pay_bank_name' => 'Bank BCA',
            'pay_bank_account' => '1340 5829 10',
            'pay_bank_holder' => 'Batik Daffa Trusmi',
            'pay_bank_note' => 'Transfer sesuai nominal total pesanan. Admin akan memverifikasi pembayaran dan menghubungi Anda.',
            'pay_qris_image' => '/images/qris-default.svg',
            'pay_qris_merchant' => 'Batik Daffa · Trusmi Cirebon',
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
