<?php

namespace App\Filament\Resources\HeroSlideResource\Pages;

use App\Filament\Resources\HeroSlideResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageHeroSlides extends ManageRecords
{
    protected static string $resource = HeroSlideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Slide Hero')
                ->icon('heroicon-o-plus')
                ->mutateFormDataUsing(function (array $data): array {
                    $data['image'] = ! empty($data['uploaded_image'])
                        ? $data['uploaded_image']
                        : '/images/campaigns/hero-1.jpg';
                    unset($data['uploaded_image']);

                    return $data;
                }),
        ];
    }
}
