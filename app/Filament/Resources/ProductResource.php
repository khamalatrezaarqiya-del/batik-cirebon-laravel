<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-swatch';

    protected static ?string $navigationGroup = 'Katalog Web';

    protected static ?string $modelLabel = 'Produk';

    protected static ?string $pluralModelLabel = 'Produk';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return (string) Product::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): string | array | null
    {
        return 'gray';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(['default' => 1, 'lg' => 3])->schema([
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Informasi Produk')
                        ->description('Tampil langsung di katalog dan halaman detail produk web.')
                        ->schema([
                            Forms\Components\TextInput::make('nama')
                                ->label('Nama Produk')
                                ->placeholder('Contoh: Batik Mega Mendung Sogan')
                                ->required()
                                ->maxLength(160)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                    if ($operation === 'create') {
                                        $set('slug', Str::slug($state ?? ''));
                                    }
                                })
                                ->columnSpanFull(),

                            Forms\Components\Select::make('category_id')
                                ->label('Kategori Katalog Web')
                                ->relationship('category', 'nama')
                                ->searchable()
                                ->preload()
                                ->native(false)
                                ->required()
                                ->helperText('Pilih jenis katalog di web (Kain, Pakaian, Aksesoris, dll).')
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('nama')
                                        ->label('Nama Kategori Baru')
                                        ->required()
                                        ->maxLength(80),
                                ])
                                ->createOptionUsing(function (array $data): int {
                                    $base = Str::slug($data['nama']) ?: 'kategori';
                                    $slug = $base;
                                    $i = 2;
                                    while (Category::where('slug', $slug)->exists()) {
                                        $slug = "{$base}-{$i}";
                                        $i++;
                                    }

                                    return Category::create([
                                        'nama' => trim($data['nama']),
                                        'slug' => $slug,
                                    ])->id;
                                }),

                            Forms\Components\Select::make('motif')
                                ->label('Motif Batik')
                                ->options([
                                    'mega' => 'Mega Mendung',
                                    'kawung' => 'Kawung',
                                    'parang' => 'Parang',
                                    'truntum' => 'Truntum',
                                    'wadasan' => 'Wadasan',
                                    'lainnya' => 'Lainnya',
                                ])
                                ->default('mega')
                                ->native(false)
                                ->required(),

                            Forms\Components\Textarea::make('deskripsi')
                                ->label('Deskripsi Produk')
                                ->placeholder('Ceritakan bahan, ukuran kain/jahitan, dan karakter motifnya…')
                                ->rows(4)
                                ->columnSpanFull(),
                        ])->columns(2),

                    Forms\Components\Section::make('Foto Produk')
                        ->description('Rasio 4:5 disarankan agar tampil rapi di katalog web.')
                        ->schema([
                            Forms\Components\FileUpload::make('foto')
                                ->label('Unggah Foto')
                                ->disk('public')
                                ->visibility('public')
                                ->directory('products')
                                ->image()
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                ->maxSize(2048)
                                ->imagePreviewHeight('180')
                                ->fetchFileInformation(false)
                                ->helperText('JPG/PNG/WebP maks. 2 MB (rasio 4:5 disarankan). Kosongkan untuk motif SVG otomatis.')
                                ->columnSpanFull(),
                        ]),
                ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Harga, Stok & Publikasi')
                        ->schema([
                            Forms\Components\TextInput::make('harga')
                                ->label('Harga Jual (Rp)')
                                ->required()
                                ->inputMode('numeric')
                                ->prefix('Rp')
                                ->rules(['regex:/^[\d.,\s]+$/'])
                                ->formatStateUsing(fn ($state) => filled($state) ? number_format((int) $state, 0, ',', '.') : '')
                                ->dehydrateStateUsing(fn ($state) => (int) preg_replace('/\D/', '', (string) $state))
                                ->helperText('Boleh ketik 250000 atau 250.000 — otomatis tersinkron ke seluruh halaman web & keranjang.'),

                            Forms\Components\TextInput::make('stok')
                                ->label('Stok Tersedia')
                                ->numeric()
                                ->minValue(0)
                                ->default(10)
                                ->required(),

                            Forms\Components\Toggle::make('is_active')
                                ->label('Tayangkan di Toko Web')
                                ->helperText('Matikan jika ingin menyembunyikan sementara.')
                                ->default(true),

                            Forms\Components\Toggle::make('is_best_seller')
                                ->label('Best Seller')
                                ->helperText('Tampilkan label Best Seller & masukkan ke Pilihan Terlaris beranda.')
                                ->default(false),

                            Forms\Components\Toggle::make('is_new')
                                ->label('Baru')
                                ->helperText('Tampilkan label Baru & masukkan ke Koleksi Terbaru beranda.')
                                ->default(false),

                            Forms\Components\TextInput::make('slug')
                                ->label('Slug URL')
                                ->required()
                                ->maxLength(160)
                                ->unique(ignoreRecord: true)
                                ->prefix('/produk/'),
                        ]),

                    Forms\Components\Section::make('Warna Cadangan Motif SVG')
                        ->description('Dipakai hanya bila produk belum memiliki foto.')
                        ->collapsed()
                        ->schema([
                            Forms\Components\ColorPicker::make('c1')
                                ->label('Warna Dasar Motif')
                                ->default('#6b3d1f')
                                ->required(),

                            Forms\Components\ColorPicker::make('c2')
                                ->label('Warna Aksen Motif')
                                ->default('#e6b871')
                                ->required(),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('foto')
                    ->label('')
                    ->disk('public')
                    ->height(48)
                    ->width(38)
                    ->extraImgAttributes(['class' => 'rounded object-cover']),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Produk')
                    ->weight('medium')
                    ->searchable()
                    ->sortable()
                    ->limit(42)
                    ->description(fn (Product $record) => match ($record->motif) {
                        'mega' => 'Mega Mendung',
                        'kawung' => 'Kawung',
                        'parang' => 'Parang',
                        'truntum' => 'Truntum',
                        'wadasan' => 'Wadasan',
                        default => 'Klasik',
                    }),

                Tables\Columns\TextColumn::make('category.nama')
                    ->label('Kategori Katalog')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('harga')
                    ->label('Harga Web')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((int) $state, 0, ',', '.'))
                    ->weight('semibold')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stok')
                    ->label('Stok')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state) => $state <= 3 ? 'danger' : 'gray'),

                Tables\Columns\ToggleColumn::make('is_best_seller')
                    ->label('Best Seller')
                    ->afterStateUpdated(function (Product $record, bool $state): void {
                        Notification::make()
                            ->title($state ? 'Ditandai Best Seller' : 'Label Best Seller dilepas')
                            ->body($record->nama)
                            ->success()
                            ->send();
                    }),

                Tables\Columns\ToggleColumn::make('is_new')
                    ->label('Baru')
                    ->afterStateUpdated(function (Product $record, bool $state): void {
                        Notification::make()
                            ->title($state ? 'Ditandai Baru' : 'Label Baru dilepas')
                            ->body($record->nama)
                            ->success()
                            ->send();
                    }),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Tayang di Web')
                    ->afterStateUpdated(function (Product $record, bool $state): void {
                        Notification::make()
                            ->title($state ? 'Produk aktif di web' : 'Produk disembunyikan dari web')
                            ->body($record->nama)
                            ->success()
                            ->send();
                    }),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori Katalog')
                    ->relationship('category', 'nama'),

                Tables\Filters\SelectFilter::make('motif')
                    ->label('Motif Batik')
                    ->options([
                        'mega' => 'Mega Mendung',
                        'kawung' => 'Kawung',
                        'parang' => 'Parang',
                        'truntum' => 'Truntum',
                        'wadasan' => 'Wadasan',
                        'lainnya' => 'Lainnya',
                    ]),

                Tables\Filters\TernaryFilter::make('is_best_seller')
                    ->label('Best Seller'),

                Tables\Filters\TernaryFilter::make('is_new')
                    ->label('Produk Baru'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Tayang Web'),
            ])
            ->actions([
                Tables\Actions\Action::make('quickPrice')
                    ->label('Harga')
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
                            ->helperText('Boleh ketik 250000 atau 250.000 — otomatis tersinkron ke katalog, detail produk, dan keranjang web.'),
                        Forms\Components\TextInput::make('stok')
                            ->label('Stok Tersedia')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        Forms\Components\Select::make('category_id')
                            ->label('Kategori Katalog Web')
                            ->options(fn () => Category::orderBy('id')->pluck('nama', 'id'))
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
                            ->title('Harga & stok diperbarui')
                            ->body("{$record->nama} sekarang Rp " . number_format($record->harga, 0, ',', '.') . ' di web.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('viewWeb')
                    ->label('Web')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Product $record) => route('product.detail', $record->slug), shouldOpenInNewTab: true),

                Tables\Actions\EditAction::make()->label('Edit'),
                Tables\Actions\DeleteAction::make()->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('moveCategory')
                        ->label('Pindah Kategori Katalog')
                        ->icon('heroicon-o-folder')
                        ->form([
                            Forms\Components\Select::make('category_id')
                                ->label('Kategori Tujuan')
                                ->options(fn () => Category::orderBy('id')->pluck('nama', 'id'))
                                ->native(false)
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each->update(['category_id' => $data['category_id']]);
                            Notification::make()
                                ->title('Kategori produk diperbarui')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
