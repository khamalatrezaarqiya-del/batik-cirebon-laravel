<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Str;

class QuickAddProductWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.quick-add-product-widget';

    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 'full';

    public ?array $data = [];

    public function mount(): void
    {
        $this->resetFormState();
    }

    protected function resetFormState(): void
    {
        $defaultCategoryId = Category::orderBy('id')->value('id');

        $this->form->fill([
            'nama' => '',
            'category_id' => $defaultCategoryId,
            'motif' => 'mega',
            'bahan' => 'katun',
            'teknik' => 'kombinasi',
            'gender' => 'unisex',
            'harga' => null,
            'stok' => 10,
            'deskripsi' => '',
            'foto' => null,
            'is_active' => true,
            'is_best_seller' => false,
            'is_new' => true,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make([
                    'default' => 1,
                    'md' => 3,
                ])->schema([
                    Forms\Components\TextInput::make('nama')
                        ->label('Nama Produk')
                        ->placeholder('Contoh: Kemeja Mega Mendung Sogan')
                        ->required()
                        ->maxLength(160)
                        ->columnSpan(['md' => 2]),

                    Forms\Components\Select::make('category_id')
                        ->label('Kategori Katalog Web')
                        ->options(fn () => Category::orderBy('id')->pluck('nama', 'id'))
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->required()
                        ->helperText('Menentukan katalog & menu tujuan di web.')
                        ->createOptionForm([
                            Forms\Components\TextInput::make('nama')
                                ->label('Nama Kategori Baru')
                                ->placeholder('Contoh: Batik Couple')
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

                    Forms\Components\TextInput::make('harga')
                        ->label('Harga Jual (Rp)')
                        ->placeholder('250.000')
                        ->inputMode('numeric')
                        ->prefix('Rp')
                        ->required()
                        ->rules(['regex:/^[\d.,\s]+$/'])
                        ->dehydrateStateUsing(fn ($state) => (int) preg_replace('/\D/', '', (string) $state))
                        ->helperText('Boleh ketik 250000 atau 250.000 — langsung sinkron ke web.'),

                    Forms\Components\Select::make('motif')
                        ->label('Motif Batik')
                        ->options(Product::MOTIF_OPTIONS)
                        ->native(false)
                        ->required(),

                    Forms\Components\Select::make('bahan')
                        ->label('Jenis Bahan')
                        ->options(Product::BAHAN_OPTIONS)
                        ->default('katun')
                        ->native(false)
                        ->required(),

                    Forms\Components\Select::make('teknik')
                        ->label('Teknik Produksi')
                        ->options(Product::TEKNIK_OPTIONS)
                        ->default('kombinasi')
                        ->native(false)
                        ->required(),

                    Forms\Components\Select::make('gender')
                        ->label('Model / Ukuran')
                        ->options(Product::GENDER_OPTIONS)
                        ->default('unisex')
                        ->native(false),

                    Forms\Components\TextInput::make('stok')
                        ->label('Stok Tersedia')
                        ->numeric()
                        ->minValue(0)
                        ->default(10)
                        ->required(),

                    Forms\Components\FileUpload::make('foto')
                        ->label('Foto Produk (Opsional)')
                        ->disk('public')
                        ->visibility('public')
                        ->directory('products')
                        ->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(2048)
                        ->imagePreviewHeight('150')
                        ->fetchFileInformation(false)
                        ->helperText('JPG/PNG/WebP maks. 2 MB. Bila kosong, motif batik otomatis dipakai.')
                        ->columnSpan(['md' => 1]),

                    Forms\Components\Textarea::make('deskripsi')
                        ->label('Deskripsi Singkat (Opsional)')
                        ->placeholder('Bahan katun primis, ukuran kain / jahitan, keterangan perawatan…')
                        ->rows(3)
                        ->columnSpan(['md' => 2]),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Langsung tayangkan di katalog web')
                        ->default(true)
                        ->inline(false),

                    Forms\Components\Toggle::make('is_best_seller')
                        ->label('Tandai Best Seller')
                        ->default(false)
                        ->inline(false),

                    Forms\Components\Toggle::make('is_new')
                        ->label('Tandai Produk Baru')
                        ->default(true)
                        ->inline(false),
                ]),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $state = $this->form->getState();

        $palette = match ($state['motif'] ?? 'mega') {
            'singaan' => ['#5a3418', '#e8c48a'],
            'kawung' => ['#6d4520', '#e5c191'],
            'truntum' => ['#5c2b1c', '#dfa869'],
            'wadasan' => ['#3d4a3a', '#bcd0a8'],
            default => ['#6b3d1f', '#e6b871'],
        };

        $product = Product::create([
            'category_id' => $state['category_id'],
            'nama' => trim($state['nama']),
            'slug' => Product::uniqueSlug($state['nama']),
            'harga' => (int) preg_replace('/\D/', '', (string) ($state['harga'] ?? 0)),
            'motif' => $state['motif'],
            'bahan' => $state['bahan'] ?? 'katun',
            'teknik' => $state['teknik'] ?? 'kombinasi',
            'gender' => $state['gender'] ?? 'unisex',
            'stok' => (int) $state['stok'],
            'deskripsi' => filled($state['deskripsi'] ?? null) ? trim($state['deskripsi']) : null,
            'foto' => $state['foto'] ?: null,
            'c1' => $palette[0],
            'c2' => $palette[1],
            'is_active' => (bool) ($state['is_active'] ?? true),
            'is_best_seller' => (bool) ($state['is_best_seller'] ?? false),
            'is_new' => (bool) ($state['is_new'] ?? false),
        ]);

        $categoryName = $product->category?->nama ?? 'Katalog';

        Notification::make()
            ->title('Produk berhasil ditambahkan & tersinkron')
            ->body("{$product->nama} masuk ke kategori {$categoryName} dengan harga Rp " . number_format($product->harga, 0, ',', '.') . '.')
            ->success()
            ->actions([
                NotificationAction::make('viewOnWeb')
                    ->label('Lihat di Web')
                    ->url(route('product.detail', $product->slug), shouldOpenInNewTab: true),
            ])
            ->send();

        $this->resetFormState();
        $this->dispatch('product-created');
    }
}
