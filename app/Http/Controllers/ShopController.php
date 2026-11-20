<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    public function index(): Response
    {
        $featured = Product::query()
            ->where('is_active', true)
            ->with('category:id,slug,nama')
            ->orderBy('id')
            ->limit(8)
            ->get();

        $newest = Product::query()
            ->where('is_active', true)
            ->with('category:id,slug,nama')
            ->latest('id')
            ->limit(4)
            ->get();

        return Inertia::render('Shop/Home', [
            'featured' => $featured,
            'newest' => $newest,
            'categories' => Category::withCount([
                'products' => fn ($q) => $q->where('is_active', true),
            ])->get(),
        ]);
    }

    public function catalog(Request $request): Response
    {
        $cat = $request->string('cat')->toString() ?: 'all';
        $motif = $request->string('motif')->toString() ?: 'all';
        $term = trim($request->string('q')->toString());
        $sort = $request->string('sort')->toString() ?: 'new';

        $products = Product::query()
            ->where('is_active', true)
            ->with('category:id,slug,nama')
            ->when($cat !== 'all', fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $cat)))
            ->when($motif !== 'all', fn ($q) => $q->where('motif', $motif))
            ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('nama', 'like', "%{$term}%")
                ->orWhere('deskripsi', 'like', "%{$term}%")))
            ->when($sort === 'cheap', fn ($q) => $q->orderBy('harga'))
            ->when($sort === 'pricey', fn ($q) => $q->orderByDesc('harga'))
            ->when($sort === 'new', fn ($q) => $q->orderByDesc('id'))
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Product $p) => $this->card($p));

        return Inertia::render('Shop/Catalog', [
            'products' => $products,
            'categories' => Category::orderBy('id')->get(['slug', 'nama']),
            'filters' => compact('cat', 'motif', 'term', 'sort'),
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
            ->map(fn (Product $p) => $this->card($p));

        return Inertia::render('Shop/ProductDetail', [
            'product' => $this->card($product) + ['deskripsi' => $product->deskripsi, 'stok' => $product->stok],
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
            'auth' => auth()->user()?->only('id', 'name', 'email'),
        ]);
    }

    /** Keranjang hanya menyimpan id, jadi katalog ringkas dikirim agar harga/nama tidak basi. */
    private function cartCatalog()
    {
        return Product::query()
            ->with('category:id,slug,nama')
            ->get()
            ->map(fn (Product $p) => $this->card($p));
    }

    /**
     * Checkout ditangani server: keranjang dikirim dari klien, harga & total
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
            $ids = collect($data['items'])->pluck('product_id')->unique();
            $products = Product::whereIn('id', $ids)->get()->keyBy('id');

            $subtotal = 0;
            $lines = [];

            foreach ($data['items'] as $item) {
                $product = $products[$item['product_id']] ?? null;
                abort_if(! $product || ! $product->is_active, 422, 'Produk tidak tersedia.');

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

            $order = Order::create([
                'order_code' => 'ORD-' . strtoupper(Str::random(6)),
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

        return redirect()->route('order.show', $order->order_code);
    }

    public function orderShow(string $orderCode): Response
    {
        $order = Order::with('items.product:id,nama,slug,c1,c2,motif,foto')
            ->where('order_code', $orderCode)
            ->firstOrFail();

        return Inertia::render('Shop/OrderDetail', [
            'order' => $order,
        ]);
    }

    public function help(?string $topic = null): Response
    {
        return Inertia::render('Shop/Help', [
            'topic' => $topic ?? 'cara-belanja',
        ]);
    }

    /** Bentuk seragam yang dikirim ke React, meniru struktur data lama. */
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
            'foto' => $p->foto,
            'kategori' => $p->category?->slug,
            'kategori_nama' => $p->category?->nama,
        ];
    }
}
