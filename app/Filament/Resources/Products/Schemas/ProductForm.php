<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados principais')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome do Produto')
                            ->disabled(),
                    ]),
                Section::make('Identificadores')
                    ->schema([
                        TextInput::make('m2_id')
                            ->label('ID Magento')
                            ->numeric()
                            ->disabled(),
                        TextInput::make('biso_id')
                            ->label('ID Biso')
                            ->numeric()
                            ->disabled(),
                        TextInput::make('biso_sku')
                            ->label('SKU Biso')
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('m2_sku')
                            ->label('SKU Magento')
                            ->required()
                            ->unique(ignoreRecord: true),
                    ])->columns(2),
                Section::make('Dados Magento')
                    ->schema([
                        Textarea::make('m2_data')
                            ->label('Dados do Magento (JSON)')
                            ->columnSpanFull()
                            ->disabled()
                            ->rows(20)
                            ->formatStateUsing(fn ($state) => !empty($state) && is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state),
                    ]),
                Section::make('Dados Integração')
                    ->schema([
                        Textarea::make('request_data')
                            ->label('Dados da Requisição')
                            ->columnSpanFull()
                            ->disabled()
                            ->rows(8)
                            ->formatStateUsing(fn ($state) => !empty($state) && is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state),
                        Textarea::make('response_data')
                            ->label('Dados da Resposta')
                            ->columnSpanFull()
                            ->disabled()
                            ->rows(8)
                            ->formatStateUsing(fn ($state) => !empty($state) && is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state),
                    ])->columns(2),
                Section::make('Sincronização')
                    ->schema([
                        Toggle::make('is_synced')
                            ->label('Produto Sincronizado')
                            ->required(),
                    ]),
                Section::make('Log')
                    ->schema([
                        Textarea::make('log')
                            ->label('Log de Ações')
                            ->columnSpanFull()
                            ->disabled(),
                    ]),
            ]);
    }
}
