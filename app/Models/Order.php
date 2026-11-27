<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;

    public const STATUSES = ['baru', 'diproses', 'dikirim', 'selesai', 'batal'];

    public const PAYMENT_STATUSES = [
        'pending',
        'paid',
        'failed',
        'expired',
        'cancelled',
        'refund_pending',
        'refunded',
    ];

    public const ALLOWED_STATUS_TRANSITIONS = [
        'baru' => ['diproses', 'dikirim', 'batal'],
        'diproses' => ['dikirim', 'selesai', 'batal'],
        'dikirim' => ['selesai', 'batal'],
        'selesai' => [],
        'batal' => [],
    ];

    public const ALLOWED_PAYMENT_TRANSITIONS = [
        'pending' => ['paid', 'failed', 'expired', 'cancelled'],
        'failed' => ['pending', 'cancelled'],
        'expired' => ['cancelled'],
        'paid' => ['refund_pending', 'refunded'],
        'refund_pending' => ['refunded', 'paid'],
        'refunded' => [],
        'cancelled' => [],
    ];

    protected $fillable = [
        'order_code',
        'idempotency_key',
        'user_id',
        'recipient_name',
        'phone',
        'shipping_address',
        'note',
        'payment_method',
        'payment_status',
        'subtotal',
        'shipping_cost',
        'grand_total',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'shipping_cost' => 'integer',
        'grand_total' => 'integer',
        'paid_at' => 'datetime',
        'stock_restored_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Order $order) {
            $order->subtotal = max(0, (int) $order->subtotal);
            $order->shipping_cost = max(0, (int) $order->shipping_cost);
            $order->grand_total = $order->subtotal + $order->shipping_cost;

            $order->status = $order->status ?: 'baru';
            $order->payment_status = $order->payment_status ?: 'pending';

            if (! in_array($order->status, self::STATUSES, true)) {
                throw new DomainException("Status pesanan '{$order->status}' tidak valid.");
            }

            if (! in_array($order->payment_status, self::PAYMENT_STATUSES, true)) {
                throw new DomainException("Status pembayaran '{$order->payment_status}' tidak valid.");
            }

            if ($order->exists && $order->isDirty('status')) {
                $fromStatus = (string) ($order->getOriginal('status') ?: 'baru');
                $toStatus = (string) $order->status;

                if (! $order->canTransitionStatusTo($toStatus, $fromStatus)) {
                    throw new DomainException("Transisi status pesanan dari '{$fromStatus}' ke '{$toStatus}' tidak diizinkan.");
                }

                if ($toStatus === 'batal' && ! $order->isDirty('payment_status')) {
                    $curPay = (string) ($order->getOriginal('payment_status') ?: 'pending');
                    if (in_array($curPay, ['pending', 'failed', 'expired'], true)) {
                        $order->payment_status = 'cancelled';
                    } elseif ($curPay === 'paid') {
                        $order->payment_status = 'refund_pending';
                    }
                }
            }

            if ($order->exists && $order->isDirty('payment_status')) {
                $fromPay = (string) ($order->getOriginal('payment_status') ?: 'pending');
                $toPay = (string) $order->payment_status;

                if (! $order->canTransitionPaymentTo($toPay, $fromPay)) {
                    throw new DomainException("Transisi status pembayaran dari '{$fromPay}' ke '{$toPay}' tidak diizinkan.");
                }
            }

            if ($order->payment_status === 'paid' && $order->paid_at === null) {
                $order->paid_at = now();
            }
        });

        static::saved(function (Order $order) {
            if ($order->wasChanged('status') && $order->status === 'batal' && $order->stock_restored_at === null) {
                $order->restoreStockAtomically();
            }
        });
    }

    public function canTransitionStatusTo(string $to, ?string $from = null): bool
    {
        $from ??= (string) ($this->getOriginal('status') ?: $this->status ?: 'baru');
        if ($to === $from) {
            return true;
        }

        return in_array($to, self::ALLOWED_STATUS_TRANSITIONS[$from] ?? [], true);
    }

    public function canTransitionPaymentTo(string $to, ?string $from = null): bool
    {
        $from ??= (string) ($this->getOriginal('payment_status') ?: $this->payment_status ?: 'pending');
        if ($to === $from) {
            return true;
        }

        return in_array($to, self::ALLOWED_PAYMENT_TRANSITIONS[$from] ?? [], true);
    }

    /**
     * Mengembalikan stok item secara atomik dan idempoten tepat satu kali saat pesanan dibatalkan.
     */
    public function restoreStockAtomically(): void
    {
        DB::transaction(function () {
            /** @var Order|null $locked */
            $locked = static::whereKey($this->id)->lockForUpdate()->first();
            if (! $locked || $locked->stock_restored_at !== null || $locked->status !== 'batal') {
                return;
            }

            $locked->load('items');
            foreach ($locked->items as $item) {
                if ($item->product_id && $item->qty > 0) {
                    Product::whereKey($item->product_id)->increment('stok', $item->qty);
                }
            }

            $now = now();
            $locked->forceFill(['stock_restored_at' => $now])->saveQuietly();
            $this->setRawAttributes(array_merge($this->getAttributes(), [
                'stock_restored_at' => $now,
            ]), true);
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getRouteKeyName(): string
    {
        return 'order_code';
    }
}
