<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('m2_id')
                    ->label('ID Magento')
                    ->required()
                    ->disabled(),
                TextInput::make('biso_id')
                    ->label('ID Biso')
                    ->disabled(),
                TextInput::make('order_number')
                    ->label('Número do Pedido')
                    ->required()
                    ->disabled(),
                TextInput::make('m2_status')
                    ->label('Status Magento')
                    ->disabled(),
                TextInput::make('m2_state')
                    ->label('State Magento')
                    ->disabled(),
                TextInput::make('total_amount')
                    ->label('Valor Total')
                    ->required()
                    ->numeric()
                    ->prefix('R$')
                    ->disabled(),

                Textarea::make('m2_data')
                    ->label('Dados Magento (JSON)')
                    ->disabled()
                    ->rows(20)
                    ->formatStateUsing(fn ($state) => !empty($state) && is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state),
                Textarea::make('request_data')
                    ->label('Dados da Requisição')
                    ->disabled()
                    ->rows(20)
                    ->formatStateUsing(fn ($state) => !empty($state) && is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state),
                Textarea::make('response_data')
                    ->label('Dados da Resposta')
                    ->disabled()
                    ->rows(10)
                    ->formatStateUsing(fn ($state) => !empty($state) && is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state),
                Textarea::make('log')
                    ->label('Log de Ações')
                    ->columnSpanFull()
                    ->disabled(),
            ]);
    }
}
