<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopTest extends TestCase
{
    use RefreshDatabase;

    private function seedSampleProduct(array $overrides = []): Product
    {
        $cat = Category::firstOrCreate(['slug' => 'pakaian'], ['nama' => 'Pakaian']);

        return Product::create(array_merge([
            'category_id' => $cat->id,
            'nama' => 'Kemeja Mega Mendung Daffa',
            'slug' => 'kemeja-mega-mendung-daffa',
            'harga' => 350000,
            'motif' => 'mega',
            'c1' => '#4a2c16',
            'c2' => '#e6b871',
            'foto' => 'products/sample.jpg',
            'stok' => 5,
            'is_active' => true,
        ], $overrides));
    }

    public function test_home_includes_normalized_product_cards_with_kategori_and_storage_url(): void
    {
        $this->seedSampleProduct();

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('<title inertia>Batik Daffa</title>', false);
        $response->assertSee('&quot;kategori&quot;:&quot;pakaian&quot;', false);
        $response->assertSee('\/storage\/products\/sample.jpg', false);
    }

    public function test_orders_page_loads_and_tracks_order_code(): void
    {
        $product = $this->seedSampleProduct();

        $checkout = $this->post('/checkout', [
            'recipient_name' => 'Budi Santoso',
            'phone' => '081234567890',
            'shipping_address' => 'Jl. Trusmi Kulon No. 10, Cirebon',
            'payment_method' => 'transfer',
            'items' => [
                ['product_id' => $product->id, 'size' => 'L', 'qty' => 2],
            ],
        ]);

        $order = Order::firstOrFail();
        $checkout->assertRedirect(route('order.show', ['orderCode' => $order->order_code, 'new' => 1]));

        // Stok otomatis berkurang 5 -> 3
        $this->assertEquals(3, $product->fresh()->stok);

        // Halaman /pesanan tidak 404 dan bisa melacak kode pesanan
        $this->get('/pesanan')->assertStatus(200);
        $this->get('/pesanan?code=' . $order->order_code)
            ->assertStatus(200)
            ->assertSee($order->order_code);
    }

    public function test_checkout_rejects_quantity_exceeding_stock(): void
    {
        $product = $this->seedSampleProduct(['stok' => 2]);

        $response = $this->post('/checkout', [
            'recipient_name' => 'Siti',
            'phone' => '081234567890',
            'shipping_address' => 'Cirebon',
            'payment_method' => 'cod',
            'items' => [
                ['product_id' => $product->id, 'size' => 'M', 'qty' => 4],
            ],
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertEquals(2, $product->fresh()->stok);
    }

    public function test_order_recalculates_grand_total_when_shipping_cost_updated(): void
    {
        $order = Order::create([
            'order_code' => 'ORD-TEST01',
            'recipient_name' => 'Ani',
            'phone' => '081234567890',
            'shipping_address' => 'Cirebon',
            'payment_method' => 'transfer',
            'subtotal' => 250000,
            'shipping_cost' => 0,
            'grand_total' => 250000,
            'status' => 'baru',
        ]);

        $order->update(['shipping_cost' => 25000]);

        $this->assertEquals(275000, $order->fresh()->grand_total);
    }

    public function test_only_admin_can_access_filament_panel(): void
    {
        $panel = Filament::getPanel('admin');
        $buyer = User::factory()->create(['is_admin' => false]);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->assertFalse($buyer->canAccessPanel($panel));
        $this->assertTrue($admin->canAccessPanel($panel));
    }

    public function test_catalog_supports_price_stock_filters_and_best_sort(): void
    {
        $cheap = $this->seedSampleProduct([
            'nama' => 'Selendang Murah',
            'slug' => 'selendang-murah',
            'harga' => 120000,
            'stok' => 4,
        ]);
        $soldOut = $this->seedSampleProduct([
            'nama' => 'Kemeja Habis',
            'slug' => 'kemeja-habis',
            'harga' => 420000,
            'stok' => 0,
        ]);

        $this->get('/katalog?price=under-200')
            ->assertStatus(200)
            ->assertSee($cheap->nama)
            ->assertDontSee($soldOut->nama);

        $this->get('/katalog?stock=in_stock&sort=best')
            ->assertStatus(200)
            ->assertSee($cheap->nama)
            ->assertDontSee($soldOut->nama);
    }

    public function test_admin_dashboard_pages_render_and_sync_prices_and_new_products_to_web(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $cat = Category::firstOrCreate(['slug' => 'kain'], ['nama' => 'Kain']);

        $this->actingAs($admin, 'admin')
            ->get('/admin')
            ->assertStatus(200)
            ->assertSee('Tambah Produk Cepat ke Katalog Web')
            ->assertSee('Kelola Cepat Harga');

        // Sesi admin terpisah dari sesi pelanggan: halaman /login pelanggan tetap bisa diakses (tidak dianggap login pelanggan)
        $this->actingAs($admin, 'admin')
            ->get('/login')
            ->assertStatus(200);

        $this->actingAs($admin, 'admin')->get('/admin/products')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/products/create')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/categories')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/orders')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/hero-slides')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/tampilan-toko')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/pengaturan-pembayaran')->assertStatus(200);

        // Pengaturan rekening & QRIS tampil langsung di checkout
        \App\Models\SiteSetting::setValue('pay_bank_account', '9988 7766 55');
        $this->get('/checkout')
            ->assertStatus(200)
            ->assertSee('9988 7766 55');

        // Slide hero & banner kustom dari admin langsung tampil di Beranda
        \App\Models\HeroSlide::create([
            'title' => 'Kampanye Batik Daffa 2026',
            'description' => 'Koleksi eksklusif langsung dari admin.',
            'cta_label' => 'Lihat Sekarang',
            'cta_url' => '/katalog',
            'image' => 'banners/custom-hero.jpg',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        \App\Models\SiteSetting::setValue('featured_title', 'Koleksi Spesial Admin');
        \App\Models\SiteSetting::setValue('featured_image', 'banners/custom-featured.jpg');

        $this->get('/')
            ->assertStatus(200)
            ->assertSee('Kampanye Batik Daffa 2026')
            ->assertSee('\/storage\/banners\/custom-hero.jpg', false)
            ->assertSee('Koleksi Spesial Admin')
            ->assertSee('\/storage\/banners\/custom-featured.jpg', false);

        $product = Product::create([
            'category_id' => $cat->id,
            'nama' => 'Batik Tulis Daffa Eksklusif',
            'slug' => 'batik-tulis-daffa-eksklusif',
            'harga' => 450000,
            'motif' => 'mega',
            'c1' => '#6b3d1f',
            'c2' => '#e6b871',
            'stok' => 7,
            'is_active' => true,
            'is_best_seller' => true,
            'is_new' => true,
        ]);

        // Langsung tampil di beranda, katalog sesuai kategori (beserta label Best Seller & Baru), dan API sinkron harga
        $this->get('/')
            ->assertStatus(200)
            ->assertSee($product->nama)
            ->assertSee('&quot;is_best_seller&quot;:true', false)
            ->assertSee('&quot;is_new&quot;:true', false);
        $this->get('/katalog?cat=kain')
            ->assertStatus(200)
            ->assertSee($product->nama)
            ->assertSee('&quot;is_best_seller&quot;:true', false)
            ->assertSee('&quot;is_new&quot;:true', false);
        $this->getJson("/api/cart-sync?ids={$product->id}")
            ->assertOk()
            ->assertJsonPath('products.0.harga', 450000);

        // Update harga di admin langsung sinkron ke web & cart-sync API
        $product->update(['harga' => 495000]);

        $this->get('/produk/' . $product->slug)
            ->assertStatus(200)
            ->assertSee('495000');

        $this->getJson("/api/cart-sync?ids={$product->id}")
            ->assertOk()
            ->assertJsonPath('products.0.harga', 495000);

        // Route /storage/{path} menyajikan file foto yang sudah terupload di disk public
        \Illuminate\Support\Facades\Storage::disk('public')->put('products/test-sync.txt', 'ok');
        $this->get('/storage/products/test-sync.txt')->assertStatus(200);
        \Illuminate\Support\Facades\Storage::disk('public')->delete('products/test-sync.txt');
    }

    public function test_customer_can_confirm_order_completed_and_submit_star_review(): void
    {
        $product = $this->seedSampleProduct();

        $this->post('/checkout', [
            'recipient_name' => 'Rina Kartika',
            'phone' => '081234567890',
            'shipping_address' => 'Cirebon',
            'payment_method' => 'transfer',
            'items' => [
                ['product_id' => $product->id, 'size' => 'M', 'qty' => 1],
            ],
        ]);

        $order = Order::firstOrFail();
        $order->update(['status' => 'dikirim']);

        // Konsumen konfirmasi pesanan selesai
        $this->post("/pesanan/{$order->order_code}/selesai")
            ->assertRedirect(route('order.show', $order->order_code));

        $this->assertEquals('selesai', $order->fresh()->status);

        // Konsumen kirim bintang & ulasan
        $this->post("/pesanan/{$order->order_code}/ulasan", [
            'rating' => 5,
            'comment' => 'Kain halus dan motif Mega Mendung sangat rapi.',
        ])->assertRedirect(route('order.show', $order->order_code));

        $this->assertDatabaseHas('reviews', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Kain halus dan motif Mega Mendung sangat rapi.',
        ]);

        // Ulasan tampil di halaman detail produk
        $this->get("/produk/{$product->slug}")
            ->assertStatus(200)
            ->assertSee('Kain halus dan motif Mega Mendung sangat rapi.');
    }

    public function test_database_seeder_is_idempotent_for_production_deploys(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $first = Product::orderBy('id')->firstOrFail();
        $first->update(['harga' => 999000]);

        // Menjalankan seeder ulang tidak menimpa perubahan harga admin dan tidak menduplikasi data
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->assertEquals(999000, $first->fresh()->harga);
        $this->assertEquals(26, Product::count());
        $this->assertEquals(8, Product::where('is_best_seller', true)->count());
        $this->assertEquals(4, Product::where('is_new', true)->count());
        $this->assertEquals(3, \App\Models\HeroSlide::count());
        $this->assertEquals(3, Category::count());
    }

    public function test_company_profile_warranty_batik_filters_dp50_toggles_and_admin_nota(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->get('/tentang')->assertStatus(200)->assertSee('Shop\/About', false);
        $this->get('/bantuan/garansi')->assertStatus(200)->assertSee('garansi');

        $atbm = $this->seedSampleProduct([
            'nama' => 'Kain ATBM Singaan Eksklusif',
            'slug' => 'kain-atbm-singaan-eksklusif',
            'harga' => 3500000,
            'motif' => 'singaan',
            'bahan' => 'atbm',
            'teknik' => 'tulis',
            'gender' => 'pria',
            'stok' => 3,
        ]);

        $this->get('/katalog?motif=singaan&bahan=atbm&teknik=tulis&gender=pria&price=above-2000')
            ->assertStatus(200)
            ->assertSee($atbm->nama);

        // Toggle QRIS ON dan COD OFF dari pengaturan admin langsung tercermin di Checkout
        \App\Models\SiteSetting::setValue('pay_qris_enabled', '1');
        \App\Models\SiteSetting::setValue('pay_cod_enabled', '0');

        $this->get('/checkout')
            ->assertStatus(200)
            ->assertSee('&quot;ewallet&quot;:true', false)
            ->assertSee('&quot;cod&quot;:false', false);

        // Checkout dengan metode DP 50% & ukuran XXL berhasil
        $this->post('/checkout', [
            'recipient_name' => 'Hendra',
            'phone' => '081234567890',
            'shipping_address' => 'Jl. Raya Panembahan, Cirebon',
            'payment_method' => 'dp50',
            'items' => [
                ['product_id' => $atbm->id, 'size' => 'XXL', 'qty' => 1],
            ],
        ])->assertRedirect();

        $order = Order::latest('id')->firstOrFail();
        $this->assertEquals('dp50', $order->payment_method);

        // Cetak nota hanya bisa diakses oleh admin
        $this->get("/admin-nota/{$order->id}")->assertRedirect();
        $this->actingAs($admin, 'admin')
            ->get("/admin-nota/{$order->id}")
            ->assertStatus(200)
            ->assertSee($order->order_code)
            ->assertSee('Nominal DP 50%');
    }
}
