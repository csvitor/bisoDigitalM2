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
            $response = $bisoHelper->createProduct($productData);


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
            'productSkuName'    => $m2_data['name'] ?? '', // ou outro campo se necessário
            'productDepartmentId' => (string)($m2_data['attribute_set_id'] ?? ''),
            'productDepartmentName' => '', // Magento não traz por padrão, preencha se tiver
            'productPrice'      => (float)($m2_data['price'] ?? 0),
            'productCost'       => 0, // Preencha se tiver custo
            'productSalePrice'  => (float)($m2_data['price'] ?? 0), // ou outro campo se necessário
            'isActive'          => ($m2_data['status'] ?? 0) == 1,
        ];
    }

    private function callBisoApi(array $data)
    {
        // Este método não é mais necessário pois usamos o helper
        // Mantido para compatibilidade, mas pode ser removido
        $bisoHelper = HelperBisoDigital::init();
        return $bisoHelper->createProduct($data);
    }
}
