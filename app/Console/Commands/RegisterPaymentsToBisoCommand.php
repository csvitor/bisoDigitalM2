<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Helpers\HelperBisoDigital;
use Illuminate\Console\Command;

class RegisterPaymentsToBisoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'biso:register-payments {--order-id= : ID específico do pedido para registrar pagamento}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Registra pagamentos na Biso Digital antes de enviar pedidos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orderId = $this->option('order-id');

        // Se foi especificado um ID específico, processa apenas esse pedido
        if ($orderId) {
            $order = Order::find($orderId);
            if (!$order) {
                $this->error("Pedido com ID {$orderId} não encontrado.");
                return 1;
            }

            $this->info("Registrando pagamento do pedido {$order->order_number}...");
            $this->processOrderPayment($order);
            return 0;
        }

        // Processamento normal em lote
        $maxAttempts = 3;

        // Busca pedidos que ainda não tiveram pagamentos registrados na Biso
        $orders = Order::where('is_payment_synced', false)
            ->where('is_synced_to_biso', true) // Só processa pedidos já enviados para a Biso
            ->where('payment_sync_attempts', '<', $maxAttempts)
            ->whereNotNull('m2_data->payment') // Só pedidos que têm informação de pagamento
            ->orderBy('order_date', 'desc')
            ->limit(10)
            ->get();

        if ($orders->isEmpty()) {
            $this->info('Nenhum pedido elegível para registro de pagamento encontrado.');
            return;
        }

        $bisoHelper = HelperBisoDigital::init();

        foreach ($orders as $order) {
            $this->processOrderPayment($order, $bisoHelper);
        }
    }

    /**
     * Processa o pagamento de um pedido específico
     */
    private function processOrderPayment(Order $order, HelperBisoDigital $bisoHelper = null)
    {
        if (!$bisoHelper) {
            $bisoHelper = HelperBisoDigital::init();
        }

        // Verifica se o pedido já foi enviado para a Biso
        if (!$order->is_synced_to_biso) {
            $this->error("Pedido {$order->order_number} ainda não foi enviado para a Biso. Execute 'export:orders-to-biso' primeiro.");
            return false;
        }

        // Incrementa tentativas
        $order->increment('payment_sync_attempts');
        $order->update(['last_payment_sync_attempt' => now()]);

        $m2Data = $order->m2_data;
        
        if (!isset($m2Data['payment'])) {
            $this->error("Pedido {$order->order_number} não possui informações de pagamento.");
            $order->update(['log' => $order->log . '|ERRO_SEM_INFO_PAGAMENTO']);
            return false;
        }

        // Prepara dados do pagamento
        $paymentData = $this->preparePaymentData($order);
        
        if (!$paymentData) {
            $this->error("Não foi possível preparar dados de pagamento para o pedido {$order->order_number}.");
            return false;
        }

        // Tenta registrar o pagamento na Biso
        $response = $bisoHelper->addOrderPayment($order->m2_id, $paymentData);
        
        if ($response[0]) {
            $order->update([
                'is_payment_synced' => true,
                'payment_biso_id' => $paymentData['paymentId'],
                'payment_response_data' => $response[1],
                'log' => $order->log . '|PAGAMENTO_REGISTRADO_BISO',
            ]);
            
            $this->info("Pagamento do pedido {$order->order_number} registrado na Biso com sucesso!");
            return true;
        }

        $order->update([
            'payment_response_data' => $response[1],
            'log' => $order->log . '|ERRO_REGISTRO_PAGAMENTO_BISO',
        ]);

        $this->error("Erro ao registrar pagamento do pedido {$order->order_number}: " . json_encode($response[1]));
        return false;
    }

    /**
     * Prepara os dados do pagamento para envio à Biso
     */
    private function preparePaymentData(Order $order): ?array
    {
        $m2Data = $order->m2_data;
        $paymentInfo = $m2Data['payment'] ?? [];
        
        $magentoPaymentMethod = $paymentInfo['method'] ?? '';
        
        // Busca o mapeamento de forma de pagamento
        $paymentMethod = PaymentMethod::findByMagentoCode($magentoPaymentMethod);
        
        if (!$paymentMethod) {
            $this->warn("Método de pagamento '{$magentoPaymentMethod}' não mapeado para o pedido {$order->order_number}");
            
            // Fallback para método não mapeado
            return [
                'paymentId' => (string)($paymentInfo['entity_id'] ?? $order->m2_id),
                'paymentMethod' => $magentoPaymentMethod ?: 'Unknown',
                'formsOfPayment' => null,
                'paymentInstallment' => 1,
                'paymentValue' => round((float)$order->total_amount, 2),
            ];
        }

        return [
            'paymentId' => (string)($paymentInfo['entity_id'] ?? $order->m2_id),
            'paymentMethod' => $paymentMethod->biso_payment_method,
            'formsOfPayment' => $paymentMethod->biso_forms_of_payment,
            'paymentInstallment' => min($paymentMethod->max_installments, 1), // Pode ser expandido para ler do pedido
            'paymentValue' => round((float)$order->total_amount, 2),
        ];
    }
}