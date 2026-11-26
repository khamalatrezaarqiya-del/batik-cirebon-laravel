<x-filament-widgets::widget>
    <x-filament::section
        heading="Tambah Produk Cepat ke Katalog Web"
        description="Produk yang disimpan otomatis tampil di Beranda, Katalog, dan kategori pilihan Anda secara langsung."
        collapsible
    >
        <form wire:submit="create" class="space-y-6">
            {{ $this->form }}

            <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-gray-200">
                <span class="text-xs text-gray-500">
                    Harga, stok, dan kategori langsung tersinkron dengan halaman toko.
                </span>

                <x-filament::button
                    type="submit"
                    icon="heroicon-o-plus"
                    wire:loading.attr="disabled"
                >
                    Simpan &amp; Tayangkan ke Web
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</x-filament-widgets::widget>
