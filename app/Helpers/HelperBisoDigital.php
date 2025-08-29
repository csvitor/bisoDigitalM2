<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class HelperBisoDigital
{
    const API_URL = 'https://api.bisodigital.com';
    const API_HOMOLOG_URL = 'https://baas-api-homolog.biso.digital';
    const API_STORE_ID = 'loja';

    private $httpClient;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->http();
    }

    /**
     * Envia um pedido para o Biso Digital
     */
    public function createOrder(array $orderData)
    {
        $url = sprintf('%s/orders', $this->getUrl());
        $response = $this->httpClient->post($url, $orderData);

        if ($response->successful()) {
            return [true, $response->json()];
        }
        
        return [false, $response->json() ?: $response->body()];
    }

    public function getOrderById($orderId)
    {
        $url = sprintf('%s/orders/%s', $this->getUrl(), $orderId);
        $response = $this->httpClient->get($url);

        if ($response->successful()) {
            return $response->json();
        }
        
        return null;
    }

    /**
     * Envia um produto para o Biso Digital
     */
    public function createProduct(array $productData)
    {
        $url = sprintf('%s/products', $this->getUrl());
        $response = $this->httpClient->post($url, $productData);

        if ($response->successful()) {
            return [true, $response->json()];
        }

        return [false, $response->json() ?: $response->body()];
    }

    public function createStockToBiso($productId, $productSkuId, array $stockData)
    {

        if ($this->middlewareStockCreate($productId, $productSkuId)) {
            return $this->updateStockToBiso($productId, $productSkuId, $stockData);
        }

        $url = sprintf(
            '%s/products/%s/sku/%s/stock',
            $this->getUrl(),
            $productId,
            $productSkuId
        );

        return $this->httpClient->post($url, $stockData);
    }

    public function getHttpClient()
    {
        return $this->httpClient;
    }

    public function getStock($id, $sku)
    {
        $url = sprintf(
            '%s/products/%s/sku/%s/stock/%s',
            $this->getUrl(),
            $id,
            $sku,
            HelperBisoDigital::API_STORE_ID
        );

        return $this->httpClient->get($url);
    }

    public function getUrl()
    {
        return env('APP_ENV') === 'local' ? static::API_HOMOLOG_URL : static::API_URL;
    }

    public function http()
    {
        $this->httpClient = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'x-biso-client-id' => env('BISO_CLIENT_ID'),
            'x-biso-api-key' => env('BISO_API_KEY'),
        ]);
    }

    public static function init()
    {
        return new self();
    }

    /**
     * Atualiza o status de um pedido no Biso Digital
     */
    public function updateOrderStatus($bisoOrderId, array $statusData)
    {
        $url = sprintf('%s/orders/%s', $this->getUrl(), $bisoOrderId);
        $response = $this->httpClient->patch($url, $statusData);

        if ($response->successful()) {
            return [true, $response->json()];
        }
        
        return [false, $response->json() ?: $response->body()];
    }

    /**
     * Marca um pedido como pago no Biso Digital usando PATCH
     */
    public function markOrderAsPaid($bisoOrderId, array $additionalData = [])
    {
        $statusData = [
            'status' => 'paid',
            'shippingType' => 'paid'
        ];

        // Adiciona dados opcionais como origin se fornecido
        if (!empty($additionalData)) {
            $statusData = array_merge($statusData, $additionalData);
        }

        return $this->updateOrderStatus($bisoOrderId, $statusData);
    }

    /**
     * Marca um pedido como aberto no Biso Digital
     */
    public function markOrderAsOpen($bisoOrderId, array $additionalData = [])
    {
        $statusData = [
            'status' => 'open'
        ];

        if (!empty($additionalData)) {
            $statusData = array_merge($statusData, $additionalData);
        }

        return $this->updateOrderStatus($bisoOrderId, $statusData);
    }

    /**
     * Marca um pedido como cancelado no Biso Digital
     */
    public function markOrderAsCanceled($bisoOrderId, array $additionalData = [])
    {
        $statusData = [
            'status' => 'canceled'
        ];

        if (!empty($additionalData)) {
            $statusData = array_merge($statusData, $additionalData);
        }

        return $this->updateOrderStatus($bisoOrderId, $statusData);
    }

    /**
     * Atualiza o tipo de entrega do pedido
     */
    public function updateOrderShippingType($bisoOrderId, string $shippingType, array $additionalData = [])
    {
        $statusData = [
            'shippingType' => $shippingType // 'paid', 'free', etc.
        ];

        if (!empty($additionalData)) {
            $statusData = array_merge($statusData, $additionalData);
        }

        return $this->updateOrderStatus($bisoOrderId, $statusData);
    }

    /**
     * Check is stock exists
     * @param mixed $productId
     * @param mixed $productSkuId
     * @return bool
     */
    public function middlewareStockCreate($productId, $productSkuId)
    {
        $response = $this->httpClient->get(sprintf(
            '%s/products/%s/sku/%s/stock/%s',
            $this->getUrl(),
            $productId,
            $productSkuId,
            HelperBisoDigital::API_STORE_ID
        ));

        return $response->successful();
    }

    /**
     * Atualiza estoque de um produto no Biso Digital
     */
    public function updateStock($productId, $productSkuId, array $stockData)
    {
        return $this->createStockToBiso($productId, $productSkuId, $stockData);
    }

    public function updateStockToBiso($productId, $productSkuId, array $stockData)
    {
        $url = sprintf(
            '%s/products/%s/sku/%s/stock/%s',
            $this->getUrl(),
            $productId,
            $productSkuId,
            HelperBisoDigital::API_STORE_ID
        );
        return $this->httpClient->patch($url, $stockData);
    }

    /**
     * Testa a conectividade com a API do Biso Digital
     */
    public function testConnection()
    {
        try {
            // Tenta fazer uma requisição para listar produtos (método mais simples para testar)
            $url = sprintf('%s/products?limit=1', $this->getUrl());
            $response = $this->httpClient->get($url);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'response' => $response->successful() ? 'Conexão estabelecida com sucesso' : $response->json(),
                'url' => $url
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => 0,
                'response' => $e->getMessage(),
                'url' => $url ?? 'N/A'
            ];
        }
    }
}
