<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('confirmOrder')
                ->label('Konfirmasi Pesanan')
                ->icon('heroicon-o-check-circle')
                ->color('primary')
                ->visible(fn (Order $record) => $record->status === 'baru')
                ->requiresConfirmation()
                ->action(function (Order $record): void {
                    $record->update(['status' => 'diproses']);
                    Notification::make()
                        ->title("Pesanan {$record->order_code} dikonfirmasi")
                        ->success()
                        ->send();
                    $this->refreshFormData(['status']);
                }),

            Actions\Action::make('contactCustomer')
                ->label('Hubungi Pelanggan via WA')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('success')
                ->url(fn (Order $record) => OrderResource::customerWaUrl($record), shouldOpenInNewTab: true),

            Actions\EditAction::make()->label('Edit'),
        ];
    }
}
