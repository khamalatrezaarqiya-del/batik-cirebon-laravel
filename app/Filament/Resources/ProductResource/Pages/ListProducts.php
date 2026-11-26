<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Produk')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('Semua')
                ->badge(Product::count()),
        ];

        $categories = Category::withCount('products')->orderBy('id')->get();
        foreach ($categories as $cat) {
            $tabs[$cat->slug] = Tab::make($cat->nama)
                ->badge($cat->products_count)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category_id', $cat->id));
        }

        return $tabs;
    }
}
