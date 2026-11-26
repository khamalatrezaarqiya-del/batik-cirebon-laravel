<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Livewire\Attributes\On;

class LatestProductsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Kelola Cepat Harga & Produk Terbaru';

    #[On('product-created')]
    public function refreshTable(): void
    {
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()->with('category')->latest('id')->limit(8)
            )
            ->paginated(false)
            ->columns([
                Tables\Columns\ImageColumn::make('foto')
                    ->label('')
                    ->disk('public')
                    ->height(40)
                    ->width(32)
                    ->extraImgAttributes(['class' => 'rounded object-cover']),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Produk')
                    ->weight('medium')
                    ->searchable()
                    ->limit(38),

                Tables\Columns\TextColumn::make('category.nama')
                    ->label('Kategori Katalog')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('harga')
                    ->label('Harga Web')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((int) $state, 0, ',', '.'))
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('stok')
                    ->label('Stok')
                    ->numeric()
                    ->color(fn (int $state) => $state <= 3 ? 'danger' : 'gray'),

                Tables\Columns\ToggleColumn::make('is_best_seller')
                    ->label('Best Seller')
                    ->afterStateUpdated(function (Product $record, bool $state): void {
                        Notification::make()
                            ->title($state ? 'Ditandai Best Seller' : 'Label Best Seller dilepas')
                            ->body($record->nama)
                            ->success()
                            ->send();

                        $this->dispatch('product-updated');
                    }),

                Tables\Columns\ToggleColumn::make('is_new')
                    ->label('Baru')
                    ->afterStateUpdated(function (Product $record, bool $state): void {
                        Notification::make()
                            ->title($state ? 'Ditandai Baru' : 'Label Baru dilepas')
                            ->body($record->nama)
                            ->success()
                            ->send();

                        $this->dispatch('product-updated');
                    }),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Tayang di Web')
                    ->afterStateUpdated(function (Product $record, bool $state): void {
                        Notification::make()
                            ->title($state ? 'Produk ditayangkan di web' : 'Produk disembunyikan dari web')
                            ->body($record->nama)
                            ->success()
                            ->send();

                        $this->dispatch('product-updated');
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('quickPrice')
                    ->label('Ubah Harga')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('gray')
                    ->modalHeading(fn (Product $record) => "Ubah Harga & Stok · {$record->nama}")
                    ->modalWidth('md')
                    ->fillForm(fn (Product $record) => [
                        'harga' => number_format((int) $record->harga, 0, ',', '.'),
                        'stok' => $record->stok,
                        'category_id' => $record->category_id,
                    ])
                    ->form([
                        Forms\Components\TextInput::make('harga')
                            ->label('Harga Baru (Rp)')
                            ->inputMode('numeric')
                            ->prefix('Rp')
                            ->required()
                            ->rules(['regex:/^[\d.,\s]+$/'])
                            ->helperText('Boleh ketik 250000 atau 250.000 — otomatis sinkron ke web & keranjang.'),
                        Forms\Components\TextInput::make('stok')
                            ->label('Stok Tersedia')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        Forms\Components\Select::make('category_id')
                            ->label('Kategori Katalog Web')
                            ->options(fn () => \App\Models\Category::orderBy('id')->pluck('nama', 'id'))
                            ->native(false)
                            ->required(),
                    ])
                    ->action(function (Product $record, array $data): void {
                        $record->update([
                            'harga' => (int) preg_replace('/\D/', '', (string) $data['harga']),
                            'stok' => (int) $data['stok'],
                            'category_id' => $data['category_id'],
                        ]);

                        Notification::make()
                            ->title('Harga & katalog tersinkron')
                            ->body("{$record->nama} kini Rp " . number_format($record->harga, 0, ',', '.') . '.')
                            ->success()
                            ->send();

                        $this->dispatch('product-updated');
                    }),

                Tables\Actions\Action::make('viewWeb')
                    ->label('Web')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Product $record) => route('product.detail', $record->slug), shouldOpenInNewTab: true),
            ])
            ->headerActions([
                Tables\Actions\Action::make('allProducts')
                    ->label('Kelola Semua Produk')
                    ->icon('heroicon-o-squares-2x2')
                    ->color('gray')
                    ->url(ProductResource::getUrl('index')),
            ]);
    }
}
