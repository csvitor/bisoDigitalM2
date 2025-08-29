<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('m2_id')
                    ->label('ID Magento'),
                TextEntry::make('biso_id')
                    ->label('ID Biso'),
                TextEntry::make('order_number')
                    ->label('Número do Pedido'),
                TextEntry::make('m2_status')
                    ->label('Status Magento')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'complete' => 'success',
                        'processing' => 'warning',
                        'pending' => 'gray',
                        'canceled' => 'danger',
                        default => 'gray',
                    }),
                TextEntry::make('m2_state')
                    ->label('State Magento'),
                TextEntry::make('total_amount')
                    ->label('Valor Total')
                    ->money('BRL'),
                TextEntry::make('currency')
                    ->label('Moeda'),
                TextEntry::make('order_date')
                    ->label('Data do Pedido')
                    ->dateTime('d/m/Y H:i'),
                IconEntry::make('is_synced_to_biso')
                    ->label('Sincronizado com Biso')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextEntry::make('sync_attempts')
                    ->label('Tentativas de Sincronização')
                    ->formatStateUsing(fn (int $state): string => $state . '/3')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'gray',
                        $state < 3 => 'warning',
                        default => 'danger',
                    }),
                TextEntry::make('last_sync_attempt')
                    ->label('Última Tentativa')
                    ->dateTime('d/m/Y H:i'),
                IconEntry::make('is_paid')
                    ->label('Pedido Pago')
                    ->boolean()
                    ->trueIcon('heroicon-o-currency-dollar')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),
                IconEntry::make('is_paid_synced_to_biso')
                    ->label('Pagamento Sincronizado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextEntry::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i'),
                TextEntry::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i'),
            ]);
    }
}
