<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
            <span class="text-xs text-gray-500">
                Data pembayaran langsung ditampilkan ke pelanggan saat memilih metode bayar.
            </span>

            <x-filament::button
                type="submit"
                icon="heroicon-o-check"
                wire:loading.attr="disabled"
            >
                Simpan Pengaturan Pembayaran
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
