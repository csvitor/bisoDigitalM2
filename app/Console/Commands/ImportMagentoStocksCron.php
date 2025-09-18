<?php

namespace App\Console\Commands;

use App\Helpers\HelperMagento;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Console\Command;

class ImportMagentoStocksCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-magento-stocks-cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Magento stocks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $productsBase = Product::all();

        if ($productsBase->isEmpty()) {
            $this->info('No products found to import stocks.');
            return;
        }

        // Import Magento stocks logic here
        $helper = HelperMagento::init();
        $productsMap = $this->convertProducts($productsBase);

        // Para evitar 414 (URL muito longa), dividimos os SKUs em lotes e usamos condition_type IN
        $skus = $productsBase->pluck('m2_sku')->filter()->values()->all();
        $chunkSize = 50; // tamanho do lote

        foreach (array_chunk($skus, $chunkSize) as $chunk) {
            $params = [
                'searchCriteria' => [
                    'filter_groups' => [
                        [
                            'filters' => [
                                [
                                    'field' => 'sku',
                                    'value' => implode(',', $chunk),
                                    'condition_type' => 'in',
                                ],
                            ],
                        ],
                    ],
                    'pageSize' => count($chunk),
                ],
            ];

            $stocksResponse = (array) $helper->getStocks($params);
            $items = isset($stocksResponse['items']) ? (array) $stocksResponse['items'] : [];
            foreach ($items as $item) {
                $item = (array) $item;
                $sku = $item['sku'] ?? null;
                if (!$sku || !isset($productsMap[$sku])) {
                    continue;
                }
                $current = $productsMap[$sku];
                $quantity = $item['quantity'] ?? 0;
                $status = $item['status'] ?? 0;

                if ($current->stocks()->exists()) {
                    $stockCurrent = $current->stocks()->first();
                    if ((float) $stockCurrent->quantity !== (float) $quantity || (int) $stockCurrent->is_in_stock !== (int) $status) {
                        $stockCurrent->update([
                            'quantity' => $quantity,
                            'is_in_stock' => $status,
                            'sync_biso_digital' => true,
                            'stock_logs' => array_merge($stockCurrent->stock_logs ?? [], [[
                                'changed_by' => 'system',
                                'old_quantity' => $stockCurrent->quantity,
                                'new_quantity' => $quantity,
                                'reason' => 'import',
                                'timestamp' => now()->toISOString(),
                            ]])
                        ]);
                    }
                    continue;
                }
                $current->stocks()->create([
                    'quantity' => $quantity,
                    'is_in_stock' => $status,
                    'sync_biso_digital' => true,
                    'stock_logs' => [
                        [
                            'changed_by' => 'system',
                            'old_quantity' => '-',
                            'new_quantity' => $quantity,
                            'reason' => 'import',
                            'timestamp' => now()->toISOString(),
                        ]
                    ]
                ]);
            }
        }
    }

    public function convertProducts($products)
    {
        $result = [];
        foreach ($products as $product) {
            $result[$product->m2_sku] = $product;
        }
        return $result;
    }
}
