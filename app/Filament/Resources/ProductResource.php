<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-swatch';

    protected static ?string $navigationGroup = 'Katalog';

    protected static ?string $modelLabel = 'Produk';

    protected static ?string $pluralModelLabel = 'Produk';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Produk')->schema([
                Forms\Components\TextInput::make('nama')
                    ->label('Nama produk')
                    ->required()
                    ->maxLength(160)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                        if ($operation === 'create') {
                            $set('slug', Str::slug($state ?? ''));
                        }
                    }),

                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(160)
                    ->unique(ignoreRecord: true)
                    ->helperText('Dipakai di URL: /produk/{slug}'),

                Forms\Components\Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'nama')
                    ->required(),

                Forms\Components\TextInput::make('harga')
                    ->label('Harga (Rp)')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->prefix('Rp'),

                Forms\Components\Textarea::make('deskripsi')
                    ->rows(4)
                    ->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Motif & tampilan')->schema([
                Forms\Components\Select::make('motif')
                    ->options([
                        'mega' => 'Mega Mendung',
                        'kawung' => 'Kawung',
                        'parang' => 'Parang',
                        'truntum' => 'Truntum',
                        'wadasan' => 'Wadasan',
                        'lainnya' => 'Lainnya',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('stok')
                    ->numeric()
                    ->default(10)
                    ->required(),

                Forms\Components\ColorPicker::make('c1')
                    ->label('Warna dasar motif')
                    ->required(),

                Forms\Components\ColorPicker::make('c2')
                    ->label('Warna aksen motif')
                    ->required(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Tampilkan di toko')
                    ->default(true),
            ])->columns(2),

            Forms\Components\Section::make('Foto asli')->schema([
                Forms\Components\FileUpload::make('foto')
                    ->label('Foto produk')
                    ->image()
                    ->directory('products')
                    ->imageEditor()
                    ->helperText('Kosongkan untuk memakai motif SVG otomatis.')
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('foto')
                    ->label('Foto')
                    ->height(40)
                    ->width(40)
                    ->defaultImageUrl(null),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('category.nama')
                    ->label('Kategori')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('motif')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'mega' => 'Mega Mendung',
                        'kawung' => 'Kawung',
                        'parang' => 'Parang',
                        'truntum' => 'Truntum',
                        'wadasan' => 'Wadasan',
                        default => 'Lainnya',
                    }),

                Tables\Columns\TextColumn::make('harga')
                    ->label('Harga')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stok')
                    ->numeric()
                    ->sortable()
                    ->color(fn (int $state) => $state <= 3 ? 'danger' : null),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->relationship('category', 'nama'),

                Tables\Filters\SelectFilter::make('motif')
                    ->options([
                        'mega' => 'Mega Mendung',
                        'kawung' => 'Kawung',
                        'parang' => 'Parang',
                        'truntum' => 'Truntum',
                        'wadasan' => 'Wadasan',
                        'lainnya' => 'Lainnya',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status tampil'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
