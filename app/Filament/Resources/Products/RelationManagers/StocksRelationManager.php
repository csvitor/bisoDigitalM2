<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StocksRelationManager extends RelationManager
{
    protected static string $relationship = 'stocks';

    protected static ?string $recordTitleAttribute = 'quantity';

    protected static ?string $title = 'Estoques';

    protected static ?string $modelLabel = 'Estoque';

    protected static ?string $pluralModelLabel = 'Estoques';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quantity')
                    ->label('Quantidade')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'danger',
                        $state < 10 => 'warning',
                        default => 'success',
                    }),
                IconColumn::make('is_in_stock')
                    ->label('Em Estoque')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('sync_biso_digital')
                    ->label('Sync Biso Digital')
                    ->boolean()
                    ->trueIcon('heroicon-o-cloud-arrow-up')
                    ->falseIcon('heroicon-o-cloud-arrow-down')
                    ->trueColor('info')
                    ->falseColor('gray'),
                IconColumn::make('is_synced')
                    ->label('Sincronizado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Novo Estoque')
                    ->form([
                        TextInput::make('quantity')
                            ->label('Quantidade')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        Toggle::make('is_in_stock')
                            ->label('Em Estoque')
                            ->default(true),
                        Toggle::make('sync_biso_digital')
                            ->label('Sincronizar com Biso Digital')
                            ->default(false),
                        Toggle::make('is_synced')
                            ->label('Sincronizado')
                            ->default(false),
                        Textarea::make('stock_logs')
                            ->label('Logs de Estoque')
                            ->placeholder('Logs serão gerados automaticamente')
                            ->disabled(),
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Visualizar'),
                EditAction::make()
                    ->label('Editar')
                    ->form([
                        TextInput::make('quantity')
                            ->label('Quantidade')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        Toggle::make('is_in_stock')
                            ->label('Em Estoque'),
                        Toggle::make('sync_biso_digital')
                            ->label('Sincronizar com Biso Digital'),
                        Toggle::make('is_synced')
                            ->label('Sincronizado'),
                        Textarea::make('stock_logs')
                            ->label('Logs de Estoque')
                            ->rows(5)
                            ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT) : $state),
                    ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Excluir Selecionados'),
                ]),
            ]);
    }
}
