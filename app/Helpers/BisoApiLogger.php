<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class BisoApiLogger
{
    /**
     * Loga uma chamada para a API Biso.
     *
     * @param string $endpoint
     * @param array $request
     * @param array $headers
     * @param mixed $response
     * @return void
     */
    public static function log($endpoint, $request, $headers, $response)
    {
        if(!env('LOGS_BISO_API')) {
            return;
        }

        Log::channel('daily')->info('[BISO API CALL]', [
            'endpoint' => $endpoint,
            'request' => $request,
            'headers' => $headers,
            'response' => $response,
        ]);
    }
}
