<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Helpers\HelperBisoDigital;
use Illuminate\Console\Command;

class ExportProductsToBisoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export-products-to-biso-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export products to Biso';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Export products to Biso logic here
        $limit = env('BISO_COUNT_PRODUCTS_CREATED', 10);
        $products = Product::where('is_synced', false)
            ->orderBy('updated_at', 'desc')->limit($limit)->get();

        foreach ($products as $product) {
            // Call Biso API to export each product

            $productData = $this->prepareProductForBiso((array) $product->m2_data);
            $bisoHelper = HelperBisoDigital::init();
            $product_biso = $bisoHelper->getProductById($productData['productId'], $productData['productSkuId']);
            $response = $product_biso && isset($product_biso['productId']) 
                ? $bisoHelper->updateProduct($productData) 
                : $bisoHelper->createProduct($productData);


            if ($response[0]) {
                $product->update([
                    'is_synced' => true,
                    'biso_id' => $productData['productId'],
                    'biso_sku' => $productData['productSkuId'],
                    'log' => $product->log . '|EXPORTED',
                    'request_data' => $productData,
                    'response_data' => $response
                ]);
                $this->info('Produto enviado com sucesso!');
                continue;
            }

            $this->error('Erro ao enviar produto:');

            $product->update([
                'is_synced' => false,
                'log' => $product->log . '|EXPORT_FAILED',
                'request_data' => $productData,
                'response_data' => $response
            ]);

            continue;
        }
    }


    private function prepareProductForBiso(array $m2_data): array
    {
        return [
            'productId'         => (string)($m2_data['id'] ?? ''),
            'productSkuId'      => (string)($m2_data['sku'] ?? ''),
            'productName'       => $m2_data['name'] ?? '',
            'productSkuName'    => $m2_data['name'] ?? '',
            'productDepartmentId' => (string)($m2_data['category_main']['id'] ?? ''),
            'productDepartmentName' => $m2_data['category_main']['name'] ?? '', // Magento não traz por padrão
            'productPrice'      => (float)($m2_data['price'] ?? 0),
            'productCost'       => (float) $this->getCustomAttributes($m2_data['custom_attributes'] ?? [], 'cost') ?? 0,
            'productSalePrice'  => (float)($m2_data['price'] ?? 0),
            'isActive'          => ($m2_data['status'] ?? 0) == 1,
        ];
    }

    /**
     * Get a specific custom attribute value by key.
     * @param array $custom_attributes
     * @param mixed $key
     * @return array|null
     */
    private function getCustomAttributes(array $custom_attributes, $key): mixed
    {
        if (isset($custom_attributes) && is_array($custom_attributes)) {
            foreach ($custom_attributes as $attr) {
                if(isset($attr['attribute_code']) && isset($attr['value']) && $key === $attr['attribute_code']) {
                    return $attr['value'];
                }
            }
        }
        return null;
    }
}
