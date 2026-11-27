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

    protected static ?string $navigationLabel = 'Pembayaran & WA';

    protected static ?string $title = 'Pengaturan Pembayaran (Rekening, DP 50%, QRIS & COD)';

    protected static ?string $slug = 'pengaturan-pembayaran';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.manage-payment-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'pay_transfer_enabled' => SiteSetting::getValue('pay_transfer_enabled', '1') === '1',
            'pay_dp50_enabled' => SiteSetting::getValue('pay_dp50_enabled', '1') === '1',
            'pay_qris_enabled' => SiteSetting::getValue('pay_qris_enabled', '0') === '1',
            'pay_cod_enabled' => SiteSetting::getValue('pay_cod_enabled', '0') === '1',
            'pay_bank_name' => SiteSetting::getValue('pay_bank_name', 'Bank BCA'),
            'pay_bank_account' => SiteSetting::getValue('pay_bank_account', '1340 5829 10'),
            'pay_bank_holder' => SiteSetting::getValue('pay_bank_holder', 'Batik Daffa Cirebon'),
            'pay_bank_note' => SiteSetting::getValue(
                'pay_bank_note',
                'Transfer m-Banking sesuai nominal total pesanan. Admin Batik Daffa akan memverifikasi mutasi dan menghubungi Anda untuk pengiriman.'
            ),
            'pay_dp50_note' => SiteSetting::getValue(
                'pay_dp50_note',
                'Khusus pesanan bernominal besar, seragam, atau custom. Transfer DP 50% terlebih dahulu dan pelunasan dilakukan sebelum pengiriman.'
            ),
            'pay_qris_merchant' => SiteSetting::getValue('pay_qris_merchant', 'Batik Daffa · Plered, Cirebon'),
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
                Forms\Components\Section::make('Aktif / Nonaktifkan Metode Pembayaran di Checkout')
                    ->description('Atur metode pembayaran apa saja yang ditampilkan ke pelanggan. Anda dapat mengaktifkan QRIS sewaktu pameran (misal Inacraft) dan menonaktifkannya untuk operasional harian.')
                    ->schema([
                        Forms\Components\Toggle::make('pay_transfer_enabled')
                            ->label('Transfer Bank (Lunas)')
                            ->helperText('Metode harian utama melalui m-Banking / transfer BCA.')
                            ->default(true),

                        Forms\Components\Toggle::make('pay_dp50_enabled')
                            ->label('DP 50% (Transfer Bank)')
                            ->helperText('Untuk pesanan nominal besar, seragam, atau custom.')
                            ->default(true),

                        Forms\Components\Toggle::make('pay_qris_enabled')
                            ->label('QRIS / E-Wallet')
                            ->helperText('Aktifkan saat fasilitas QRIS tersedia / pameran.')
                            ->default(false),

                        Forms\Components\Toggle::make('pay_cod_enabled')
                            ->label('COD (Bayar di Tempat)')
                            ->helperText('Aktifkan bila melayani pembayaran tunai di tempat.')
                            ->default(false),
                    ])->columns(2),

                Forms\Components\Section::make('Transfer Bank & DP 50%')
                    ->description('Nomor rekening ini tampil langsung saat pelanggan memilih Transfer Bank atau DP 50% di Checkout & Detail Pesanan.')
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
                            ->placeholder('Contoh: Batik Daffa Cirebon')
                            ->required()
                            ->maxLength(120)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('pay_bank_note')
                            ->label('Instruksi Transfer Lunas')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('pay_dp50_note')
                            ->label('Instruksi DP 50%')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('QRIS / E-Wallet')
                    ->description('Gambar QRIS ini tampil otomatis saat opsi QRIS diaktifkan di atas.')
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

                Forms\Components\Section::make('WhatsApp Admin (Custom, Grosir B2B & Bantuan)')
                    ->description('Nomor tujuan saat pelanggan menekan tombol Pesan Custom, Konsultasi Grosir B2B, atau Bantuan via WhatsApp.')
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

        $transferOn = (bool) ($state['pay_transfer_enabled'] ?? true);
        $dp50On = (bool) ($state['pay_dp50_enabled'] ?? false);
        $qrisOn = (bool) ($state['pay_qris_enabled'] ?? false);
        $codOn = (bool) ($state['pay_cod_enabled'] ?? false);

        // Pastikan minimal 1 metode pembayaran tetap aktif
        if (! $transferOn && ! $dp50On && ! $qrisOn && ! $codOn) {
            $transferOn = true;
        }

        SiteSetting::setValue('pay_transfer_enabled', $transferOn ? '1' : '0');
        SiteSetting::setValue('pay_dp50_enabled', $dp50On ? '1' : '0');
        SiteSetting::setValue('pay_qris_enabled', $qrisOn ? '1' : '0');
        SiteSetting::setValue('pay_cod_enabled', $codOn ? '1' : '0');

        SiteSetting::setValue('pay_bank_name', trim($state['pay_bank_name']));
        SiteSetting::setValue('pay_bank_account', trim($state['pay_bank_account']));
        SiteSetting::setValue('pay_bank_holder', trim($state['pay_bank_holder']));
        SiteSetting::setValue('pay_bank_note', trim($state['pay_bank_note'] ?? ''));
        SiteSetting::setValue('pay_dp50_note', trim($state['pay_dp50_note'] ?? ''));
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
            ->body('Status ON/OFF metode pembayaran, rekening & QRIS langsung tersinkron ke Checkout web.')
            ->success()
            ->send();
    }
}
