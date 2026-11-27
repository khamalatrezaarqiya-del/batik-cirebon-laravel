<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\HeroSlide;
use App\Models\SiteSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\HtmlString;

class ManageStoreVisuals extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Tampilan Web';

    protected static ?string $navigationLabel = 'Banner & Foto Toko';

    protected static ?string $title = 'Banner & Foto Toko';

    protected static ?string $slug = 'tampilan-toko';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.manage-store-visuals';

    public ?array $data = [];

    public function mount(): void
    {
        $state = [
            'featured_label' => SiteSetting::getValue('featured_label', 'Koleksi Pilihan'),
            'featured_title' => SiteSetting::getValue('featured_title', 'Harmoni Sogan & Mega Mendung'),
            'featured_description' => SiteSetting::getValue(
                'featured_description',
                'Perpaduan motif pesisir Cirebon dan nuansa sogan hangat dalam kain lembaran, kemeja, serta selendang untuk beragam momen.'
            ),
            'featured_cta_label' => SiteSetting::getValue('featured_cta_label', 'Lihat Koleksi Mega Mendung'),
            'featured_cta_url' => SiteSetting::getValue('featured_cta_url', '/katalog?motif=mega'),
            'featured_image_upload' => null,
        ];

        foreach (Category::orderBy('id')->get() as $cat) {
            $state["cat_image_{$cat->id}"] = null;
        }

        $this->form->fill($state);
    }

    public function form(Form $form): Form
    {
        $currentFeaturedUrl = HeroSlide::resolveMediaUrl(
            SiteSetting::getValue('featured_image'),
            '/images/campaigns/featured-collection.jpg'
        );

        $categoryFields = [];
        foreach (Category::orderBy('id')->get() as $cat) {
            $fallback = match ($cat->slug) {
                'kain' => '/images/categories/kain.jpg',
                'pakaian' => '/images/categories/pakaian.jpg',
                'aksesoris' => '/images/categories/aksesoris.jpg',
                default => '/images/categories/kain.jpg',
            };
            $currentCatUrl = HeroSlide::resolveMediaUrl($cat->image, $fallback);

            $categoryFields[] = Forms\Components\Group::make()->schema([
                Forms\Components\Placeholder::make("cat_preview_{$cat->id}")
                    ->label("Foto Kategori: {$cat->nama}")
                    ->content(new HtmlString(
                        '<img src="' . e($currentCatUrl) . '" alt="' . e($cat->nama) . '" style="width:100%;height:190px;object-fit:cover;border-radius:6px;border:1px solid #E8E4DC;" />'
                    )),

                Forms\Components\FileUpload::make("cat_image_{$cat->id}")
                    ->label("Ganti Foto {$cat->nama}")
                    ->disk('public')
                    ->visibility('public')
                    ->directory('categories')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(2048)
                    ->imagePreviewHeight('130')
                    ->fetchFileInformation(false)
                    ->helperText('Rasio 4:5 potret disarankan (maks. 2 MB).'),
            ]);
        }

        return $form
            ->schema([
                Forms\Components\Section::make('Banner Koleksi Pilihan (Beranda)')
                    ->description('Mengatur foto editorial & teks pada seksi "Koleksi Pilihan" di halaman Beranda.')
                    ->schema([
                        Forms\Components\Grid::make(['default' => 1, 'lg' => 2])->schema([
                            Forms\Components\Group::make()->schema([
                                Forms\Components\Placeholder::make('featured_preview')
                                    ->label('Foto Banner Saat Ini')
                                    ->content(new HtmlString(
                                        '<img src="' . e($currentFeaturedUrl) . '" alt="Koleksi Pilihan" style="width:100%;max-height:240px;object-fit:cover;border-radius:6px;border:1px solid #E8E4DC;" />'
                                    )),

                                Forms\Components\FileUpload::make('featured_image_upload')
                                    ->label('Ganti Foto Banner Koleksi Pilihan')
                                    ->disk('public')
                                    ->visibility('public')
                                    ->directory('banners')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(2048)
                                    ->imagePreviewHeight('150')
                                    ->fetchFileInformation(false)
                                    ->helperText('Rasio 4:3 lanskap disarankan (JPG/PNG/WebP maks. 2 MB).'),
                            ]),

                            Forms\Components\Group::make()->schema([
                                Forms\Components\TextInput::make('featured_label')
                                    ->label('Label Kecil')
                                    ->required()
                                    ->maxLength(60),

                                Forms\Components\TextInput::make('featured_title')
                                    ->label('Judul Seksi')
                                    ->required()
                                    ->maxLength(120),

                                Forms\Components\Textarea::make('featured_description')
                                    ->label('Deskripsi Singkat')
                                    ->rows(3)
                                    ->required(),

                                Forms\Components\TextInput::make('featured_cta_label')
                                    ->label('Teks Tombol')
                                    ->required()
                                    ->maxLength(80),

                                Forms\Components\TextInput::make('featured_cta_url')
                                    ->label('Link Tujuan Tombol')
                                    ->required()
                                    ->maxLength(255)
                                    ->rules(['regex:#^(/[^/\\\\].*|/$|https://[^\s<>"\']+$)#i'])
                                    ->helperText('Contoh: /katalog?motif=mega atau /katalog'),
                            ]),
                        ]),
                    ]),

                Forms\Components\Section::make('Foto Kategori di Beranda')
                    ->description('Ganti gambar mockup tiap kategori yang tampil pada seksi "Belanja Berdasarkan Kategori".')
                    ->schema([
                        Forms\Components\Grid::make([
                            'default' => 1,
                            'md' => 3,
                        ])->schema($categoryFields),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        SiteSetting::setValue('featured_label', trim($state['featured_label']));
        SiteSetting::setValue('featured_title', trim($state['featured_title']));
        SiteSetting::setValue('featured_description', trim($state['featured_description']));
        SiteSetting::setValue('featured_cta_label', trim($state['featured_cta_label']));
        SiteSetting::setValue('featured_cta_url', trim($state['featured_cta_url']));

        if (! empty($state['featured_image_upload'])) {
            SiteSetting::setValue('featured_image', $state['featured_image_upload']);
        }

        foreach (Category::orderBy('id')->get() as $cat) {
            $key = "cat_image_{$cat->id}";
            if (! empty($state[$key])) {
                $cat->update(['image' => $state[$key]]);
            }
        }

        $this->mount();

        Notification::make()
            ->title('Tampilan & foto toko diperbarui')
            ->body('Banner Koleksi Pilihan dan foto kategori langsung tersinkron ke halaman Beranda.')
            ->success()
            ->actions([
                NotificationAction::make('openHome')
                    ->label('Lihat Beranda')
                    ->url('/', shouldOpenInNewTab: true),
            ])
            ->send();
    }
}
