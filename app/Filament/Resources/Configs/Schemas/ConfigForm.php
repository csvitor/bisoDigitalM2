<?php

namespace App\Filament\Resources\Configs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ConfigForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
            TextInput::make('magento_token')
                ->label('Token do Magento')
                ->password()
                ->required()
                ->helperText('Token de acesso à API do Magento'),
            TextInput::make('magento_url')
                ->label('URL do Magento')
                ->required()
                ->url()
                ->helperText('https://dominio.com.br'),
            TextInput::make('biso_client_id')
                ->label('Client ID do Biso')
                ->required()
                ->helperText('Identificador do cliente na API do Biso'),
            TextInput::make('biso_api_key')
                ->label('API Key do Biso')
                ->password()
                ->required()
                ->helperText('Chave de acesso à API do Biso'),

            // Seção de Status dos Crons
            Select::make('magento_cron_get_products_status')
                ->label('Status: Importar Produtos do Magento')
                ->options(['enabled' => 'Ativado', 'disabled' => 'Desativado'])
                ->required()
                ->default('disabled'),
            Select::make('magento_cron_sync_products_status')
                ->label('Status: Sincronizar Produtos com Biso')
                ->options(['enabled' => 'Ativado', 'disabled' => 'Desativado'])
                ->required()
                ->default('disabled'),
            Select::make('magento_cron_sync_orders_status')
                ->label('Status: Sincronizar Pedidos')
                ->options(['enabled' => 'Ativado', 'disabled' => 'Desativado'])
                ->required()
                ->default('disabled'),
            Select::make('magento_cron_sync_orders_paid_status')
                ->label('Status: Atualizar Pedidos Pagos')
                ->options(['enabled' => 'Ativado', 'disabled' => 'Desativado'])
                ->required()
                ->default('disabled'),
            Select::make('magento_cron_sync_inventory_status')
                ->label('Status: Sincronizar Estoque')
                ->options(['enabled' => 'Ativado', 'disabled' => 'Desativado'])
                ->required()
                ->default('disabled'),



            // Seção de Status dos Crons do Biso
            Select::make('biso_cron_check_product_exists_status')
                ->label('Status: Verificar Produtos no Biso')
                ->options(['enabled' => 'Ativado', 'disabled' => 'Desativado'])
                ->required()
                ->default('disabled'),
            Select::make('biso_cron_check_order_paid_status')
                ->label('Status: Verificar Pedidos Pagos no Biso')
                ->options(['enabled' => 'Ativado', 'disabled' => 'Desativado'])
                ->required()
                ->default('disabled'),



            // Contadores e Limites
            TextInput::make('magento_count_products_created')
                ->label('Contador: Produtos Criados no Magento')
                ->required()
                ->numeric()
                ->default(0)
                ->helperText('Número de produtos importados'),
            TextInput::make('magento_count_orders_created')
                ->label('Contador: Pedidos Criados no Magento')
                ->required()
                ->numeric()
                ->default(0)
                ->helperText('Número de pedidos importados'),

            // Configurações de Status Inválido (em horas)
            TextInput::make('magento_invalid_status_processing')
                ->label('Horas: Status Processing Inválido')
                ->required()
                ->numeric()
                ->default(744)
                ->helperText('Tempo em horas para considerar status processing como inválido'),
            TextInput::make('magento_invalid_status_canceled')
                ->label('Horas: Status Canceled Inválido')
                ->required()
                ->numeric()
                ->default(3)
                ->helperText('Tempo em horas para considerar status canceled como inválido'),
            TextInput::make('magento_invalid_status_pending')
                ->label('Horas: Status Pending Inválido')
                ->required()
                ->numeric()
                ->default(1)
                ->helperText('Tempo em horas para considerar status pending como inválido'),

            // Limites de Processamento do Biso
            TextInput::make('biso_count_send_stock')
                ->label('Limite: Envio de Estoque para Biso')
                ->required()
                ->numeric()
                ->default(10)
                ->helperText('Quantidade máxima de atualizações de estoque por execução'),
            TextInput::make('biso_count_products_created')
                ->label('Limite: Produtos Criados no Biso')
                ->required()
                ->numeric()
                ->default(5)
                ->helperText('Quantidade máxima de produtos criados por execução'),
            TextInput::make('biso_count_orders_created')
                ->label('Limite: Pedidos Criados no Biso')
                ->required()
                ->numeric()
                ->default(10)
                ->helperText('Quantidade máxima de pedidos criados por execução'),
                // Adicionando campos que faltam (agora como strings para expressões cron)
                TextInput::make('cron_export_products')
                    ->label('Cron: Exportar Produtos')
                    ->required()
                    ->default('* * * * *')
                    ->helperText('Expressão cron para exportação de produtos'),

                TextInput::make('cron_import_products')
                    ->label('Cron: Importar Produtos')
                    ->required()
                    ->default('* * * * *')
                    ->helperText('Expressão cron para importação de produtos'),

                TextInput::make('cron_export_stocks')
                    ->label('Cron: Exportar Estoques')
                    ->required()
                    ->default('* * * * *')
                    ->helperText('Expressão cron para exportação de estoques'),

                TextInput::make('cron_import_stocks')
                    ->label('Cron: Importar Estoques')
                    ->required()
                    ->default('* * * * *')
                    ->helperText('Expressão cron para importação de estoques'),

                TextInput::make('cron_export_orders')
                    ->label('Cron: Exportar Pedidos')
                    ->required()
                    ->default('* * * * *')
                    ->helperText('Expressão cron para exportação de pedidos'),

                TextInput::make('cron_import_orders')
                    ->label('Cron: Importar Pedidos')
                    ->required()
                    ->default('* * * * *')
                    ->helperText('Expressão cron para importação de pedidos'),

                TextInput::make('cron_update_orders')
                    ->label('Cron: Atualizar Pedidos')
                    ->required()
                    ->default('* * * * *')
                    ->helperText('Expressão cron para atualização de pedidos'),

                // logs biso 
                Select::make('logs_biso_api')
                    ->label('Logs: Ativar Logs da API do Biso')
                    ->options(['1' => 'Ativado', '0' => 'Desativado'])
                    ->required()
                    ->default('0')
                    ->helperText('Ativa ou desativa o registro de logs para chamadas à API do Biso'),

                // Categorias permitidas
                Textarea::make('allowed_categories_input')
                    ->label('Categorias Permitidas')
                    ->placeholder('1,2,3,10,15')
                    ->helperText('IDs das categorias do Magento que serão aceitas (separados por vírgula). Ex: 1,2,3,10,15')
                    ->dehydrateStateUsing(function ($state) {
                        if (empty($state)) {
                            return null;
                        }
                        // Converte string "1,2,3" para array [1,2,3]
                        return array_map('intval', array_filter(explode(',', $state)));
                    })
                    ->formatStateUsing(function ($state, $record) {
                        // Carrega do campo allowed_categories e converte para string
                        $categories = $record?->allowed_categories ?? [];
                        return is_array($categories) ? implode(',', $categories) : '';
                    })
                    ->statePath('allowed_categories'),
            ]);
        }
}
