<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Product;
use App\Models\PaymentMethod;
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
            $this->processOrder($order, false, $maxAttempts);
        }
    }

    /**
     * Processa um pedido específico
     */
    private function processOrder(Order $order, bool $forced = false, int $maxAttempts = 3)
    {
        
        $bisoHelper = HelperBisoDigital::init();
        

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
        $subtotal = (float)($m2Data['subtotal'] ?? 0);
        $grandTotal = (float)($m2Data['grand_total'] ?? 0);

        // Prepara os itens do pedido
        $items = [];
        $itemsTotalValue = 0;
        $totalShippingDistributed = 0;

        if (isset($m2Data['items'])) {
            $itemCount = count($m2Data['items']);
            
            foreach ($m2Data['items'] as $index => $item) {
                $sku = $item['sku'] ?? '';

                // Busca o produto na tabela local para pegar os IDs do Biso
                $product = Product::where('m2_sku', $sku)->first();

                $itemTotal = (float)($item['row_total'] ?? 0);
                $itemDiscount = (float)($item['discount_amount'] ?? 0);
                
                // Calcula o frete proporcional baseado no subtotal
                $itemShipping = 0;
                if ($shippingAmount > 0 && $subtotal > 0) {
                    $itemShipping = ($itemTotal / $subtotal) * $shippingAmount;
                }

                // Para o último item, ajusta o frete para garantir que a soma seja exata
                if ($index === $itemCount - 1) {
                    $itemShipping = $shippingAmount - $totalShippingDistributed;
                }

                $itemTotalValue = round($itemTotal + $itemShipping, 2);
                $itemShipping = round($itemShipping, 2);
                $itemTotal = round($itemTotal, 2);

                $items[] = [
                    'productId' => (string)($product->biso_id ?? ''),
                    'productSkuId' => (string)($product->biso_sku ?? $sku),
                    'productName' => $item['name'] ?? '',
                    'productSkuName' => $item['name'] ?? '',
                    'quantitySold' => (int)($item['qty_ordered'] ?? 1),
                    'unitValue' => round((float)($item['price'] ?? 0), 2),
                    'totalValue' => $itemTotalValue, // Total do item + proporcional do frete
                    'discountValue' => round(abs($itemDiscount), 2),
                    'shippingPrice' => $itemShipping,
                    'itemValueWithoutShippingPrice' => $itemTotal,
                    'shippingPricePaidByCustomer' => $itemShipping,
                    'storeId' => 'loja',
                    'sellerId' => '',
                ];

                $itemsTotalValue += $itemTotalValue;
                $totalShippingDistributed += $itemShipping;
            }
        }
        
        // Usa o grand_total como referência principal para evitar discrepâncias
        $orderTotalValue = round($grandTotal, 2);

        // Extrai dados do endereço de entrega
        $shippingAddress = $m2Data['extension_attributes']['shipping_assignments'][0]['shipping']['address'] ?? null;
        $billingAddress = $m2Data['billing_address'] ?? null;
        
        // Usa endereço de entrega como prioridade, ou billing como fallback
        $deliveryAddress = $shippingAddress ?? $billingAddress;

        return [
            'orderId' => (string)$order->m2_id,
            'channel' => 'website',
            'sourcePlatform' => 'Magento 2',
            'totalValue' => $orderTotalValue, // Usar o grand_total do Magento
            'discountValue' => round(abs($discountAmount), 2),
            'createdAt' => $order->order_date->format('Y-m-d\TH:i:s'),
            'customerUniqueIdentifier' => (string)($m2Data['customer_id'] ?? $m2Data['customer_email'] ?? ''),
            'customerEmail' => $m2Data['customer_email'] ?? null,
            'customerDocument' => $this->extractDocument($m2Data),
            'customerFullName' => $this->extractCustomerFullName($m2Data),
            'customerPhone' => $this->extractCustomerPhone($deliveryAddress, $billingAddress),
            'customerDestinationAddressCity' => $deliveryAddress['city'] ?? null,
            'customerDestinationAddressState' => $deliveryAddress['region'] ?? null,
            'customerDestinationAddressCountry' => $deliveryAddress['country_id'] ?? null,
            'shippingPrice' => round($shippingAmount, 2),
            'shippingPricePaidByCustomer' => round($shippingAmount, 2),
            'shippingMethod' => $this->x($m2Data),
            'items' => $items,
            'origin' => 'Ecommerce',
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

    /**
     * Extrai o documento do cliente (CPF/CNPJ)
     */
    private function extractDocument($m2Data)
    {
        // Tenta pegar do customer_taxvat (campo principal do Magento)
        if (!empty($m2Data['customer_taxvat'])) {
            return preg_replace('/[^0-9]/', '', $m2Data['customer_taxvat']);
        }

        // Fallback: tenta pegar do billing address vat_id
        if (!empty($m2Data['billing_address']['vat_id'])) {
            return preg_replace('/[^0-9]/', '', $m2Data['billing_address']['vat_id']);
        }

        // Fallback: tenta pegar do shipping address vat_id
        $shippingAddress = $m2Data['extension_attributes']['shipping_assignments'][0]['shipping']['address'] ?? null;
        if (!empty($shippingAddress['vat_id'])) {
            return preg_replace('/[^0-9]/', '', $shippingAddress['vat_id']);
        }

        return null;
    }

    /**
     * Extrai o nome completo do cliente
     */
    private function extractCustomerFullName($m2Data)
    {
        $firstName = $m2Data['customer_firstname'] ?? '';
        $lastName = $m2Data['customer_lastname'] ?? '';
        
        if ($firstName && $lastName) {
            return trim($firstName . ' ' . $lastName);
        }

        // Fallback: tenta pegar do billing address
        $billingFirstName = $m2Data['billing_address']['firstname'] ?? '';
        $billingLastName = $m2Data['billing_address']['lastname'] ?? '';
        
        if ($billingFirstName && $billingLastName) {
            return trim($billingFirstName . ' ' . $billingLastName);
        }

        return trim($firstName . ' ' . $lastName) ?: null;
    }

    /**
     * Extrai o telefone do cliente
     */
    private function extractCustomerPhone($deliveryAddress, $billingAddress)
    {
        // Prioridade: endereço de entrega
        if (!empty($deliveryAddress['telephone'])) {
            return $this->formatPhone($deliveryAddress['telephone']);
        }

        // Fallback: endereço de cobrança
        if (!empty($billingAddress['telephone'])) {
            return $this->formatPhone($billingAddress['telephone']);
        }

        return null;
    }

    /**
     * Formata o telefone removendo caracteres especiais
     */
    private function formatPhone($phone)
    {
        if (empty($phone)) {
            return null;
        }

        // Remove todos os caracteres que não são números
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // Retorna apenas se tiver pelo menos 10 dígitos (formato brasileiro)
        return strlen($cleanPhone) >= 10 ? $cleanPhone : null;
    }

    /**
     * Extrai o nome da transportadora do shipping_description
     * Remove informações de prazo de entrega
     */
    private function extractShippingCarrier($m2Data)
    {
        $shippingDescription = $m2Data['shipping_description'] ?? '';
        
        if (empty($shippingDescription)) {
            return 'In-store Pickup';
        }
        
        // Remove padrões comuns de prazo de entrega
        // Exemplos: "DIALOGO - Normal (5 dias úteis)" -> "DIALOGO"
        //          "PRESSA FR (TESTE) - Pesada (3 dias úteis)" -> "PRESSA FR (TESTE)"
        
        // Remove tudo após " - " seguido de informações de prazo
        $carrier = preg_replace('/\s*-\s*[^-]*\(\d+\s*dias?\s*úteis?\).*$/', '', $shippingDescription);
        
        // Remove apenas a parte do prazo se não tiver " - "
        $carrier = preg_replace('/\s*\(\d+\s*dias?\s*úteis?\).*$/', '', $carrier);
        
        // Limpa espaços extras
        $carrier = trim($carrier);
        
        return !empty($carrier) ? $carrier : 'In-store Pickup';
    }
}
