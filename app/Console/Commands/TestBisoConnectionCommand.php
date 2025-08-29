<?php

namespace App\Console\Commands;

use App\Helpers\HelperBisoDigital;
use Illuminate\Console\Command;

class TestBisoConnectionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:biso-connection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa a conectividade com a API do Biso Digital';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Testando conectividade com a API do Biso Digital...');
        
        // Verifica se as credenciais estão configuradas
        $clientId = env('BISO_CLIENT_ID');
        $apiKey = env('BISO_API_KEY');
        
        if (empty($clientId) || empty($apiKey)) {
            $this->error('❌ Credenciais do Biso não configuradas!');
            $this->line('Configure BISO_CLIENT_ID e BISO_API_KEY no arquivo .env');
            return 1;
        }
        
        $this->info("📍 Client ID: " . substr($clientId, 0, 8) . '***');
        
        // Testa a conexão
        $bisoHelper = HelperBisoDigital::init();
        $result = $bisoHelper->testConnection();
        
        if ($result['success']) {
            $this->info('✅ Conexão com Biso Digital estabelecida com sucesso!');
            $this->line("📡 URL: {$result['url']}");
            $this->line("📊 Status: {$result['status']}");
            
            if (isset($result['response']) && is_array($result['response'])) {
                $this->line("📝 Resposta: " . json_encode($result['response'], JSON_PRETTY_PRINT));
            }
        } else {
            $this->error('❌ Falha na conexão com Biso Digital!');
            $this->line("📡 URL: {$result['url']}");
            $this->line("📊 Status: {$result['status']}");
            $this->line("📝 Erro: {$result['response']}");
            return 1;
        }
        
        return 0;
    }
}
