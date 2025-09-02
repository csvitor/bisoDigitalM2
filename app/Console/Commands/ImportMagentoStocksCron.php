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

        // Import Magento products logic here
        $helper = HelperMagento::init();
        $params = [
            'searchCriteria' => [
                'filter_groups' => [
                    [
                        'filters' => [
                            [
                                'field' => 'sku',
                                'value' => $productsBase->pluck('m2_sku'),
                                'condition_type' => 'eq',
                            ],
                        ],
                    ],
                ],
                'pageSize' => $productsBase->count()
            ]
        ];

        $stocks = (array) $helper->getStocks($params);
        $products = $this->convertProducts($productsBase);
        foreach ($stocks['items'] as $item) {
            $item = (array) $item;
            if (isset($products[$item['sku']])) {
                $current = $products[$item['sku']];
                if ($current->stocks()->exists()) {
                    $stockCurrent = $current->stocks()->first();
                    if ($stockCurrent->quantity !== $item['quantity']) {
                        $stockCurrent->update([
                            'quantity' => $item['quantity'],
                            'is_in_stock' => $item['status'],
                            'sync_biso_digital' => true,
                            'stock_logs' => array_merge($stockCurrent->stock_logs ?? [], [[
                                'changed_by' => 'system',
                                'old_quantity' => $stockCurrent->quantity,
                                'new_quantity' => $item['quantity'],
                                'reason' => 'import',
                                'timestamp' => now()->toISOString(),
                            ]])
                        ]);
                    }
                    continue;
                }
                $current->stocks()->create([
                    'quantity' => $item['quantity'],
                    'is_in_stock' => $item['status'],
                    'sync_biso_digital' => true,
                    'stock_logs' => [
                        [
                            'changed_by' => 'system',
                            'old_quantity' => '-',
                            'new_quantity' => $item['quantity'],
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
