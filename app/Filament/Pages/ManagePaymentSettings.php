<?php

namespace App\Filament\Pages;

use App\Models\HeroSlide;
use App\Models\SiteSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\HtmlString;

class ManagePaymentSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Penjualan';

    protected static ?string $navigationLabel = 'Rekening & QRIS';

    protected static ?string $title = 'Pengaturan Pembayaran (Rekening & QRIS)';

    protected static ?string $slug = 'pengaturan-pembayaran';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.manage-payment-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'pay_bank_name' => SiteSetting::getValue('pay_bank_name', 'Bank BCA'),
            'pay_bank_account' => SiteSetting::getValue('pay_bank_account', '1340 5829 10'),
            'pay_bank_holder' => SiteSetting::getValue('pay_bank_holder', 'Batik Daffa Trusmi'),
            'pay_bank_note' => SiteSetting::getValue(
                'pay_bank_note',
                'Transfer sesuai nominal total pesanan. Admin akan memverifikasi pembayaran dan menghubungi Anda.'
            ),
            'pay_qris_merchant' => SiteSetting::getValue('pay_qris_merchant', 'Batik Daffa · Trusmi Cirebon'),
            'pay_ewallet_info' => SiteSetting::getValue(
                'pay_ewallet_info',
                'Scan QRIS melalui GoPay, OVO, DANA, ShopeePay, atau mobile banking.'
            ),
            'pay_cod_note' => SiteSetting::getValue(
                'pay_cod_note',
                'Bayar tunai langsung ke kurir saat paket tiba. Admin akan menghubungi Anda terlebih dahulu sebelum paket dikirim.'
            ),
            'admin_whatsapp' => SiteSetting::getValue('admin_whatsapp', ''),
            'qris_upload' => null,
        ]);
    }

    public function form(Form $form): Form
    {
        $currentQris = HeroSlide::resolveMediaUrl(
            SiteSetting::getValue('pay_qris_image'),
            '/images/qris-default.svg'
        );

        return $form
            ->schema([
                Forms\Components\Section::make('Transfer Bank')
                    ->description('Nomor rekening ini tampil langsung saat pelanggan memilih Transfer Bank di Checkout & Detail Pesanan.')
                    ->schema([
                        Forms\Components\TextInput::make('pay_bank_name')
                            ->label('Nama Bank')
                            ->placeholder('Contoh: Bank BCA / Bank Mandiri')
                            ->required()
                            ->maxLength(80),

                        Forms\Components\TextInput::make('pay_bank_account')
                            ->label('Nomor Rekening')
                            ->placeholder('Contoh: 1340 5829 10')
                            ->required()
                            ->maxLength(60),

                        Forms\Components\TextInput::make('pay_bank_holder')
                            ->label('Atas Nama')
                            ->placeholder('Contoh: Batik Daffa Trusmi')
                            ->required()
                            ->maxLength(120)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('pay_bank_note')
                            ->label('Instruksi Transfer')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('QRIS / E-Wallet')
                    ->description('Gambar QRIS ini tampil langsung saat pelanggan memilih E-Wallet / QRIS.')
                    ->schema([
                        Forms\Components\Grid::make(['default' => 1, 'lg' => 2])->schema([
                            Forms\Components\Group::make()->schema([
                                Forms\Components\Placeholder::make('qris_preview')
                                    ->label('QRIS Saat Ini')
                                    ->content(new HtmlString(
                                        '<img src="' . e($currentQris) . '" alt="QRIS Batik Daffa" style="width:200px;height:auto;border-radius:8px;border:1px solid #E8E4DC;background:#fff;" />'
                                    )),

                                Forms\Components\FileUpload::make('qris_upload')
                                    ->label('Upload Gambar QRIS Asli')
                                    ->disk('public')
                                    ->visibility('public')
                                    ->directory('payments')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(2048)
                                    ->imagePreviewHeight('160')
                                    ->fetchFileInformation(false)
                                    ->helperText('JPG/PNG/WebP maks. 2 MB.'),
                            ]),

                            Forms\Components\Group::make()->schema([
                                Forms\Components\TextInput::make('pay_qris_merchant')
                                    ->label('Nama Merchant QRIS')
                                    ->required()
                                    ->maxLength(120),

                                Forms\Components\Textarea::make('pay_ewallet_info')
                                    ->label('Keterangan E-Wallet / QRIS')
                                    ->rows(3)
                                    ->required(),
                            ]),
                        ]),
                    ]),

                Forms\Components\Section::make('COD (Bayar di Tempat)')
                    ->schema([
                        Forms\Components\Textarea::make('pay_cod_note')
                            ->label('Keterangan COD')
                            ->rows(2)
                            ->required(),
                    ]),

                Forms\Components\Section::make('WhatsApp Admin (Pesanan Custom & Bantuan)')
                    ->description('Nomor tujuan saat pelanggan menekan tombol Pesan Custom atau Bantuan via WhatsApp.')
                    ->schema([
                        Forms\Components\TextInput::make('admin_whatsapp')
                            ->label('Nomor WhatsApp Admin')
                            ->placeholder('Contoh: 6281234567890')
                            ->tel()
                            ->maxLength(24)
                            ->helperText('Awali dengan 62 (tanpa tanda + atau spasi).'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        SiteSetting::setValue('pay_bank_name', trim($state['pay_bank_name']));
        SiteSetting::setValue('pay_bank_account', trim($state['pay_bank_account']));
        SiteSetting::setValue('pay_bank_holder', trim($state['pay_bank_holder']));
        SiteSetting::setValue('pay_bank_note', trim($state['pay_bank_note'] ?? ''));
        SiteSetting::setValue('pay_qris_merchant', trim($state['pay_qris_merchant']));
        SiteSetting::setValue('pay_ewallet_info', trim($state['pay_ewallet_info']));
        SiteSetting::setValue('pay_cod_note', trim($state['pay_cod_note']));
        SiteSetting::setValue('admin_whatsapp', preg_replace('/\D/', '', (string) ($state['admin_whatsapp'] ?? '')));

        if (! empty($state['qris_upload'])) {
            SiteSetting::setValue('pay_qris_image', $state['qris_upload']);
        }

        $this->mount();

        Notification::make()
            ->title('Pengaturan pembayaran tersimpan')
            ->body('Nomor rekening & QRIS langsung tersinkron ke halaman Checkout dan Detail Pesanan.')
            ->success()
            ->send();
    }
}
