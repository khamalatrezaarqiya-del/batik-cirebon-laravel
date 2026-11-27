<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\HeroSlide;
use App\Models\Order;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    private function createSampleProduct(array $overrides = []): Product
    {
        $catSlug = $overrides['cat_slug'] ?? 'pakaian';
        unset($overrides['cat_slug']);

        $category = Category::firstOrCreate(
            ['slug' => $catSlug],
            ['nama' => ucfirst($catSlug)]
        );

        return Product::create(array_merge([
            'category_id' => $category->id,
            'nama' => 'Batik Mega Mendung Uji',
            'slug' => 'batik-mega-mendung-uji-'.uniqid(),
            'harga' => 300000,
            'motif' => 'mega',
            'c1' => '#6b3d1f',
            'c2' => '#e6b871',
            'stok' => 10,
            'is_active' => true,
        ], $overrides));
    }

    /**
     * Scenario 1, 5, 6, 9: Forged payment status, altered totals, currency mismatch, and phantom coupon fields.
     */
    public function test_checkout_ignores_forged_payment_status_prices_currency_and_discounts(): void
    {
        $product = $this->createSampleProduct(['harga' => 350000, 'stok' => 8]);

        $response = $this->post('/checkout', [
            'recipient_name' => 'Raka Pratama',
            'phone' => '081298765432',
            'shipping_address' => 'Jl. Trusmi No. 12, Cirebon',
            'payment_method' => 'transfer',
            'payment_status' => 'paid',
            'status' => 'selesai',
            'currency' => 'USD',
            'subtotal' => 100,
            'shipping_cost' => -200000,
            'grand_total' => 100,
            'discount' => 300000,
            'coupon_code' => 'FREE100',
            'items' => [
                [
                    'product_id' => $product->id,
                    'size' => 'L',
                    'qty' => 2,
                    'price' => 1,
                    'line_total' => 2,
                ],
            ],
        ]);

        $order = Order::firstOrFail();
        $response->assertRedirect(route('order.show', ['orderCode' => $order->order_code, 'new' => 1]));

        $this->assertSame('baru', $order->status);
        $this->assertSame('pending', $order->payment_status);
        $this->assertNull($order->paid_at);
        $this->assertSame(700000, $order->subtotal);
        $this->assertSame(0, $order->shipping_cost);
        $this->assertSame(700000, $order->grand_total);
        $this->assertSame(6, $product->fresh()->stok);

        // Customer cannot mark a 'baru' (unprocessed) order as completed
        $this->post("/pesanan/{$order->order_code}/selesai")
            ->assertSessionHasErrors('status');
        $this->assertSame('baru', $order->fresh()->status);
    }

    /**
     * Scenario 2: Verify no unverified payment callback/webhook endpoint exists.
     */
    public function test_unverified_webhook_endpoints_do_not_exist_and_cannot_mark_order_paid(): void
    {
        $product = $this->createSampleProduct();
        $this->post('/checkout', [
            'recipient_name' => 'Dewi',
            'phone' => '081234567891',
            'shipping_address' => 'Jl. Kartini No. 5, Cirebon',
            'payment_method' => 'ewallet',
            'items' => [['product_id' => $product->id, 'size' => 'M', 'qty' => 1]],
        ]);

        $order = Order::firstOrFail();

        foreach (['/webhook/payment', '/api/webhook', '/payment/callback', '/midtrans/notification'] as $uri) {
            $this->postJson($uri, [
                'order_id' => $order->order_code,
                'transaction_status' => 'settlement',
            ])->assertStatus(404);
        }

        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    /**
     * Scenario 3: Idempotent checkout protection and idempotent stock restoration on cancellation.
     */
    public function test_duplicate_checkout_with_idempotency_key_and_duplicate_stock_restoration_are_idempotent(): void
    {
        $product = $this->createSampleProduct(['stok' => 10]);
        $payload = [
            'recipient_name' => 'Andi Wijaya',
            'phone' => '081311223344',
            'shipping_address' => 'Jl. Tuparev No. 88, Cirebon',
            'payment_method' => 'transfer',
            'idempotency_key' => 'idem-key-12345678',
            'items' => [['product_id' => $product->id, 'size' => 'XL', 'qty' => 3]],
        ];

        $first = $this->post('/checkout', $payload);
        $second = $this->post('/checkout', $payload);

        $this->assertSame(1, Order::count());
        $order = Order::firstOrFail();
        $first->assertRedirect(route('order.show', ['orderCode' => $order->order_code, 'new' => 1]));
        $second->assertRedirect(route('order.show', ['orderCode' => $order->order_code, 'new' => 1]));
        $this->assertSame(7, $product->fresh()->stok);

        // Cancelling the order restores stock once and sets payment_status to cancelled
        $order->update(['status' => 'batal']);
        $this->assertSame(10, $product->fresh()->stok);
        $this->assertSame('cancelled', $order->fresh()->payment_status);
        $this->assertNotNull($order->fresh()->stock_restored_at);

        // Calling restoreStockAtomically again does NOT double-increment stock
        $order->fresh()->restoreStockAtomically();
        $this->assertSame(10, $product->fresh()->stok);
    }

    /**
     * Scenario 4: Strict server-side payment and order state machine enforcement.
     */
    public function test_state_machine_blocks_invalid_payment_and_order_transitions(): void
    {
        $product = $this->createSampleProduct(['stok' => 5]);
        $this->post('/checkout', [
            'recipient_name' => 'Sari',
            'phone' => '081299887766',
            'shipping_address' => 'Jl. Siliwangi No. 20, Cirebon',
            'payment_method' => 'transfer',
            'items' => [['product_id' => $product->id, 'size' => 'S', 'qty' => 1]],
        ]);

        $order = Order::firstOrFail();

        // Negative shipping cost is clamped to 0
        $order->update(['shipping_cost' => -50000]);
        $this->assertSame(0, $order->fresh()->shipping_cost);
        $this->assertSame($product->harga, $order->fresh()->grand_total);

        // Valid payment progression: pending -> paid -> refund_pending -> refunded
        $order->update(['payment_status' => 'paid']);
        $this->assertNotNull($order->fresh()->paid_at);
        $order->update(['payment_status' => 'refund_pending']);
        $order->update(['payment_status' => 'refunded']);

        // Refunded cannot revert back to paid
        $this->expectException(DomainException::class);
        $order->fresh()->update(['payment_status' => 'paid']);
    }

    public function test_cancelled_or_completed_order_cannot_transition_back_to_active(): void
    {
        $order = Order::create([
            'order_code' => 'ORD-STATE00001',
            'recipient_name' => 'Tono',
            'phone' => '081234567890',
            'shipping_address' => 'Cirebon',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'subtotal' => 200000,
            'shipping_cost' => 0,
            'grand_total' => 200000,
            'status' => 'baru',
        ]);

        $order->update(['status' => 'batal']);

        $this->expectException(DomainException::class);
        $order->fresh()->update(['status' => 'diproses']);
    }

    /**
     * Scenario 7: Unauthorized order access (IDOR / BOLA) and auth bypass prevention.
     */
    public function test_registered_user_orders_prevent_idor_and_unauthenticated_completion_bypass(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $product = $this->createSampleProduct();

        $this->actingAs($owner)->post('/checkout', [
            'recipient_name' => 'Pemilik Asli',
            'phone' => '081277778888',
            'shipping_address' => 'Alamat Rahasia Pemilik No. 99',
            'payment_method' => 'transfer',
            'items' => [['product_id' => $product->id, 'size' => 'M', 'qty' => 1]],
        ]);

        $order = Order::firstOrFail();
        $order->update(['status' => 'dikirim']);

        // Another logged-in user cannot view, lookup, complete, or review the order
        $this->actingAs($otherUser)->get("/pesanan/{$order->order_code}")->assertForbidden();
        $this->actingAs($otherUser)->get("/pesanan?code={$order->order_code}")
            ->assertOk()
            ->assertDontSee('Alamat Rahasia Pemilik')
            ->assertDontSee('Pemilik Asli');
        $this->actingAs($otherUser)->post("/pesanan/{$order->order_code}/selesai")->assertForbidden();
        $this->actingAs($otherUser)->post("/pesanan/{$order->order_code}/ulasan", ['rating' => 1])->assertForbidden();

        // Unauthenticated visitor ALSO cannot view, complete, or review a registered user's order
        auth('web')->logout();
        $this->flushSession();

        $this->get("/pesanan/{$order->order_code}")->assertForbidden();
        $this->post("/pesanan/{$order->order_code}/selesai")->assertForbidden();
        $this->post("/pesanan/{$order->order_code}/ulasan", ['rating' => 1])->assertForbidden();
        $this->assertSame('dikirim', $order->fresh()->status);
    }

    public function test_guest_order_redacts_pii_for_unverified_sessions_until_phone_verified(): void
    {
        $product = $this->createSampleProduct();

        $this->post('/checkout', [
            'recipient_name' => 'Tamu Rahasia',
            'phone' => '081355556666',
            'shipping_address' => 'Perumahan Griya Trusmi Blok A1 No. 7',
            'payment_method' => 'transfer',
            'items' => [['product_id' => $product->id, 'size' => 'L', 'qty' => 1]],
        ]);

        $order = Order::firstOrFail();
        $order->update(['status' => 'dikirim']);

        // Simulate a completely separate visitor/session without session token
        $this->flushSession();

        $unverifiedView = $this->get("/pesanan/{$order->order_code}");
        $unverifiedView->assertOk();
        $unverifiedView->assertDontSee('Perumahan Griya Trusmi Blok A1 No. 7');
        $unverifiedView->assertDontSee('081355556666');
        $unverifiedView->assertDontSee('Tamu Rahasia');

        // Unverified visitor cannot mark guest order complete or submit review
        $this->post("/pesanan/{$order->order_code}/selesai")->assertForbidden();

        // Wrong phone rejected
        $this->post("/pesanan/{$order->order_code}/verifikasi", ['phone' => '089900001111'])
            ->assertSessionHasErrors('phone');

        // Correct phone (+62 format normalized) unlocks full access
        $this->post("/pesanan/{$order->order_code}/verifikasi", ['phone' => '6281355556666'])
            ->assertRedirect(route('order.show', $order->order_code));

        $this->get("/pesanan/{$order->order_code}")
            ->assertOk()
            ->assertSee('Perumahan Griya Trusmi Blok A1 No. 7');

        $this->post("/pesanan/{$order->order_code}/selesai")
            ->assertRedirect(route('order.show', $order->order_code));
        $this->assertSame('selesai', $order->fresh()->status);
    }

    /**
     * Scenario 8: Unauthorized admin actions, mass assignment protection, and seeder safety.
     */
    public function test_admin_routes_and_mass_assignment_and_seeder_prevent_privilege_escalation(): void
    {
        // Mass assignment of is_admin on User::create must be ignored
        $created = User::create([
            'name' => 'Regular Buyer',
            'email' => 'buyer@example.com',
            'password' => 'SecretPass123!',
            'is_admin' => true,
        ]);
        $this->assertFalse((bool) $created->fresh()->is_admin);

        // Regular buyer cannot access Filament admin endpoints even when authenticated on web or admin guard
        $this->actingAs($created, 'web')->get('/admin')->assertRedirect('/admin/login');
        $this->actingAs($created, 'admin')->get('/admin')->assertForbidden();
        $this->actingAs($created, 'admin')->get('/admin/orders')->assertForbidden();
        $this->actingAs($created, 'admin')->get('/admin/pengaturan-pembayaran')->assertForbidden();

        // DatabaseSeeder must not create any default hardcoded admin user when env vars are empty
        $this->seed(DatabaseSeeder::class);
        $this->assertSame(0, User::where('is_admin', true)->count());
    }

    /**
     * Scenario 10, 11, 12: Quantity cap split bypass, invalid checkout inputs, and cart-sync safety.
     */
    public function test_checkout_blocks_split_quantity_cap_bypass_and_invalid_inputs(): void
    {
        $product = $this->createSampleProduct(['stok' => 20]);
        $inactive = $this->createSampleProduct(['is_active' => false, 'stok' => 10]);

        // Splitting 6 + 6 = 12 across two lines of the same product must be rejected (> MAX_QTY 10)
        $splitBypass = $this->post('/checkout', [
            'recipient_name' => 'Buyer',
            'phone' => '081234567890',
            'shipping_address' => 'Jl. Contoh No. 10, Cirebon',
            'payment_method' => 'cod',
            'items' => [
                ['product_id' => $product->id, 'size' => 'S', 'qty' => 6],
                ['product_id' => $product->id, 'size' => 'M', 'qty' => 6],
            ],
        ]);
        $splitBypass->assertSessionHasErrors('items');
        $this->assertSame(20, $product->fresh()->stok);

        // Non-digit phone ('---------') and invalid size ('XXXL') rejected
        $invalidFields = $this->post('/checkout', [
            'recipient_name' => 'Buyer',
            'phone' => '------------',
            'shipping_address' => 'Jl. Contoh No. 10, Cirebon',
            'payment_method' => 'cod',
            'items' => [
                ['product_id' => $product->id, 'size' => 'XXXL', 'qty' => 1],
            ],
        ]);
        $invalidFields->assertSessionHasErrors(['phone', 'items.0.size']);

        // Cart sync excludes inactive products
        $this->getJson("/api/cart-sync?ids={$product->id},{$inactive->id}")
            ->assertOk()
            ->assertJsonCount(1, 'products')
            ->assertJsonPath('products.0.id', $product->id);
    }

    /**
     * Scenario 13: Controlled rate limiting verification.
     */
    public function test_rate_limiting_throttles_excessive_guest_verification_requests(): void
    {
        $product = $this->createSampleProduct();
        $this->post('/checkout', [
            'recipient_name' => 'Pelanggan Uji',
            'phone' => '081233334444',
            'shipping_address' => 'Jl. Pemuda No. 15, Cirebon',
            'payment_method' => 'cod',
            'items' => [['product_id' => $product->id, 'size' => 'M', 'qty' => 1]],
        ]);
        $order = Order::firstOrFail();
        $this->flushSession();

        for ($i = 0; $i < 5; $i++) {
            $this->post("/pesanan/{$order->order_code}/verifikasi", ['phone' => '080000000000'])
                ->assertStatus(302);
        }

        // 6th attempt within 1 minute must be throttled with HTTP 429
        $this->post("/pesanan/{$order->order_code}/verifikasi", ['phone' => '080000000000'])
            ->assertStatus(429);
    }

    /**
     * Scenario 14: Password reset enumeration protection, session invalidation on password change, and security headers.
     */
    public function test_password_reset_prevents_enumeration_and_password_update_purges_other_sessions_and_headers_set(): void
    {
        $user = User::factory()->create(['email' => 'registered@example.com']);

        $resExisting = $this->from('/forgot-password')->post('/forgot-password', ['email' => 'registered@example.com']);
        $resMissing = $this->from('/forgot-password')->post('/forgot-password', ['email' => 'nonexistent@example.com']);

        $resExisting->assertRedirect('/forgot-password')->assertSessionHasNoErrors();
        $resMissing->assertRedirect('/forgot-password')->assertSessionHasNoErrors();
        $this->assertSame(session('status'), __(
            Password::RESET_LINK_SENT
        ));

        // Insert another active session row in DB for this user and verify password change deletes it
        DB::table('sessions')->insert([
            'id' => 'other-device-session-id',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.2',
            'user_agent' => 'OtherBrowser',
            'payload' => base64_encode('test'),
            'last_activity' => time(),
        ]);

        $this->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'password',
                'password' => 'NewStrongPass123!',
                'password_confirmation' => 'NewStrongPass123!',
            ])
            ->assertRedirect('/profile')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('sessions', ['id' => 'other-device-session-id']);

        // Security headers & Ziggy admin route hiding on storefront
        $home = $this->get('/');
        $home->assertOk();
        $home->assertHeader('X-Content-Type-Options', 'nosniff');
        $home->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $home->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertNotEmpty($home->headers->get('Content-Security-Policy'));
        $home->assertDontSee('filament.admin.');
    }

    /**
     * Scenario 15: SQL LIKE wildcard escaping, CTA URL scheme sanitization, and public file serving protection.
     */
    public function test_input_sanitization_wildcard_escaping_cta_scheme_and_public_storage_hardening(): void
    {
        $this->createSampleProduct(['nama' => 'Batik Normal Trusmi', 'slug' => 'batik-normal']);
        $this->createSampleProduct(['nama' => 'Promo 100% Asli', 'slug' => 'batik-persen']);

        // Searching literal '%' only matches 'Promo 100% Asli', NOT every product in catalog
        $search = $this->get('/katalog?q=%25');
        $search->assertOk();
        $search->assertSee('Promo 100% Asli');
        $search->assertDontSee('Batik Normal Trusmi');

        // Dangerous javascript: or protocol-relative CTA URLs are neutralized to safe internal fallback
        HeroSlide::create([
            'title' => 'Slide Uji',
            'cta_label' => 'Klik',
            'cta_url' => 'javascript:alert(1)',
            'image' => '/images/campaigns/hero-1.jpg',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        SiteSetting::setValue('featured_cta_url', '//evil.example.com/phish');

        $home = $this->get('/');
        $home->assertOk();
        $home->assertDontSee('javascript:alert(1)', false);
        $home->assertDontSee('//evil.example.com/phish', false);

        // Public storage blocks SVG/HTML/PHP and dotfiles while serving safe static files with nosniff
        Storage::disk('public')->put('banners/unsafe.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>');
        Storage::disk('public')->put('banners/safe.txt', 'safe-text');

        $this->get('/storage/banners/unsafe.svg')->assertNotFound();
        $this->get('/storage/.gitignore')->assertNotFound();
        $this->get('/storage/..%2F.env')->assertNotFound();

        $safeRes = $this->get('/storage/banners/safe.txt');
        $safeRes->assertOk();
        $safeRes->assertHeader('X-Content-Type-Options', 'nosniff');

        Storage::disk('public')->delete(['banners/unsafe.svg', 'banners/safe.txt']);
    }
}
