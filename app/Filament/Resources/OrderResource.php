<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
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
                    ->required(),

                Forms\Components\TextInput::make('phone')
                    ->label('Nomor HP')
                    ->tel()
                    ->required(),

                Forms\Components\Textarea::make('shipping_address')
                    ->label('Alamat pengiriman')
                    ->rows(3)
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('note')
                    ->label('Catatan pembeli')
                    ->rows(2)
                    ->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Pembayaran & status')->schema([
                Forms\Components\Select::make('payment_method')
                    ->label('Metode bayar')
                    ->options([
                        'transfer' => 'Transfer Bank',
                        'ewallet' => 'E-Wallet',
                        'cod' => 'COD',
                    ])
                    ->required(),

                Forms\Components\Select::make('status')
                    ->options([
                        'baru' => 'Baru',
                        'diproses' => 'Diproses',
                        'dikirim' => 'Dikirim',
                        'selesai' => 'Selesai',
                        'batal' => 'Batal',
                    ])
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
                    ->prefix('Rp')
                    ->default(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                        $set('grand_total', (int) $get('subtotal') + (int) ($state ?: 0));
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
                    ->label('Bayar')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'transfer' => 'Transfer',
                        'ewallet' => 'E-Wallet',
                        'cod' => 'COD',
                        default => $state,
                    }),

                Tables\Columns\SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        'baru' => 'Baru',
                        'diproses' => 'Diproses',
                        'dikirim' => 'Dikirim',
                        'selesai' => 'Selesai',
                        'batal' => 'Batal',
                    ])
                    ->selectablePlaceholder(false),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Masuk')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'baru' => 'Baru',
                        'diproses' => 'Diproses',
                        'dikirim' => 'Dikirim',
                        'selesai' => 'Selesai',
                        'batal' => 'Batal',
                    ]),

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
            ->actions([
                Tables\Actions\Action::make('wa')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->url(fn (Order $r) => 'https://wa.me/' . preg_replace('/^0/', '62', $r->phone), shouldOpenInNewTab: true),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
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
