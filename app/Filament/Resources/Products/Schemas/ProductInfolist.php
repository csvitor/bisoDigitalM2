<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Informações básicas do produto
                TextEntry::make('name')
                    ->label('Nome do Produto'),
                TextEntry::make('m2_id')
                    ->label('ID Magento')
                    ->numeric(),
                TextEntry::make('biso_id')
                    ->label('ID Biso')
                    ->numeric(),
                TextEntry::make('biso_sku')
                    ->label('SKU Biso'),
                TextEntry::make('m2_sku')
                    ->label('SKU Magento'),
                IconEntry::make('is_synced')
                    ->label('Produto Sincronizado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                // Informações de estoque
                TextEntry::make('stocks_count')
                    ->label('Total de Registros de Estoque')
                    ->getStateUsing(fn ($record) => $record->stocks->count())
                    ->badge()
                    ->color('info'),
                TextEntry::make('total_quantity')
                    ->label('Quantidade Total em Estoque')
                    ->getStateUsing(fn ($record) => $record->stocks->sum('quantity'))
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state === 0 => 'danger',
                        $state < 10 => 'warning',
                        default => 'success',
                    }),
                TextEntry::make('in_stock_count')
                    ->label('Locais com Estoque Disponível')
                    ->getStateUsing(fn ($record) => $record->stocks->where('is_in_stock', true)->count())
                    ->badge()
                    ->color('success'),
                TextEntry::make('synced_stocks')
                    ->label('Estoques Sincronizados')
                    ->getStateUsing(fn ($record) => $record->stocks->where('is_synced', true)->count())
                    ->badge()
                    ->color('primary'),

                // Datas
                TextEntry::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i'),
                TextEntry::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i'),
            ]);
    }
}
