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
            'productImageUrl'   => $this->getProductImageUrl($m2_data),
            'productBrand'      => $this->getCustomAttributes($m2_data['custom_attributes'] ?? [], 'brand') ?? '',
            'productPagePath'   => $this->getProductPagePath($m2_data),
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

    /**
     * Get product image URL from Magento data
     * @param array $m2_data
     * @return string|null
     */
    private function getProductImageUrl(array $m2_data): ?string
    {
        $baseUrl = rtrim(env('MAGENTO_URL', ''), '/');
        $mediaPath = '/media/catalog/product';
        
        // Tenta buscar a imagem principal em ordem de prioridade
        $imageFields = ['image', 'small_image', 'thumbnail'];
        
        foreach ($imageFields as $field) {
            $imagePath = $this->getCustomAttributes($m2_data['custom_attributes'] ?? [], $field);
            
            // Garante que imagePath é uma string
            if (is_string($imagePath) && $imagePath !== 'no_selection' && !empty(trim($imagePath))) {
                // Remove barras duplas e garante que a URL está formatada corretamente
                $fullUrl = $baseUrl . $mediaPath . '/' . ltrim($imagePath, '/');
                return str_replace('//', '/', str_replace($baseUrl . '//', $baseUrl . '/', $fullUrl));
            }
        }
        
        return null;
    }

    /**
     * Get product page path from Magento data
     * @param array $m2_data
     * @return string|null
     */
    private function getProductPagePath(array $m2_data): ?string
    {
        // Busca o url_key do produto
        $urlKey = $this->getCustomAttributes($m2_data['custom_attributes'] ?? [], 'url_key');
        
        if (is_string($urlKey) && !empty(trim($urlKey))) {
            // Retorna apenas o caminho relativo do produto
            // Formato padrão do Magento: /produto-url-key.html
            return '/' . trim($urlKey) . '.html';
        }
        
        // Fallback: constrói path baseado no SKU se url_key não estiver disponível
        $sku = $m2_data['sku'] ?? '';
        if (!empty($sku)) {
            // Converte o SKU para um formato de URL amigável
            $urlFriendlySku = strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $sku));
            $urlFriendlySku = preg_replace('/-+/', '-', $urlFriendlySku); // Remove hífens duplos
            $urlFriendlySku = trim($urlFriendlySku, '-'); // Remove hífens do início/fim
            
            return '/' . $urlFriendlySku . '.html';
        }
        
        return null;
    }
}
