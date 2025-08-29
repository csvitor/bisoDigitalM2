<?php

namespace App\Filament\Resources\Configs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
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
            ]);
        }
}
