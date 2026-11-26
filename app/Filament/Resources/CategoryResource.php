<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use App\Models\HeroSlide;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Katalog Web';

    protected static ?string $modelLabel = 'Kategori Katalog';

    protected static ?string $pluralModelLabel = 'Kategori Katalog';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nama')
                ->label('Nama Kategori')
                ->placeholder('Contoh: Kain, Pakaian, Aksesoris')
                ->required()
                ->maxLength(80)
                ->live(onBlur: true)
                ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                    if ($operation === 'create') {
                        $set('slug', Str::slug($state ?? ''));
                    }
                }),

            Forms\Components\TextInput::make('slug')
                ->label('Slug URL Katalog')
                ->required()
                ->maxLength(80)
                ->unique(ignoreRecord: true)
                ->prefix('/katalog?cat=')
                ->helperText('Otomatis muncul di menu navigasi & filter katalog web.'),

            Forms\Components\Placeholder::make('current_image')
                ->label('Foto Kategori Saat Ini')
                ->visible(fn (?Category $record) => $record !== null)
                ->content(function (?Category $record) {
                    $fallback = match ($record->slug) {
                        'kain' => '/images/categories/kain.jpg',
                        'pakaian' => '/images/categories/pakaian.jpg',
                        'aksesoris' => '/images/categories/aksesoris.jpg',
                        default => '/images/categories/kain.jpg',
                    };
                    $url = HeroSlide::resolveMediaUrl($record->image, $fallback);

                    return new HtmlString(
                        '<img src="' . e($url) . '" alt="' . e($record->nama) . '" style="width:120px;height:150px;object-fit:cover;border-radius:6px;border:1px solid #E8E4DC;" />'
                    );
                }),

            Forms\Components\FileUpload::make('uploaded_image')
                ->label('Upload / Ganti Foto Kategori (Opsional)')
                ->disk('public')
                ->visibility('public')
                ->directory('categories')
                ->image()
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                ->maxSize(2048)
                ->imagePreviewHeight('140')
                ->fetchFileInformation(false)
                ->helperText('Tampil di seksi "Belanja Berdasarkan Kategori" di Beranda (maks. 2 MB).'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('preview')
                    ->label('Foto')
                    ->getStateUsing(function (Category $record) {
                        $fallback = match ($record->slug) {
                            'kain' => '/images/categories/kain.jpg',
                            'pakaian' => '/images/categories/pakaian.jpg',
                            'aksesoris' => '/images/categories/aksesoris.jpg',
                            default => '/images/categories/kain.jpg',
                        };

                        return HeroSlide::resolveMediaUrl($record->image, $fallback);
                    })
                    ->height(48)
                    ->width(38)
                    ->extraImgAttributes(['class' => 'rounded object-cover']),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Kategori')
                    ->weight('semibold')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('URL Katalog Web')
                    ->formatStateUsing(fn (string $state) => "/katalog?cat={$state}")
                    ->color('gray'),

                Tables\Columns\TextColumn::make('products_count')
                    ->label('Jumlah Produk')
                    ->counts('products')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('viewWeb')
                    ->label('Lihat di Web')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Category $record) => "/katalog?cat={$record->slug}", shouldOpenInNewTab: true),
                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->mutateRecordDataUsing(function (array $data): array {
                        $data['uploaded_image'] = null;

                        return $data;
                    })
                    ->mutateFormDataUsing(function (array $data, Category $record): array {
                        if (! empty($data['uploaded_image'])) {
                            $data['image'] = $data['uploaded_image'];
                        } else {
                            $data['image'] = $record->image;
                        }
                        unset($data['uploaded_image']);

                        return $data;
                    }),
                Tables\Actions\DeleteAction::make()->label('Hapus'),
            ])
            ->defaultSort('id', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCategories::route('/'),
        ];
    }
}
