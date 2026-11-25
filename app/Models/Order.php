<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    public const STATUSES = ['baru', 'diproses', 'dikirim', 'selesai', 'batal'];

    protected $fillable = [
        'order_code', 'user_id', 'recipient_name', 'phone', 'shipping_address',
        'note', 'payment_method', 'subtotal', 'shipping_cost', 'grand_total', 'status',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'shipping_cost' => 'integer',
        'grand_total' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Order $order) {
            $order->grand_total = max(0, (int) $order->subtotal + (int) $order->shipping_cost);
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
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
