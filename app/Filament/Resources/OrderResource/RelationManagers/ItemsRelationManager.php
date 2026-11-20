<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Item pesanan';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('product.nama')
                    ->label('Produk')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('size')
                    ->label('Ukuran')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('qty')
                    ->label('Qty')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Harga satuan')
                    ->money('IDR', locale: 'id'),

                Tables\Columns\TextColumn::make('line_total')
                    ->label('Subtotal')
                    ->money('IDR', locale: 'id')
                    ->weight('bold'),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
