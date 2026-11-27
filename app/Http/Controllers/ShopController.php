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
    private const MAX_QTY_PER_PRODUCT = 10;

    private const BLOCKED_PUBLIC_EXTENSIONS = [
        'svg', 'svgz', 'html', 'htm', 'xhtml', 'xml',
        'js', 'mjs', 'cjs', 'php', 'phtml', 'phar',
        'sh', 'bat', 'cmd', 'ps1', 'exe', 'dll',
    ];

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
                'id' => 'slide-'.$s->id,
                'image' => $s->resolvedImageUrl(),
                'alt' => $s->title,
                'focal' => $s->focal_point ?: '72% 45%',
                'headline' => $s->title,
                'description' => $s->description ?? '',
                'ctaLabel' => $s->cta_label ?: 'Jelajahi Koleksi',
                'ctaHref' => $this->sanitizeSafeHref($s->cta_url, '/katalog'),
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
            'ctaHref' => $this->sanitizeSafeHref(
                SiteSetting::getValue('featured_cta_url', '/katalog?motif=mega'),
                '/katalog?motif=mega'
            ),
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
        $cat = mb_substr($request->string('cat')->toString() ?: 'all', 0, 64);
        $motif = mb_substr($request->string('motif')->toString() ?: 'all', 0, 64);
        $price = mb_substr($request->string('price')->toString() ?: 'all', 0, 32);
        $stock = mb_substr($request->string('stock')->toString() ?: 'all', 0, 32);
        $term = mb_substr(trim($request->string('q')->toString()), 0, 100);
        $sort = mb_substr($request->string('sort')->toString() ?: 'new', 0, 32);

        $escapedTerm = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term);

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
                ->whereRaw("nama LIKE ? ESCAPE '!'", ["%{$escapedTerm}%"])
                ->orWhereRaw("COALESCE(deskripsi, '') LIKE ? ESCAPE '!'", ["%{$escapedTerm}%"])
                ->orWhereRaw("motif LIKE ? ESCAPE '!'", ["%{$escapedTerm}%"])))
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
            ->where(fn ($q) => $q
                ->where('slug', $slug)
                ->when(ctype_digit($slug), fn ($w) => $w->orWhere('id', (int) $slug)))
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
     * dihitung ulang di sini supaya tidak bisa dimanipulasi, dilengkapi proteksi idempotensi.
     */
    public function processCheckout(Request $request)
    {
        $data = $request->validate([
            'recipient_name' => ['required', 'string', 'max:120'],
            'phone' => [
                'required',
                'string',
                'min:9',
                'max:20',
                'regex:/^[0-9+\-\s()]+$/',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $digits = preg_replace('/\D/', '', (string) $value);
                    if (strlen($digits) < 9 || strlen($digits) > 15) {
                        $fail('Nomor telepon harus terdiri dari 9 hingga 15 digit angka.');
                    }
                },
            ],
            'shipping_address' => ['required', 'string', 'min:5', 'max:1000'],
            'note' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', 'in:transfer,ewallet,cod'],
            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.size' => ['nullable', 'string', 'in:S,M,L,XL'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:'.self::MAX_QTY_PER_PRODUCT],
            'idempotency_key' => ['nullable', 'string', 'min:8', 'max:64', 'regex:/^[A-Za-z0-9_\-]+$/'],
        ]);

        $cleanPhone = preg_replace('/\D/', '', $data['phone']);

        $idempotencyKey = $data['idempotency_key'] ?? null;
        if ($idempotencyKey !== null) {
            $existing = Order::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                $sameUser = (int) ($existing->user_id ?? 0) === (int) (auth('web')->id() ?? 0);
                $sameGuest = $existing->user_id !== null || $this->hasGuestSessionAccess($request, $existing);

                if ($sameUser && $sameGuest) {
                    return redirect()
                        ->route('order.show', ['orderCode' => $existing->order_code, 'new' => 1])
                        ->with('success', 'Pesanan berhasil dibuat!');
                }

                throw ValidationException::withMessages([
                    'idempotency_key' => 'Kunci idempotensi tidak valid atau sudah digunakan.',
                ]);
            }
        }

        $order = DB::transaction(function () use ($data, $cleanPhone, $idempotencyKey) {
            $ids = collect($data['items'])->pluck('product_id')->unique()->sort()->values();
            $products = Product::whereIn('id', $ids)->lockForUpdate()->get()->keyBy('id');

            $subtotal = 0;
            $lines = [];
            $requestedByProduct = [];
            $mergedItems = [];

            foreach ($data['items'] as $item) {
                $pid = (int) $item['product_id'];
                $qty = (int) $item['qty'];
                $size = filled($item['size'] ?? null) ? (string) $item['size'] : null;
                $lineKey = $pid.'#'.($size ?? '');

                $requestedByProduct[$pid] = ($requestedByProduct[$pid] ?? 0) + $qty;
                if (! isset($mergedItems[$lineKey])) {
                    $mergedItems[$lineKey] = [
                        'product_id' => $pid,
                        'size' => $size,
                        'qty' => $qty,
                    ];
                } else {
                    $mergedItems[$lineKey]['qty'] += $qty;
                }
            }

            foreach ($requestedByProduct as $pid => $totalQty) {
                $product = $products[$pid] ?? null;
                if (! $product || ! $product->is_active) {
                    throw ValidationException::withMessages([
                        'items' => 'Salah satu produk sudah tidak tersedia.',
                    ]);
                }
                if ($totalQty > self::MAX_QTY_PER_PRODUCT) {
                    throw ValidationException::withMessages([
                        'items' => "Maksimal pembelian {$product->nama} adalah ".self::MAX_QTY_PER_PRODUCT.' pcs per pesanan.',
                    ]);
                }
                if ($product->stok < $totalQty) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$product->nama} tersisa {$product->stok} pcs.",
                    ]);
                }
            }

            foreach ($mergedItems as $item) {
                $product = $products[$item['product_id']];
                $qty = (int) $item['qty'];
                $lineTotal = $product->harga * $qty;
                $subtotal += $lineTotal;

                $lines[] = [
                    'product_id' => $product->id,
                    'size' => $item['size'],
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
                'idempotency_key' => $idempotencyKey,
                'user_id' => auth('web')->id(),
                'recipient_name' => trim($data['recipient_name']),
                'phone' => $cleanPhone,
                'shipping_address' => trim($data['shipping_address']),
                'note' => filled($data['note'] ?? null) ? trim($data['note']) : null,
                'payment_method' => $data['payment_method'],
                'payment_status' => 'pending',
                'subtotal' => $subtotal,
                'shipping_cost' => 0,
                'grand_total' => $subtotal,
                'status' => 'baru',
            ]);

            $order->items()->createMany($lines);

            return $order;
        });

        if ($order->user_id === null) {
            $this->authorizeGuestOrderInSession($request, $order->order_code);
        }

        return redirect()
            ->route('order.show', ['orderCode' => $order->order_code, 'new' => 1])
            ->with('success', 'Pesanan berhasil dibuat!');
    }

    public function orders(Request $request): Response
    {
        $lookupCode = mb_substr(trim($request->string('code')->toString()), 0, 32);
        $lookupResult = null;
        $lookupNotFound = false;

        if ($lookupCode !== '') {
            $found = Order::with('items.product:id,nama,slug,c1,c2,motif,foto')
                ->where('order_code', strtoupper($lookupCode))
                ->first();

            if ($found === null || ($found->user_id !== null && ! $this->isOrderOwnerOrAdmin($request, $found))) {
                $lookupNotFound = true;
            } else {
                $canSeeFull = $this->canManageOrder($request, $found) || $this->isAdmin($request);
                $lookupResult = [
                    'id' => $found->id,
                    'order_code' => $found->order_code,
                    'created_at' => $found->created_at,
                    'recipient_name' => $canSeeFull ? $found->recipient_name : $this->maskName($found->recipient_name),
                    'status' => $found->status,
                    'payment_status' => $found->payment_status ?? 'pending',
                    'grand_total' => $found->grand_total,
                    'items' => $found->items->map(fn ($i) => [
                        'id' => $i->id,
                        'qty' => $i->qty,
                        'size' => $i->size,
                        'product' => $i->product ? [
                            'id' => $i->product->id,
                            'nama' => $i->product->nama,
                            'slug' => $i->product->slug,
                        ] : null,
                    ])->values(),
                ];
            }
        }

        $userOrders = auth('web')->check()
            ? Order::with('items.product:id,nama,slug')
                ->where('user_id', auth('web')->id())
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

    public function orderShow(Request $request, string $orderCode): Response
    {
        $order = Order::with(['items.product:id,nama,slug,c1,c2,motif,foto', 'reviews'])
            ->where('order_code', $orderCode)
            ->firstOrFail();

        if ($order->user_id !== null && ! $this->isOrderOwnerOrAdmin($request, $order)) {
            abort(403);
        }

        $order->items->each(function ($item) {
            if ($item->product && $item->product->foto) {
                $item->product->foto = $this->resolvePhotoUrl($item->product->foto);
            }
        });

        if ($order->user_id === null && ! $this->hasGuestSessionAccess($request, $order) && ! $this->isAdmin($request)) {
            return Inertia::render('Shop/OrderDetail', [
                'order' => $this->redactOrderForPublicView($order),
                'paymentInfo' => null,
                'requiresVerification' => true,
            ]);
        }

        return Inertia::render('Shop/OrderDetail', [
            'order' => $order,
            'paymentInfo' => $this->paymentInfo(),
            'requiresVerification' => false,
        ]);
    }

    /** Verifikasi nomor telepon penerima untuk membuka detail pesanan tamu dari sesi/perangkat baru. */
    public function verifyGuestOrder(Request $request, string $orderCode)
    {
        $order = Order::where('order_code', $orderCode)->firstOrFail();

        if ($order->user_id !== null) {
            abort(403);
        }

        $data = $request->validate([
            'phone' => ['required', 'string', 'min:6', 'max:24'],
        ]);

        $expected = $this->normalizePhoneForComparison((string) $order->phone);
        $provided = $this->normalizePhoneForComparison((string) $data['phone']);

        if ($expected === '' || ! hash_equals($expected, $provided)) {
            throw ValidationException::withMessages([
                'phone' => 'Nomor telepon tidak sesuai dengan data penerima pesanan ini.',
            ]);
        }

        $this->authorizeGuestOrderInSession($request, $order->order_code);

        return redirect()
            ->route('order.show', $order->order_code)
            ->with('success', 'Verifikasi berhasil! Detail lengkap pesanan ditampilkan.');
    }

    /** Konsumen mengonfirmasi pesanan diterima / selesai untuk membuka form rating & ulasan. */
    public function completeOrder(Request $request, string $orderCode)
    {
        $order = Order::where('order_code', $orderCode)->firstOrFail();

        if (! $this->canManageOrder($request, $order)) {
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

        if (! $this->canManageOrder($request, $order)) {
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
        $safeTopic = in_array($topic, ['cara-belanja', 'ukuran', 'pengiriman'], true)
            ? $topic
            : 'cara-belanja';

        return Inertia::render('Shop/Help', [
            'topic' => $safeTopic,
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
        if (str_contains($path, "\0")) {
            abort(404);
        }

        $clean = ltrim(str_replace('\\', '/', $path), '/');

        if ($clean === '' || str_contains($clean, '..') || preg_match('#(^|/)\.#', $clean)) {
            abort(404);
        }

        $ext = strtolower(pathinfo($clean, PATHINFO_EXTENSION));
        if ($ext === '' || in_array($ext, self::BLOCKED_PUBLIC_EXTENSIONS, true)) {
            abort(404);
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($clean)) {
            abort(404);
        }

        $rootReal = realpath($disk->path(''));
        $fileReal = realpath($disk->path($clean));

        if ($rootReal === false || $fileReal === false || ! is_file($fileReal)) {
            abort(404);
        }

        $rootPrefix = rtrim($rootReal, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        if (! str_starts_with($fileReal, $rootPrefix)) {
            abort(404);
        }

        return response()->file($fileReal, [
            'Cache-Control' => 'public, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
        ]);
    }

    private function generateOrderCode(): string
    {
        $alphabet = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $maxIdx = strlen($alphabet) - 1;

        do {
            $suffix = '';
            for ($i = 0; $i < 10; $i++) {
                $suffix .= $alphabet[random_int(0, $maxIdx)];
            }
            $code = 'ORD-'.$suffix;
        } while (Order::where('order_code', $code)->exists());

        return $code;
    }

    private function authorizeGuestOrderInSession(Request $request, string $orderCode): void
    {
        $authorized = (array) $request->session()->get('authorized_guest_orders', []);
        $authorized[$orderCode] = true;

        // Batasi ukuran sesi maksimal 25 kode pesanan terakhir
        if (count($authorized) > 25) {
            $authorized = array_slice($authorized, -25, null, true);
        }

        $request->session()->put('authorized_guest_orders', $authorized);
    }

    private function hasGuestSessionAccess(Request $request, Order $order): bool
    {
        $authorized = (array) $request->session()->get('authorized_guest_orders', []);

        return ($authorized[$order->order_code] ?? false) === true;
    }

    private function isAdmin(Request $request): bool
    {
        return (auth('admin')->check() && (bool) auth('admin')->user()?->is_admin)
            || (auth('web')->check() && (bool) auth('web')->user()?->is_admin);
    }

    private function isOrderOwnerOrAdmin(Request $request, Order $order): bool
    {
        if ($this->isAdmin($request)) {
            return true;
        }

        return $order->user_id !== null
            && auth('web')->check()
            && (int) auth('web')->id() === (int) $order->user_id;
    }

    private function canManageOrder(Request $request, Order $order): bool
    {
        if ($order->user_id !== null) {
            return auth('web')->check() && (int) auth('web')->id() === (int) $order->user_id;
        }

        return $this->hasGuestSessionAccess($request, $order);
    }

    private function normalizePhoneForComparison(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (str_starts_with($digits, '62')) {
            $digits = '0'.substr($digits, 2);
        } elseif (! str_starts_with($digits, '0') && $digits !== '') {
            $digits = '0'.$digits;
        }

        return $digits;
    }

    private function maskName(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];

        return collect($parts)
            ->filter()
            ->map(function (string $part) {
                $first = mb_substr($part, 0, 1);

                return $first.str_repeat('*', max(2, min(4, mb_strlen($part) - 1)));
            })
            ->implode(' ') ?: '***';
    }

    private function maskPhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) <= 3) {
            return '***';
        }

        return str_repeat('•', max(6, strlen($digits) - 3)).substr($digits, -3);
    }

    private function redactOrderForPublicView(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_code' => $order->order_code,
            'status' => $order->status,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status ?? 'pending',
            'recipient_name' => $this->maskName((string) $order->recipient_name),
            'phone' => $this->maskPhone((string) $order->phone),
            'shipping_address' => null,
            'note' => null,
            'subtotal' => $order->subtotal,
            'shipping_cost' => $order->shipping_cost,
            'grand_total' => $order->grand_total,
            'created_at' => $order->created_at,
            'items' => $order->items->map(fn ($i) => [
                'id' => $i->id,
                'qty' => $i->qty,
                'size' => $i->size,
                'line_total' => $i->line_total,
                'product' => $i->product ? [
                    'id' => $i->product->id,
                    'nama' => $i->product->nama,
                    'slug' => $i->product->slug,
                ] : null,
            ])->values(),
            'reviews' => [],
        ];
    }

    private function sanitizeSafeHref(?string $url, string $fallback): string
    {
        $trimmed = trim((string) $url);
        if ($trimmed === '') {
            return $fallback;
        }

        if (str_starts_with($trimmed, '/') && ! str_starts_with($trimmed, '//') && ! str_starts_with($trimmed, '/\\')) {
            return $trimmed;
        }

        if (preg_match('#^https://[^\s<>"\']+$#i', $trimmed) === 1) {
            return $trimmed;
        }

        return $fallback;
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
