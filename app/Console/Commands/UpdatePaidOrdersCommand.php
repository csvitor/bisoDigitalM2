<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Helpers\HelperMagento;
use App\Helpers\HelperBisoDigital;
use Illuminate\Console\Command;

class UpdatePaidOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-paid-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza status dos pedidos no Magento e sincroniza mudanças com Biso Digital';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Busca pedidos que foram sincronizados com o Biso
        $orders = Order::where('is_synced_to_biso', true)
            ->whereNotNull('biso_id')
            ->get();

        $helper = HelperMagento::init();
        $bisoHelper = HelperBisoDigital::init();

        foreach ($orders as $order) {
            // Busca o pedido atualizado no Magento
            $magentoOrder = $helper->getOrderById($order->m2_id);
            
            if ($magentoOrder) {
                $magentoOrder = (array) $magentoOrder;
                $currentStatus = $magentoOrder['status'] ?? '';
                $currentState = $magentoOrder['state'] ?? '';
                
                // Verifica se houve mudança de status
                if ($order->m2_status !== $currentStatus || $order->m2_state !== $currentState) {
                    $isPaid = $this->isPaidStatus($currentStatus, $currentState);
                    $newBisoStatus = $this->mapMagentoStatusToBiso($currentStatus, $currentState, $isPaid);
                    
                    // Atualiza o pedido local
                    $order->update([
                        'is_paid' => $isPaid,
                        'm2_status' => $currentStatus,
                        'm2_state' => $currentState,
                        'm2_data' => $magentoOrder,
                        'log' => $order->log . '|STATUS_ATUALIZADO_MAGENTO',
                    ]);
                    
                    // Sincroniza o novo status com o Biso
                    $this->updateBisoOrderStatus($order, $newBisoStatus, $bisoHelper);
                    
                    $this->info("Pedido {$order->order_number} atualizado: {$currentStatus}/{$currentState} -> Biso: {$newBisoStatus}");
                }
            }
        }
    }

    /**
     * Atualiza o status do pedido no Biso Digital
     */
    private function updateBisoOrderStatus(Order $order, string $newStatus, HelperBisoDigital $bisoHelper)
    {
        $response = null;
        $logSuffix = '';
        switch ($newStatus) {
            case 'paid':
                $response = $bisoHelper->markOrderAsPaid($order->biso_id);
                $logSuffix = 'MARCADO_COMO_PAGO_BISO';
                break;
                
            case 'cancelled':
                $response = $bisoHelper->markOrderAsCanceled($order->biso_id);
                $logSuffix = 'CANCELADO_BISO';
                break;
                
            case 'open':
                $response = $bisoHelper->markOrderAsOpen($order->biso_id);
                $logSuffix = 'REABERTO_BISO';
                break;
                
            case 'closed':
                // Para closed, usa o método updateOrderStatus com array de dados
                $response = $bisoHelper->updateOrderStatus($order->biso_id, ['status' => 'closed']);
                $logSuffix = 'FINALIZADO_BISO';
                break;
        }

        if ($response && $response[0]) {
            $order->update([
                'log' => $order->log . '|' . $logSuffix,
            ]);
            $this->info("Status '{$newStatus}' sincronizado com Biso para pedido {$order->order_number}");
        } else {
            $order->update([
                'log' => $order->log . '|ERRO_SYNC_STATUS_BISO_' . strtoupper($newStatus),
            ]);
            $this->error("Erro ao sincronizar status '{$newStatus}' com Biso para pedido {$order->order_number}");
        }
    }

    /**
     * Mapeia status do Magento para status da Biso Digital
     * Valores permitidos na Biso: open, paid, closed, cancelled
     */
    private function mapMagentoStatusToBiso($m2Status, $m2State, $isPaid)
    {
        // Status cancelled
        if (in_array($m2Status, ['canceled']) || in_array($m2State, ['canceled'])) {
            return 'cancelled';
        }

        // Status closed (complete)
        if (in_array($m2Status, ['complete']) || in_array($m2State, ['complete'])) {
            return 'closed';
        }

        // Status paid (processing, mas pago)
        if ($isPaid || in_array($m2Status, ['processing']) || in_array($m2State, ['processing'])) {
            return 'paid';
        }

        // Status open (default para pending, new, etc.)
        return 'open';
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
