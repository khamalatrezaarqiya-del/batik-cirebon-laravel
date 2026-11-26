<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeroSlideResource\Pages;
use App\Models\HeroSlide;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class HeroSlideResource extends Resource
{
    protected static ?string $model = HeroSlide::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Tampilan Web';

    protected static ?string $modelLabel = 'Slider Hero';

    protected static ?string $pluralModelLabel = 'Slider Hero';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Placeholder::make('current_preview')
                ->label('Foto Slide Saat Ini')
                ->visible(fn (?HeroSlide $record) => $record !== null)
                ->content(fn (?HeroSlide $record) => new HtmlString(
                    '<img src="' . e($record->resolvedImageUrl()) . '" alt="Preview" style="width:100%;max-height:200px;object-fit:cover;border-radius:6px;border:1px solid #E8E4DC;" />'
                ))
                ->columnSpanFull(),

            Forms\Components\FileUpload::make('uploaded_image')
                ->label(fn (?HeroSlide $record) => $record ? 'Ganti Foto Slide (Opsional)' : 'Upload Foto Slide')
                ->disk('public')
                ->visibility('public')
                ->directory('banners')
                ->image()
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                ->maxSize(2048)
                ->imagePreviewHeight('160')
                ->fetchFileInformation(false)
                ->required(fn (?HeroSlide $record) => $record === null)
                ->helperText('Disarankan rasio lebar 16:9 (JPG/PNG/WebP maks. 2 MB). Kosongkan saat edit jika tidak mengganti foto.')
                ->columnSpanFull(),

            Forms\Components\TextInput::make('title')
                ->label('Judul Utama (Headline)')
                ->placeholder('Contoh: Warisan Mega Mendung')
                ->required()
                ->maxLength(160)
                ->columnSpanFull(),

            Forms\Components\Textarea::make('description')
                ->label('Deskripsi Singkat')
                ->placeholder('1 kalimat ringkas yang menjelaskan koleksi kampanye…')
                ->rows(2)
                ->columnSpanFull(),

            Forms\Components\TextInput::make('cta_label')
                ->label('Teks Tombol')
                ->default('Jelajahi Koleksi')
                ->required()
                ->maxLength(80),

            Forms\Components\TextInput::make('cta_url')
                ->label('Link Tujuan Tombol')
                ->default('/katalog')
                ->placeholder('/katalog?cat=kain')
                ->required()
                ->maxLength(255)
                ->helperText('Contoh: /katalog, /katalog?cat=kain, /katalog?cat=pakaian'),

            Forms\Components\Select::make('focal_point')
                ->label('Posisi Fokus Foto')
                ->options([
                    '72% 45%' => 'Kanan Tengah (Subjek di kanan)',
                    '72% 20%' => 'Kanan Atas (Potret/Model di kanan)',
                    '50% 50%' => 'Tengah (Simetris)',
                    '50% 25%' => 'Tengah Atas',
                ])
                ->default('72% 45%')
                ->native(false),

            Forms\Components\TextInput::make('sort_order')
                ->label('Urutan Slide')
                ->numeric()
                ->default(fn () => (HeroSlide::max('sort_order') ?? 0) + 1)
                ->minValue(1)
                ->required(),

            Forms\Components\Toggle::make('is_active')
                ->label('Tampilkan di Slider Beranda')
                ->default(true)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order', 'asc')
            ->columns([
                Tables\Columns\ImageColumn::make('preview')
                    ->label('Foto')
                    ->getStateUsing(fn (HeroSlide $record) => $record->resolvedImageUrl())
                    ->height(52)
                    ->width(92)
                    ->extraImgAttributes(['class' => 'rounded object-cover']),

                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Slide')
                    ->weight('semibold')
                    ->description(fn (HeroSlide $record) => $record->description)
                    ->wrap(),

                Tables\Columns\TextColumn::make('cta_label')
                    ->label('Tombol CTA')
                    ->description(fn (HeroSlide $record) => $record->cta_url)
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Aktif di Web')
                    ->afterStateUpdated(function (HeroSlide $record, bool $state): void {
                        Notification::make()
                            ->title($state ? 'Slide diaktifkan di beranda' : 'Slide disembunyikan dari beranda')
                            ->body($record->title)
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Ubah')
                    ->mutateRecordDataUsing(function (array $data): array {
                        $data['uploaded_image'] = null;

                        return $data;
                    })
                    ->mutateFormDataUsing(function (array $data, HeroSlide $record): array {
                        if (! empty($data['uploaded_image'])) {
                            $data['image'] = $data['uploaded_image'];
                        } else {
                            $data['image'] = $record->image;
                        }
                        unset($data['uploaded_image']);

                        return $data;
                    }),
                Tables\Actions\DeleteAction::make()->label('Hapus'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageHeroSlides::route('/'),
        ];
    }
}
