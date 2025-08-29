<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Console\Commands\ExportOrdersToBisoCommand;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Artisan;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('m2_id')
                    ->searchable(),
                TextColumn::make('biso_id')
                    ->searchable(),
                TextColumn::make('order_number')
                    ->label('Número do Pedido')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Valor Total')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('order_date')
                    ->label('Data do Pedido')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('m2_status')
                    ->label('Status Magento')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'complete' => 'success',
                        'processing' => 'warning',
                        'pending' => 'gray',
                        'canceled' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
                IconColumn::make('is_synced_to_biso')
                    ->label('Sincronizado Biso')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('sync_attempts')
                    ->label('Tentativas')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'gray',
                        $state < 3 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn (int $state): string => $state . '/3')
                    ->sortable(),
                IconColumn::make('is_paid')
                    ->label('Pago')
                    ->boolean()
                    ->trueIcon('heroicon-o-currency-dollar')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),
                TextColumn::make('biso_id')
                    ->label('ID Biso')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('m2_id')
                    ->label('ID Magento')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_sync_attempt')
                    ->label('Última Tentativa')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('sync_status')
                    ->label('Status de Sincronização')
                    ->options([
                        'synced' => 'Sincronizado',
                        'pending' => 'Pendente',
                        'failed' => 'Falharam (max tentativas)',
                        'retrying' => 'Em tentativas',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'] === 'synced',
                            fn (Builder $query) => $query->where('is_synced_to_biso', true)
                        )->when(
                            $data['value'] === 'pending',
                            fn (Builder $query) => $query->where('is_synced_to_biso', false)
                                ->where('sync_attempts', 0)
                        )->when(
                            $data['value'] === 'failed',
                            fn (Builder $query) => $query->where('is_synced_to_biso', false)
                                ->where('sync_attempts', '>=', 3)
                        )->when(
                            $data['value'] === 'retrying',
                            fn (Builder $query) => $query->where('is_synced_to_biso', false)
                                ->where('sync_attempts', '>', 0)
                                ->where('sync_attempts', '<', 3)
                        );
                    }),
                SelectFilter::make('payment_status')
                    ->label('Status de Pagamento')
                    ->options([
                        'paid' => 'Pago',
                        'pending' => 'Pendente',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'] === 'paid',
                            fn (Builder $query) => $query->where('is_paid', true)
                        )->when(
                            $data['value'] === 'pending',
                            fn (Builder $query) => $query->where('is_paid', false)
                        );
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Visualizar'),
                EditAction::make()
                    ->label('Editar'),
                Action::make('reset_sync')
                    ->label('Resetar Sincronização')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn ($record) => $record->hasReachedMaxSyncAttempts() || !$record->is_synced_to_biso)
                    ->requiresConfirmation()
                    ->modalHeading('Resetar Tentativas de Sincronização')
                    ->modalDescription('Esta ação irá resetar o contador de tentativas e permitir uma nova sincronização. Deseja continuar?')
                    ->action(function ($record) {
                        $record->resetSyncAttempts();
                        
                        Notification::make()
                            ->title('Tentativas resetadas')
                            ->body('As tentativas de sincronização foram resetadas. O pedido pode ser sincronizado novamente.')
                            ->success()
                            ->send();
                    }),
                Action::make('force_sync')
                    ->label('Forçar Sincronização')
                    ->icon('heroicon-o-cloud-arrow-up')
                    ->color('success')
                    ->visible(fn ($record) => $record->canBeResynced())
                    ->requiresConfirmation()
                    ->modalHeading('Forçar Sincronização Imediata')
                    ->modalDescription('Esta ação irá tentar sincronizar o pedido imediatamente com o Biso. Deseja continuar?')
                    ->action(function ($record) {
                        try {
                            // Executa o comando de export para este pedido específico
                            Artisan::call('export:orders-to-biso', [
                                '--order-id' => $record->id
                            ]);
                            
                            // Recarrega o record para pegar as atualizações
                            $record->refresh();
                            
                            if ($record->is_synced_to_biso) {
                                Notification::make()
                                    ->title('Sincronização realizada com sucesso')
                                    ->body('O pedido foi sincronizado com o Biso.')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Falha na sincronização')
                                    ->body('Não foi possível sincronizar o pedido. Verifique os logs para mais detalhes.')
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erro na sincronização')
                                ->body('Erro: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Excluir Selecionados'),
                    Action::make('bulk_reset_sync')
                        ->label('Resetar Sincronização em Lote')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Resetar Tentativas de Sincronização em Lote')
                        ->modalDescription('Esta ação irá resetar o contador de tentativas para todos os pedidos selecionados. Deseja continuar?')
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->hasReachedMaxSyncAttempts() || !$record->is_synced_to_biso) {
                                    $record->resetSyncAttempts();
                                    $count++;
                                }
                            }
                            
                            Notification::make()
                                ->title('Sincronização resetada em lote')
                                ->body("Tentativas de sincronização resetadas para {$count} pedidos.")
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}
