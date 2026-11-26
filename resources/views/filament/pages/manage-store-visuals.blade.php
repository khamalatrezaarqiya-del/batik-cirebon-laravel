<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
            <span class="text-xs text-gray-500">
                Perubahan foto langsung tampil di Beranda tanpa perlu rebuild aplikasi.
            </span>

            <x-filament::button
                type="submit"
                icon="heroicon-o-check"
                wire:loading.attr="disabled"
            >
                Simpan Tampilan Web
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
