<?php

namespace App\Console\Commands;

use App\Models\Stock;
use App\Helpers\HelperBisoDigital;
use Illuminate\Console\Command;

class ExportStocksToBisoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export-stocks-to-biso-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export stocks to Biso';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $limit = env('BISO_COUNT_SEND_STOCK', 10);
        $stocks = Stock::where('sync_biso_digital', true)
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();

        foreach ($stocks as $stock) {
            $product = $stock->product;

            if (!$product->biso_id || !$product->biso_sku) {
                $this->error("Produto {$product->m2_sku} não está sincronizado com o Biso. Pule o estoque.");
                continue;
            }

            $productId = $product->biso_id;
            $productSkuId = $product->biso_sku;
            $stockData = $this->prepareStockPayload($stock);

            $response = $this->sendStockToBiso($stock->is_synced, $productId, $productSkuId, $stockData);

            if ($response[0]) {
                $log = array_merge(
                    $stock->stock_logs ?? [],
                    [[
                        'reason' => 'export',
                        'timestamp' => now()->toISOString(),
                        'changed_by' => 'system',
                        'request' => $stockData,
                        'response' => $response,
                    ]]
                );
                $this->info("Estoque do produto {$productSkuId} enviado com sucesso!");
                $stock->update(['sync_biso_digital' => false, 'stock_logs' => $log]);
                if (!$stock->is_synced) {
                    $stock->update(['is_synced' => true]);
                }
                continue;
            }

            $log = array_merge($stock->stock_logs ?? [], [[
                'reason' => 'export',
                'timestamp' => now()->toISOString(),
                'changed_by' => 'system',
                'request' => $stockData,
                'response' => $response
            ]]);

            if ($stock->is_synced && $response[2] == 404) {
                if($response[1]['errors'][0] == 'Stock not found') {
                    $stock->update(['is_synced' => false]);
                    $log = array_merge($log, [[
                        'reason' => 'update to create',
                        'timestamp' => now()->toISOString(),
                        'changed_by' => 'system',
                        'note' => 'O estoque foi marcado para criação, pois não foi encontrado no Biso.'
                    ]]);
                }
            }

            $stock->update(['sync_biso_digital' => false, 'stock_logs' => $log]);
            $this->error("Erro ao enviar estoque do produto {$productSkuId}: " . json_encode($response[1]));
        }
    }

    /**
     * Prepara o payload do estoque para o Biso Digital
     */
    private function prepareStockPayload($stock)
    {
        return [
            'productStockQuantity' => $stock->quantity,
            'productStockReservedQuantity' => 0,
            'storeId' => HelperBisoDigital::API_STORE_ID
        ];
    }

    /**
     * Envia o estoque para o Biso Digital
     */
    private function sendStockToBiso($is_synced, $productId, $productSkuId, array $stockData)
    {
        $httpClient = HelperBisoDigital::init();
        $response = match ($is_synced) {
            true => $httpClient->updateStockToBiso($productId, $productSkuId, $stockData),
            false => $httpClient->createStockToBiso($productId, $productSkuId, $stockData)
        };
        if ($response->successful()) {
            return [true, $response->json(), $response->status()];
        }
        return [false, $response->json() ?: $response->body(), $response->status()];
    }
}
