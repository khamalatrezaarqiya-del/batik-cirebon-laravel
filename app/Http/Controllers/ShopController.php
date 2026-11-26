<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\HeroSlide;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    public function index(): Response
    {
        $hasManualBest = Product::where('is_active', true)->where('is_best_seller', true)->exists();
        $hasManualNew = Product::where('is_active', true)->where('is_new', true)->exists();

        $featured = Product::query()
            ->where('is_active', true)
            ->when($hasManualBest, fn ($q) => $q->where('is_best_seller', true))
            ->with('category:id,slug,nama')
            ->withSum('orderItems as sold_qty', 'qty')
            ->orderByDesc('is_best_seller')
            ->orderByDesc('sold_qty')
            ->orderBy('id')
            ->limit(8)
            ->get()
            ->map(fn (Product $p) => $this->card($p))
            ->values();

        $newest = Product::query()
            ->where('is_active', true)
            ->when($hasManualNew, fn ($q) => $q->where('is_new', true))
            ->with('category:id,slug,nama')
            ->latest('id')
            ->limit(4)
            ->get()
            ->map(fn (Product $p) => $this->card($p))
            ->values();

        $categories = Category::withCount([
            'products' => fn ($q) => $q->where('is_active', true),
        ])->orderBy('id')->get()->map(function (Category $c) {
            $fallback = match ($c->slug) {
                'kain' => '/images/categories/kain.jpg',
                'pakaian' => '/images/categories/pakaian.jpg',
                'aksesoris' => '/images/categories/aksesoris.jpg',
                default => '/images/categories/kain.jpg',
            };

            return [
                'id' => $c->id,
                'slug' => $c->slug,
                'nama' => $c->nama,
                'products_count' => $c->products_count,
                'image' => HeroSlide::resolveMediaUrl($c->image, $fallback),
            ];
        })->values();

        $heroSlides = HeroSlide::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (HeroSlide $s) => [
                'id' => 'slide-' . $s->id,
                'image' => $s->resolvedImageUrl(),
                'alt' => $s->title,
                'focal' => $s->focal_point ?: '72% 45%',
                'headline' => $s->title,
                'description' => $s->description ?? '',
                'ctaLabel' => $s->cta_label ?: 'Jelajahi Koleksi',
                'ctaHref' => $s->cta_url ?: '/katalog',
            ])
            ->values();

        $featuredBanner = [
            'label' => SiteSetting::getValue('featured_label', 'Koleksi Pilihan'),
            'title' => SiteSetting::getValue('featured_title', 'Harmoni Sogan & Mega Mendung'),
            'description' => SiteSetting::getValue(
                'featured_description',
                'Perpaduan motif pesisir Cirebon dan nuansa sogan hangat dalam kain lembaran, kemeja, serta selendang untuk beragam momen.'
            ),
            'ctaLabel' => SiteSetting::getValue('featured_cta_label', 'Lihat Koleksi Mega Mendung'),
            'ctaHref' => SiteSetting::getValue('featured_cta_url', '/katalog?motif=mega'),
            'image' => HeroSlide::resolveMediaUrl(
                SiteSetting::getValue('featured_image'),
                '/images/campaigns/featured-collection.jpg'
            ),
        ];

        return Inertia::render('Shop/Home', [
            'featured' => $featured,
            'newest' => $newest,
            'categories' => $categories,
            'heroSlides' => $heroSlides,
            'featuredBanner' => $featuredBanner,
        ]);
    }

    public function catalog(Request $request): Response
    {
        $cat = $request->string('cat')->toString() ?: 'all';
        $motif = $request->string('motif')->toString() ?: 'all';
        $price = $request->string('price')->toString() ?: 'all';
        $stock = $request->string('stock')->toString() ?: 'all';
        $term = trim($request->string('q')->toString());
        $sort = $request->string('sort')->toString() ?: 'new';

        $products = Product::query()
            ->where('is_active', true)
            ->with('category:id,slug,nama')
            ->when($cat !== 'all', fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $cat)))
            ->when($motif !== 'all', fn ($q) => $q->where('motif', $motif))
            ->when($price === 'under-200', fn ($q) => $q->where('harga', '<', 200000))
            ->when($price === '200-350', fn ($q) => $q->whereBetween('harga', [200000, 350000]))
            ->when($price === 'above-350', fn ($q) => $q->where('harga', '>', 350000))
            ->when($stock === 'in_stock', fn ($q) => $q->where('stok', '>', 0))
            ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('nama', 'like', "%{$term}%")
                ->orWhere('deskripsi', 'like', "%{$term}%")
                ->orWhere('motif', 'like', "%{$term}%")))
            ->when($sort === 'best', fn ($q) => $q
                ->withSum('orderItems as sold_qty', 'qty')
                ->orderByDesc('is_best_seller')
                ->orderByDesc('sold_qty')
                ->orderBy('id'))
            ->when($sort === 'cheap', fn ($q) => $q->orderBy('harga'))
            ->when($sort === 'pricey', fn ($q) => $q->orderByDesc('harga'))
            ->when(! in_array($sort, ['best', 'cheap', 'pricey'], true), fn ($q) => $q->orderByDesc('id'))
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Product $p) => $this->card($p));

        return Inertia::render('Shop/Catalog', [
            'products' => $products,
            'categories' => Category::withCount([
                'products' => fn ($q) => $q->where('is_active', true),
            ])->orderBy('id')->get(['id', 'slug', 'nama']),
            'filters' => compact('cat', 'motif', 'price', 'stock', 'term', 'sort'),
        ]);
    }

    public function show(string $slug): Response
    {
        $product = Product::query()
            ->where('is_active', true)
            ->with('category:id,slug,nama')
            ->where(fn ($q) => $q->where('slug', $slug)->orWhere('id', $slug))
            ->firstOrFail();

        $related = Product::query()
            ->where('is_active', true)
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->limit(4)
            ->with('category:id,slug,nama')
            ->get()
            ->map(fn (Product $p) => $this->card($p))
            ->values();

        $reviews = $product->reviews()
            ->latest()
            ->limit(20)
            ->get(['id', 'reviewer_name', 'rating', 'comment', 'created_at']);

        return Inertia::render('Shop/ProductDetail', [
            'product' => $this->card($product) + [
                'deskripsi' => $product->deskripsi,
                'stok' => $product->stok,
                'rating_avg' => $reviews->count() ? round((float) $reviews->avg('rating'), 1) : null,
                'reviews_count' => $reviews->count(),
            ],
            'reviews' => $reviews,
            'related' => $related,
        ]);
    }

    public function cart(): Response
    {
        return Inertia::render('Shop/Cart', [
            'products' => $this->cartCatalog(),
        ]);
    }

    public function checkoutPage(): Response
    {
        return Inertia::render('Shop/Checkout', [
            'products' => $this->cartCatalog(),
            'paymentInfo' => $this->paymentInfo(),
        ]);
    }

    /** Keranjang hanya menyimpan id, jadi katalog ringkas dikirim agar harga/nama tidak basi. */
    private function cartCatalog()
    {
        return Product::query()
            ->where('is_active', true)
            ->with('category:id,slug,nama')
            ->get()
            ->map(fn (Product $p) => $this->card($p))
            ->values();
    }

    /**
     * Checkout ditangani server: keranjang dikirim dari klien, harga, stok, dan total
     * dihitung ulang di sini supaya tidak bisa dimanipulasi.
     */
    public function processCheckout(Request $request)
    {
        $data = $request->validate([
            'recipient_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'min:9', 'max:20'],
            'shipping_address' => ['required', 'string', 'max:1000'],
            'note' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', 'in:transfer,ewallet,cod'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.size' => ['nullable', 'string', 'max:8'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $order = DB::transaction(function () use ($data) {
            $ids = collect($data['items'])->pluck('product_id')->unique()->sort()->values();
            $products = Product::whereIn('id', $ids)->lockForUpdate()->get()->keyBy('id');

            $subtotal = 0;
            $lines = [];
            $requestedByProduct = [];

            foreach ($data['items'] as $item) {
                $pid = (int) $item['product_id'];
                $qty = (int) $item['qty'];
                $requestedByProduct[$pid] = ($requestedByProduct[$pid] ?? 0) + $qty;
            }

            foreach ($requestedByProduct as $pid => $totalQty) {
                $product = $products[$pid] ?? null;
                if (! $product || ! $product->is_active) {
                    throw ValidationException::withMessages([
                        'items' => 'Salah satu produk sudah tidak tersedia.',
                    ]);
                }
                if ($product->stok < $totalQty) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$product->nama} tersisa {$product->stok} pcs.",
                    ]);
                }
            }

            foreach ($data['items'] as $item) {
                $product = $products[$item['product_id']];
                $qty = (int) $item['qty'];
                $lineTotal = $product->harga * $qty;
                $subtotal += $lineTotal;

                $lines[] = [
                    'product_id' => $product->id,
                    'size' => $item['size'] ?? null,
                    'qty' => $qty,
                    'price' => $product->harga,
                    'line_total' => $lineTotal,
                ];
            }

            foreach ($requestedByProduct as $pid => $totalQty) {
                $products[$pid]->decrement('stok', $totalQty);
            }

            $order = Order::create([
                'order_code' => $this->generateOrderCode(),
                'user_id' => auth()->id(),
                'recipient_name' => $data['recipient_name'],
                'phone' => preg_replace('/\D/', '', $data['phone']),
                'shipping_address' => $data['shipping_address'],
                'note' => $data['note'] ?? null,
                'payment_method' => $data['payment_method'],
                'subtotal' => $subtotal,
                'shipping_cost' => 0,
                'grand_total' => $subtotal,
                'status' => 'baru',
            ]);

            $order->items()->createMany($lines);

            return $order;
        });

        return redirect()
            ->route('order.show', ['orderCode' => $order->order_code, 'new' => 1])
            ->with('success', 'Pesanan berhasil dibuat!');
    }

    public function orders(Request $request): Response
    {
        $lookupCode = trim($request->string('code')->toString());
        $lookupResult = null;
        $lookupNotFound = false;

        if ($lookupCode !== '') {
            $lookupResult = Order::with('items.product:id,nama,slug,c1,c2,motif,foto')
                ->where('order_code', strtoupper($lookupCode))
                ->first();
            $lookupNotFound = $lookupResult === null;
        }

        $userOrders = auth()->check()
            ? Order::with('items.product:id,nama,slug')
                ->where('user_id', auth()->id())
                ->latest()
                ->get()
            : collect();

        return Inertia::render('Shop/Orders', [
            'orders' => $userOrders,
            'lookupCode' => $lookupCode,
            'lookupResult' => $lookupResult,
            'lookupNotFound' => $lookupNotFound,
        ]);
    }

    public function orderShow(string $orderCode): Response
    {
        $order = Order::with(['items.product:id,nama,slug,c1,c2,motif,foto', 'reviews'])
            ->where('order_code', $orderCode)
            ->firstOrFail();

        $order->items->each(function ($item) {
            if ($item->product && $item->product->foto) {
                $item->product->foto = $this->resolvePhotoUrl($item->product->foto);
            }
        });

        return Inertia::render('Shop/OrderDetail', [
            'order' => $order,
            'paymentInfo' => $this->paymentInfo(),
        ]);
    }

    /** Konsumen mengonfirmasi pesanan diterima / selesai untuk membuka form rating & ulasan. */
    public function completeOrder(Request $request, string $orderCode)
    {
        $order = Order::where('order_code', $orderCode)->firstOrFail();

        if ($order->user_id && auth('web')->check() && auth('web')->id() !== $order->user_id) {
            abort(403);
        }

        if (! in_array($order->status, ['diproses', 'dikirim'], true)) {
            return redirect()
                ->route('order.show', $order->order_code)
                ->withErrors(['status' => 'Pesanan hanya dapat dikonfirmasi selesai setelah diproses atau dikirim.']);
        }

        $order->update(['status' => 'selesai']);

        return redirect()
            ->route('order.show', $order->order_code)
            ->with('success', 'Pesanan selesai dikonfirmasi! Silakan berikan bintang dan ulasan Anda di bawah.');
    }

    /** Simpan bintang (1-5) & ulasan konsumen untuk pesanan yang sudah selesai. */
    public function storeReview(Request $request, string $orderCode)
    {
        $order = Order::with('items')->where('order_code', $orderCode)->firstOrFail();

        if ($order->user_id && auth('web')->check() && auth('web')->id() !== $order->user_id) {
            abort(403);
        }

        if ($order->status !== 'selesai') {
            return redirect()
                ->route('order.show', $order->order_code)
                ->withErrors(['review' => 'Konfirmasi pesanan selesai terlebih dahulu sebelum memberi ulasan.']);
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $productIds = $order->items->pluck('product_id')->unique()->filter();

        foreach ($productIds as $pid) {
            Review::updateOrCreate(
                ['order_id' => $order->id, 'product_id' => $pid],
                [
                    'user_id' => auth('web')->id() ?? $order->user_id,
                    'reviewer_name' => $order->recipient_name,
                    'rating' => (int) $data['rating'],
                    'comment' => filled($data['comment'] ?? null) ? trim($data['comment']) : null,
                ],
            );
        }

        return redirect()
            ->route('order.show', $order->order_code)
            ->with('success', 'Terima kasih! Bintang dan ulasan Anda berhasil disimpan.');
    }

    /** Informasi rekening bank, QRIS, dan COD yang dikelola dari Admin. */
    private function paymentInfo(): array
    {
        return [
            'bank' => [
                'name' => SiteSetting::getValue('pay_bank_name', 'Bank BCA'),
                'account' => SiteSetting::getValue('pay_bank_account', '1340 5829 10'),
                'holder' => SiteSetting::getValue('pay_bank_holder', 'Batik Daffa Trusmi'),
                'note' => SiteSetting::getValue(
                    'pay_bank_note',
                    'Transfer sesuai nominal total pesanan. Admin akan memverifikasi pembayaran dan menghubungi Anda.'
                ),
            ],
            'qris' => [
                'image' => HeroSlide::resolveMediaUrl(
                    SiteSetting::getValue('pay_qris_image'),
                    '/images/qris-default.svg'
                ),
                'merchant' => SiteSetting::getValue('pay_qris_merchant', 'Batik Daffa · Trusmi Cirebon'),
                'info' => SiteSetting::getValue(
                    'pay_ewallet_info',
                    'Scan QRIS melalui GoPay, OVO, DANA, ShopeePay, atau mobile banking.'
                ),
            ],
            'cod' => [
                'note' => SiteSetting::getValue(
                    'pay_cod_note',
                    'Bayar tunai langsung ke kurir saat paket tiba. Admin akan menghubungi Anda terlebih dahulu sebelum paket dikirim.'
                ),
            ],
        ];
    }

    public function help(?string $topic = null): Response
    {
        return Inertia::render('Shop/Help', [
            'topic' => $topic ?? 'cara-belanja',
        ]);
    }

    /** Sinkronisasi harga, nama, stok, dan foto terbaru dari DB untuk Mini Cart Drawer. */
    public function cartSync(Request $request)
    {
        $ids = collect(explode(',', (string) $request->query('ids', '')))
            ->map(fn ($v) => (int) trim($v))
            ->filter(fn ($v) => $v > 0)
            ->unique()
            ->take(50)
            ->values();

        if ($ids->isEmpty()) {
            return response()->json(['products' => []]);
        }

        $products = Product::query()
            ->where('is_active', true)
            ->whereIn('id', $ids)
            ->with('category:id,slug,nama')
            ->get()
            ->map(fn (Product $p) => $this->card($p))
            ->values();

        return response()->json(['products' => $products]);
    }

    /** Fallback aman untuk file disk public (/storage/...) bila symlink belum tersedia di OS lokal. */
    public function publicFile(string $path)
    {
        $clean = ltrim(str_replace('\\', '/', $path), '/');

        if ($clean === '' || str_contains($clean, '..')) {
            abort(404);
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($clean)) {
            abort(404);
        }

        return response()->file($disk->path($clean), [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private function generateOrderCode(): string
    {
        do {
            $code = 'ORD-' . strtoupper(Str::random(6));
        } while (Order::where('order_code', $code)->exists());

        return $code;
    }

    private function resolvePhotoUrl(?string $foto): ?string
    {
        if (! $foto) {
            return null;
        }

        if (Str::startsWith($foto, ['http://', 'https://', '/'])) {
            return $foto;
        }

        return Storage::disk('public')->url($foto);
    }

    /** Bentuk seragam yang dikirim ke React. */
    private function card(Product $p): array
    {
        return [
            'id' => $p->id,
            'nama' => $p->nama,
            'slug' => $p->slug,
            'harga' => $p->harga,
            'motif' => $p->motif,
            'c1' => $p->c1,
            'c2' => $p->c2,
            'foto' => $this->resolvePhotoUrl($p->foto),
            'stok' => $p->stok,
            'kategori' => $p->category?->slug,
            'kategori_nama' => $p->category?->nama,
            'is_best_seller' => (bool) $p->is_best_seller,
            'is_new' => (bool) $p->is_new,
        ];
    }
}
