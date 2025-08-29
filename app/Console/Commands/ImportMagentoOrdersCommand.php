<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Helpers\HelperMagento;
use Illuminate\Console\Command;

class ImportMagentoOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-magento-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa pedidos do Magento 2';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $helper = HelperMagento::init();
        
        $params = [
            'searchCriteria' => [
                'filter_groups' => [
                    [
                        'filters' => [
                            [
                                'field' => 'created_at',
                                'value' => now()->subDays(7)->format('Y-m-d H:i:s'), // últimos 7 dias
                                'condition_type' => 'gteq',
                            ],
                        ],
                    ],
                ],
                'currentPage' => 1,
                'pageSize' => 50,
                'sortOrders' => [
                    [
                        'field' => 'created_at',
                        'direction' => 'desc',
                    ],
                ],
            ],
        ];

        $orders = $helper->getOrders($params);
        $this->saveOrders($orders);
        
        $this->info('Importação de pedidos concluída!');
    }

    /**
     * Salva os pedidos no banco de dados
     */
    private function saveOrders($orders)
    {
        $orders = (array) $orders;
        
        if (!isset($orders['items'])) {
            $this->error('Nenhum pedido encontrado.');
            return;
        }

        foreach ($orders['items'] as $order) {
            $order = (array) $order;
            
            // Verifica se o pedido já existe
            $existingOrder = Order::where('m2_id', $order['entity_id'])->first();
            
            if (!$existingOrder) {
                // Determina se o pedido está pago baseado no status
                $isPaid = $this->isPaidStatus($order['status'] ?? '', $order['state'] ?? '');
                
                Order::create([
                    'm2_id' => $order['entity_id'],
                    'order_number' => $order['increment_id'],
                    'm2_status' => $order['status'] ?? '',
                    'm2_state' => $order['state'] ?? '',
                    'total_amount' => $order['grand_total'] ?? 0,
                    'currency' => $order['order_currency_code'] ?? 'BRL',
                    'order_date' => $order['created_at'],
                    'm2_data' => $order,
                    'is_paid' => $isPaid,
                    'log' => 'IMPORTADO_DO_MAGENTO',
                ]);
                
                $this->info("Pedido {$order['increment_id']} importado.");
            } else {
                // Verifica se houve mudança de status antes de atualizar
                $newStatus = $order['status'] ?? '';
                $newState = $order['state'] ?? '';
                $isPaid = $this->isPaidStatus($newStatus, $newState);
                
                // Só atualiza se houver mudança no status ou state
                if ($existingOrder->m2_status !== $newStatus || $existingOrder->m2_state !== $newState) {
                    $existingOrder->update([
                        'm2_status' => $newStatus,
                        'm2_state' => $newState,
                        'is_paid' => $isPaid,
                        'm2_data' => $order,
                        'log' => $existingOrder->log . '|STATUS_ATUALIZADO',
                    ]);
                    
                    $this->info("Status do pedido {$order['increment_id']} atualizado: {$newStatus}/{$newState}");
                }
            }
        }
    }

    /**
     * Determina se um pedido está pago baseado no status/state
     */
    private function isPaidStatus($status, $state)
    {
        $paidStatuses = ['complete', 'processing'];
        $paidStates = ['complete', 'processing'];
        
        return in_array($status, $paidStatuses) || in_array($state, $paidStates);
    }
}
