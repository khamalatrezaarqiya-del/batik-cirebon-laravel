<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Penjualan';

    protected static ?string $modelLabel = 'Pesanan';

    protected static ?string $pluralModelLabel = 'Pesanan';

    protected static ?int $navigationSort = 1;

    private const STATUS_LABELS = [
        'baru' => 'Baru',
        'diproses' => 'Diproses',
        'dikirim' => 'Dikirim',
        'selesai' => 'Selesai',
        'batal' => 'Batal',
    ];

    private const PAYMENT_LABELS = [
        'pending' => 'Menunggu Bayar',
        'paid' => 'Lunas',
        'failed' => 'Gagal',
        'expired' => 'Kedaluwarsa',
        'cancelled' => 'Dibatalkan',
        'refund_pending' => 'Menunggu Refund',
        'refunded' => 'Direfund',
    ];

    public static function allowedStatusOptions(?Order $record): array
    {
        if (! $record || ! $record->exists) {
            return self::STATUS_LABELS;
        }

        $current = $record->status ?: 'baru';
        $allowed = array_unique(array_merge([$current], Order::ALLOWED_STATUS_TRANSITIONS[$current] ?? []));

        return array_intersect_key(self::STATUS_LABELS, array_flip($allowed));
    }

    public static function allowedPaymentOptions(?Order $record): array
    {
        if (! $record || ! $record->exists) {
            return self::PAYMENT_LABELS;
        }

        $current = $record->payment_status ?: 'pending';
        $allowed = array_unique(array_merge([$current], Order::ALLOWED_PAYMENT_TRANSITIONS[$current] ?? []));

        return array_intersect_key(self::PAYMENT_LABELS, array_flip($allowed));
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Order::where('status', 'baru')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Penerima')->schema([
                Forms\Components\TextInput::make('order_code')
                    ->label('Kode pesanan')
                    ->disabled()
                    ->dehydrated(false),

                Forms\Components\TextInput::make('recipient_name')
                    ->label('Nama penerima')
                    ->maxLength(120)
                    ->required(),

                Forms\Components\TextInput::make('phone')
                    ->label('Nomor HP')
                    ->tel()
                    ->maxLength(20)
                    ->required(),

                Forms\Components\Textarea::make('shipping_address')
                    ->label('Alamat pengiriman')
                    ->rows(3)
                    ->maxLength(1000)
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('note')
                    ->label('Catatan pembeli')
                    ->rows(2)
                    ->maxLength(1000)
                    ->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Pembayaran & status')->schema([
                Forms\Components\Select::make('payment_method')
                    ->label('Metode bayar')
                    ->options([
                        'transfer' => 'Transfer Bank (Lunas)',
                        'dp50' => 'DP 50% (Transfer Bank)',
                        'ewallet' => 'QRIS / E-Wallet',
                        'cod' => 'COD',
                    ])
                    ->required(),

                Forms\Components\Select::make('payment_status')
                    ->label('Status pembayaran')
                    ->options(fn (?Order $record) => self::allowedPaymentOptions($record))
                    ->required(),

                Forms\Components\Select::make('status')
                    ->label('Status pesanan')
                    ->options(fn (?Order $record) => self::allowedStatusOptions($record))
                    ->required(),

                Forms\Components\TextInput::make('subtotal')
                    ->label('Subtotal')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled()
                    ->dehydrated(false),

                Forms\Components\TextInput::make('shipping_cost')
                    ->label('Ongkir')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(10000000)
                    ->prefix('Rp')
                    ->default(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                        $safeShipping = max(0, (int) ($state ?: 0));
                        $set('grand_total', (int) $get('subtotal') + $safeShipping);
                    }),

                Forms\Components\TextInput::make('grand_total')
                    ->label('Total')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Otomatis dihitung ulang (subtotal + ongkir) saat disimpan.'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_code')
                    ->label('Kode')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('recipient_name')
                    ->label('Penerima')
                    ->searchable()
                    ->description(fn (Order $r) => $r->phone),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Item')
                    ->counts('items')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metode')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'transfer' => 'Transfer',
                        'dp50' => 'DP 50%',
                        'ewallet' => 'QRIS',
                        'cod' => 'COD',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Pembayaran')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => self::PAYMENT_LABELS[$state ?: 'pending'] ?? ($state ?: 'pending'))
                    ->color(fn (?string $state) => match ($state ?: 'pending') {
                        'paid' => 'success',
                        'pending', 'refund_pending' => 'warning',
                        'failed', 'expired', 'cancelled' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\SelectColumn::make('status')
                    ->label('Status')
                    ->options(fn (Order $record) => self::allowedStatusOptions($record))
                    ->selectablePlaceholder(false),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Masuk')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('reviews_avg_rating')
                    ->label('Rating')
                    ->avg('reviews', 'rating')
                    ->formatStateUsing(fn ($state) => $state ? number_format((float) $state, 1).' / 5' : '—')
                    ->badge()
                    ->color(fn ($state) => $state ? 'warning' : 'gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(self::STATUS_LABELS),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Pembayaran')
                    ->options(self::PAYMENT_LABELS),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('dari'),
                        Forms\Components\DatePicker::make('sampai'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['dari'], fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                            ->when($data['sampai'], fn ($q, $d) => $q->whereDate('created_at', '<=', $d));
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('exportOrdersCsv')
                    ->label('Export Rekap (Excel/CSV)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function () {
                        $filename = 'rekap-pesanan-batik-daffa-' . now()->format('Y-m-d') . '.csv';

                        return response()->streamDownload(function () {
                            $out = fopen('php://output', 'w');
                            fwrite($out, "\xEF\xBB\xBF");
                            fputcsv($out, ['Kode Pesanan', 'Tanggal', 'Penerima', 'No HP', 'Alamat', 'Item', 'Metode Bayar', 'Status Bayar', 'Status Pesanan', 'Subtotal (Rp)', 'Ongkir (Rp)', 'Total (Rp)', 'Catatan']);

                            Order::with('items.product:id,nama')->latest('created_at')->chunk(100, function ($rows) use ($out) {
                                foreach ($rows as $o) {
                                    $items = $o->items->map(fn ($i) => ($i->product?->nama ?? 'Produk') . ($i->size ? " ({$i->size})" : '') . " x{$i->qty}")->implode('; ');
                                    fputcsv($out, [
                                        $o->order_code,
                                        $o->created_at?->format('Y-m-d H:i'),
                                        $o->recipient_name,
                                        $o->phone,
                                        $o->shipping_address,
                                        $items,
                                        match ($o->payment_method) {
                                            'transfer' => 'Transfer Bank',
                                            'dp50' => 'DP 50% (Transfer)',
                                            'ewallet' => 'QRIS / E-Wallet',
                                            'cod' => 'COD',
                                            default => $o->payment_method,
                                        },
                                        self::PAYMENT_LABELS[$o->payment_status ?: 'pending'] ?? $o->payment_status,
                                        self::STATUS_LABELS[$o->status] ?? $o->status,
                                        $o->subtotal,
                                        $o->shipping_cost,
                                        $o->grand_total,
                                        $o->note ?? '',
                                    ]);
                                }
                            });

                            fclose($out);
                        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('markPaid')
                    ->label('Verifikasi Lunas')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (Order $r) => ($r->payment_status ?: 'pending') === 'pending' && $r->status !== 'batal')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Order $r) => "Tandai Pembayaran {$r->order_code} Lunas?")
                    ->modalDescription('Pastikan mutasi rekening atau QRIS sesuai dengan nominal pesanan sebelum memverifikasi pembayaran.')
                    ->action(function (Order $record): void {
                        $updates = ['payment_status' => 'paid'];
                        if ($record->status === 'baru') {
                            $updates['status'] = 'diproses';
                        }
                        $record->update($updates);

                        Notification::make()
                            ->title("Pembayaran {$record->order_code} terverifikasi lunas")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('confirmOrder')
                    ->label('Konfirmasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('primary')
                    ->visible(fn (Order $r) => $r->status === 'baru')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Order $r) => "Konfirmasi Pesanan {$r->order_code}?")
                    ->modalDescription('Status pesanan akan diubah menjadi Diproses. Setelah itu Anda dapat langsung menghubungi pelanggan via WhatsApp.')
                    ->action(function (Order $record): void {
                        $record->update(['status' => 'diproses']);

                        Notification::make()
                            ->title("Pesanan {$record->order_code} dikonfirmasi")
                            ->body("Status diubah ke Diproses. Klik tombol di bawah untuk mengabari {$record->recipient_name} via WhatsApp.")
                            ->success()
                            ->actions([
                                NotificationAction::make('contactCustomer')
                                    ->label('Hubungi Pelanggan via WA')
                                    ->url(self::customerWaUrl($record), shouldOpenInNewTab: true),
                            ])
                            ->send();
                    }),

                Tables\Actions\Action::make('wa')
                    ->label('Hubungi WA')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->url(fn (Order $r) => self::customerWaUrl($r), shouldOpenInNewTab: true),

                Tables\Actions\Action::make('printNota')
                    ->label('Nota')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn (Order $r) => route('admin.orders.nota', $r), shouldOpenInNewTab: true),

                Tables\Actions\ViewAction::make()->label('Lihat'),
                Tables\Actions\EditAction::make()->label('Edit'),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }

    /** Template pesan WhatsApp dari Admin ke Customer sesuai status pesanan. */
    public static function customerWaUrl(Order $order): string
    {
        $order->loadMissing('items.product:id,nama');

        $phone = preg_replace('/\D/', '', (string) $order->phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        } elseif (! str_starts_with($phone, '62')) {
            $phone = '62'.$phone;
        }

        $itemsText = $order->items
            ->map(fn ($i) => ($i->product?->nama ?? 'Produk').($i->size ? " ({$i->size})" : '')." x{$i->qty}")
            ->implode(', ');

        $total = 'Rp '.number_format((int) $order->grand_total, 0, ',', '.');
        $method = match ($order->payment_method) {
            'transfer' => 'Transfer Bank (Lunas)',
            'dp50' => 'DP 50% (Transfer Bank)',
            'ewallet' => 'QRIS / E-Wallet',
            'cod' => 'COD (Bayar di Tempat)',
            default => $order->payment_method,
        };

        $text = match ($order->status) {
            'dikirim' => "Halo Kak {$order->recipient_name}, pesanan Batik Daffa Anda dengan kode *{$order->order_code}* ({$itemsText}) sudah kami kirim ke alamat tujuan. Terima kasih telah berbelanja di Batik Daffa!",
            'selesai' => "Halo Kak {$order->recipient_name}, terima kasih telah berbelanja di Batik Daffa. Semoga pesanan *{$order->order_code}* berkenan dan nyaman dikenakan.",
            'batal' => "Halo Kak {$order->recipient_name}, kami menginformasikan bahwa pesanan Batik Daffa *{$order->order_code}* telah dibatalkan. Silakan kabari kami bila ingin memesan kembali.",
            default => "Halo Kak {$order->recipient_name}, terima kasih telah berbelanja di Batik Daffa.\n\nPesanan Anda dengan kode *{$order->order_code}* sudah kami terima & konfirmasi:\n• Item: {$itemsText}\n• Total: {$total}\n• Metode: {$method}\n• Alamat: {$order->shipping_address}\n\nKami akan segera mengabarkan detail pengiriman dan resi kepada Anda. Terima kasih!",
        };

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($text);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
