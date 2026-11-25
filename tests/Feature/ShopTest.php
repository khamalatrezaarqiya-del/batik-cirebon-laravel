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
}
