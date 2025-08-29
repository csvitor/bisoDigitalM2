<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Product;
use App\Helpers\HelperBisoDigital;
use Illuminate\Console\Command;

class ExportOrdersToBisoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:orders-to-biso {--order-id= : ID específico do pedido para forçar sincronização}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exporta pedidos para o Biso Digital';

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

            $this->info("Forçando sincronização do pedido {$order->order_number}...");
            $this->processOrder($order, true); // true indica que é uma sincronização forçada
            return 0;
        }

        // Processamento normal em lote
        $maxAttempts = 3; // Máximo de 3 tentativas

        $orders = Order::where('is_synced_to_biso', false)
            ->where('sync_attempts', '<', $maxAttempts) // Só pega pedidos com menos de 3 tentativas
            ->orderBy('order_date', 'desc')
            ->limit(10)
            ->get();

        if ($orders->isEmpty()) {
            $this->info('Nenhum pedido elegível para sincronização encontrado.');
            return;
        }

        $bisoHelper = HelperBisoDigital::init();

        foreach ($orders as $order) {
            $this->processOrder($order, false, $maxAttempts, $bisoHelper);
        }
    }

    /**
     * Processa um pedido específico
     */
    private function processOrder(Order $order, bool $forced = false, int $maxAttempts = 3, HelperBisoDigital $bisoHelper = null)
    {
        if (!$bisoHelper) {
            $bisoHelper = HelperBisoDigital::init();
        }

        // Se for forçado, não incrementa tentativas na inicialização
        if (!$forced) {
            // Incrementa tentativas e atualiza timestamp
            $order->increment('sync_attempts');
            $order->update(['last_sync_attempt' => now()]);
        } else {
            // Para sincronização forçada, apenas atualiza o timestamp
            $order->update(['last_sync_attempt' => now()]);
        }

        // Verifica se todos os produtos do pedido estão integrados com o Biso
        if (!$this->validateProductsIntegration($order)) {
            if (!$forced) {
                $order->update([
                    'log' => $order->log . '|PRODUTOS_NAO_INTEGRADOS_BISO',
                ]);
            }
            $this->error("Pedido {$order->order_number} não pode ser enviado: produtos não estão integrados com o Biso.");
            return;
        }
        $orderData = $this->prepareOrderForBiso($order);
        $response = $bisoHelper->createOrder($orderData);
        if ($response[0]) {
            $orderBiso = $bisoHelper->getOrderById($orderData['orderId']);
            $order->update([
                'is_synced_to_biso' => true,
                'biso_id'       => $orderData['orderId'] ?? null,
                'response_data' => $orderBiso ?: $response,
                'request_data'  => $orderData,
                'log'           => $order->log . ($forced ? '|REENVIADO_PARA_BISO_FORCADO' : '|ENVIADO_PARA_BISO'),
            ]);
            $this->info("Pedido {$order->order_number} enviado para o Biso com sucesso!");
            return true;
        }
        $attemptInfo = "Tentativa {$order->sync_attempts}/{$maxAttempts}";
        $logMessage = $order->sync_attempts >= $maxAttempts
            ? '|ERRO_ENVIO_BISO_MAX_TENTATIVAS'
            : '|ERRO_ENVIO_BISO';

        if ($forced) {
            $logMessage = '|ERRO_ENVIO_BISO_FORCADO';
        }

        $order->update([
            'response_data' => $response,
            'request_data'  => $orderData,
            'log'           => $order->log . $logMessage,
        ]);

        if ($order->sync_attempts >= $maxAttempts && !$forced) {
            $this->error("Pedido {$order->order_number} atingiu o máximo de tentativas ({$maxAttempts}). Não será mais processado.");
            return;
        }
        $this->error("Erro ao enviar pedido {$order->order_number}" . ($forced ? " (forçado)" : " ({$attemptInfo})") . ": " . json_encode($response[1]));
    }

    /**
     * Valida se todos os produtos do pedido estão integrados com o Biso
     */
    private function validateProductsIntegration(Order $order)
    {
        $m2Data = $order->m2_data;

        if (!isset($m2Data['items'])) {
            return false;
        }

        foreach ($m2Data['items'] as $item) {
            $sku = $item['sku'] ?? '';

            // Busca o produto na tabela local pelo SKU do Magento
            $product = Product::where('m2_sku', $sku)->first();

            // Verifica se o produto existe e se está integrado com o Biso
            if (!$product || !$product->biso_id || !$product->biso_sku) {
                $this->error("Produto com SKU {$sku} não está integrado com o Biso.");
                return false;
            }
        }

        return true;
    }

    /**
     * Prepara os dados do pedido para o Biso Digital
     */
    private function prepareOrderForBiso(Order $order)
    {
        $m2Data = $order->m2_data;

        // Calcula valores do pedido
        $shippingAmount = (float)($m2Data['shipping_amount'] ?? 0);
        $discountAmount = (float)($m2Data['discount_amount'] ?? 0);
        $taxAmount = (float)($m2Data['tax_amount'] ?? 0);
        $subtotal = (float)($m2Data['subtotal'] ?? 0);

        // Prepara os itens do pedido
        $items = [];
        $itemsTotalValue = 0;

        if (isset($m2Data['items'])) {
            foreach ($m2Data['items'] as $item) {
                $sku = $item['sku'] ?? '';

                // Busca o produto na tabela local para pegar os IDs do Biso
                $product = Product::where('m2_sku', $sku)->first();

                $itemTotal = (float)($item['row_total'] ?? 0);
                $itemDiscount = (float)($item['discount_amount'] ?? 0);
                $itemShipping = $shippingAmount > 0 ? ($itemTotal / $subtotal) * $shippingAmount : 0;

                $items[] = [
                    'productId' => (string)($product->biso_id ?? ''),
                    'productSkuId' => (string)($product->biso_sku ?? $sku),
                    'productName' => $item['name'] ?? '',
                    'productSkuName' => $item['name'] ?? '',
                    'quantitySold' => (int)($item['qty_ordered'] ?? 1),
                    'unitValue' => (float)($item['price'] ?? 0),
                    'totalValue' => $itemTotal + $itemShipping, // Total do item + proporcional do frete
                    'discountValue' => abs($itemDiscount),
                    'shippingPrice' => $itemShipping,
                    'itemValueWithoutShippingPrice' => $itemTotal,
                    'shippingPricePaidByCustomer' => $itemShipping,
                    'storeId' => 'loja',
                    'sellerId' => '',
                ];

                $itemsTotalValue += $itemTotal + $itemShipping;
            }
        }

        // Prepara informações de pagamento
        $payments = [];
        if (isset($m2Data['payment'])) {
            $payments[] = [
                'paymentId' => (string)($m2Data['payment']['entity_id'] ?? $order->m2_id),
                'paymentMethod' => $m2Data['payment']['method'] ?? '',
                'paymentInstallment' => 1,
                'paymentValue' => $order->total_amount,
            ];
        }

        return [
            'orderId' => (string)$order->m2_id,
            'channel' => 'website',
            'totalValue' => $itemsTotalValue, // Usa a soma calculada dos itens
            'discountValue' => abs($discountAmount),
            'createdAt' => $order->order_date->format('Y-m-d\TH:i:s'),
            'customerUniqueIdentifier' => (string)($m2Data['customer_id'] ?? $m2Data['customer_email'] ?? ''),
            'shippingPrice' => $shippingAmount,
            'shippingPricePaidByCustomer' => $shippingAmount,
            'payments' => $payments,
            'items' => $items,
            'origin' => 'Physical Store',
            'status' => $this->mapMagentoStatusToBiso($order->m2_status, $order->m2_state, $order->is_paid),
            'shippingType' => $shippingAmount > 0 ? 'paid' : 'free',
        ];
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
}
