<?php

namespace App\Console\Commands;

use App\Helpers\HelperMagento;
use Illuminate\Console\Command;
use App\Models\PaginationMagento;

class ImportMagentoProductsCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-magento-products-cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Magento products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Import Magento products logic here
        $helper = HelperMagento::init();

        // Busca a configuração de paginação
        $pagination = PaginationMagento::first();

        if (!$pagination) {
            $pagination = new PaginationMagento([
                'page' => 1,
                'page_size' => 10,
                'sort_by' => 'updated_at',
            ]);
            $pagination->save();
        }
        $currentPage = $pagination?->page ?? 1;
        $pageSize = $pagination?->page_size ?? 10;
        $sortBy = $pagination?->sort_by ?? 'updated_at';

        $params = [
            'fields' => 'items[id,sku,name,price,status,type_id,attribute_set_id,weight,visibility,updated_at,created_at,custom_attributes[url_key,sync_biso_digital]],search_criteria[page_size,current_page],total_count[]',
            'searchCriteria' => [
                'filter_groups' => [
                    [
                        'filters' => [
                            [
                                'field' => 'type_id',
                                'value' => 'simple',
                                'condition_type' => 'eq',
                            ],
                        ],
                    ],
                    [
                        'filters' => [
                            [
                                'field' => 'sync_biso_digital',
                                'value' => '1', // ou 'sim', dependendo do valor salvo no Magento
                                'condition_type' => 'eq',
                            ],
                        ],
                    ],
                ],
                'currentPage' => $currentPage,
                'pageSize' => $pageSize,
                'sortOrders' => [
                    [
                        'field' => $sortBy,
                    ],
                ],
            ],
        ];

        $products = (array) $helper->getProducts($params);
        $this->saveProducts($products);

        // Controle de próxima página
        $totalCount = $products['total_count'] ?? 0;
        $itemsCount = isset($products['items']) ? count($products['items']) : 0;
        if ($itemsCount > 0 && $pagination) {
            $nextPage = $pagination->page + 1;
            $maxPages = $pageSize > 0 ? ceil($totalCount / $pageSize) : 1;
            if ($nextPage > $maxPages) {
                $nextPage = 1; // Reseta para 1 se passar do total
            }
            $pagination->update(['page' => $nextPage]);
        }
    }

    private function saveProducts($products)
    {
        $products = (array) $products;
        foreach ($products['items'] as $product) {
            $product = (array) $product;
            // Check if product exists by Magento ID
            $existingProduct = \App\Models\Product::where('m2_id', $product['id'])->first();

            if (!$existingProduct) {
                // Create new product
                \App\Models\Product::create([
                    'name' => $product['name'],
                    'm2_id' => $product['id'],
                    'm2_sku' => $product['sku'],
                    'm2_data' => $product,
                    'is_synced' => false,
                    'log' => 'NOVO'
                ]);
            }
        }
    }
}
