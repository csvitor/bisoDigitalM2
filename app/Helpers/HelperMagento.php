<?php

namespace App\Helpers;



class HelperMagento
{
    protected $service;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->service = new \Experius\Magento2ApiClient\Service\RestApi();
        $this->service->setToken(env('MAGENTO_TOKEN'));
        $this->service->setUrl($this->getUrl());
        $this->service->setStoreCode('default');
        $this->service->init();
    }

    public function getProducts($dataArray)
    {
        return $this->service->call('products', $dataArray, 'GET');
    }

    public function getService()
    {
        return $this->service;
    }

    public function getStocks($dataArray)
    {
        return $this->service->call('inventory/source-items', $dataArray, 'GET');
    }

    public function getUrl()
    {
        return env('MAGENTO_URL')  . '/rest/V1/';
    }

    public static function init()
    {
        return new self();
    }

    /**
     * Busca pedidos do Magento
     */
    public function getOrders($dataArray)
    {
        return $this->service->call('orders', $dataArray, 'GET');
    }

    /**
     * Busca um pedido específico pelo ID
     */
    public function getOrderById($orderId)
    {
        return $this->service->call("orders/{$orderId}", [], 'GET');
    }
}
