<?php

namespace App\Helpers;

use Carbon\Carbon;

class CronHelper
{
    /**
     * Calcula a próxima execução baseada na expressão cron
     */
    public static function getNextExecution(string $cronExpression): Carbon
    {
        try {
            // Para simplicidade, vamos usar lógica baseada nas expressões mais comuns
            return self::calculateNextExecution($cronExpression);
        } catch (\Exception $e) {
            // Se der erro, retorna 1 minuto a partir de agora
            return Carbon::now()->addMinute();
        }
    }

    private static function calculateNextExecution(string $cronExpression): Carbon
    {
        $now = Carbon::now();
        
        // Mapear as expressões mais comuns
        switch ($cronExpression) {
            case '* * * * *':
                return $now->addMinute();
            case '*/2 * * * *':
                return $now->addMinutes(2);
            case '*/3 * * * *':
                return $now->addMinutes(3);
            case '*/5 * * * *':
                return $now->addMinutes(5);
            case '*/10 * * * *':
                return $now->addMinutes(10);
            case '*/15 * * * *':
                return $now->addMinutes(15);
            case '*/30 * * * *':
                return $now->addMinutes(30);
            case '0 * * * *':
                return $now->addHour();
            case '0 */2 * * *':
                return $now->addHours(2);
            case '0 */6 * * *':
                return $now->addHours(6);
            case '0 */12 * * *':
                return $now->addHours(12);
            case '0 0 * * *':
                return $now->addDay();
            default:
                // Para expressões não mapeadas, assume 1 minuto
                return $now->addMinute();
        }
    }

    /**
     * Valida se uma expressão cron é válida
     */
    public static function isValidCron(string $cronExpression): bool
    {
        // Regex básica para validar cron expressions
        $pattern = '/^(\*|([0-5]?\d))(\/\d+)?\s+(\*|([0-1]?\d|2[0-3]))(\/\d+)?\s+(\*|([0-2]?\d|3[0-1]))(\/\d+)?\s+(\*|([0]?\d|1[0-2]))(\/\d+)?\s+(\*|[0-6])(\/\d+)?$/';
        return preg_match($pattern, $cronExpression);
    }

    /**
     * Converte expressão cron em descrição legível
     */
    public static function getCronDescription(string $cronExpression): string
    {
        $descriptions = [
            '* * * * *' => 'A cada minuto',
            '*/2 * * * *' => 'A cada 2 minutos',
            '*/3 * * * *' => 'A cada 3 minutos',
            '*/5 * * * *' => 'A cada 5 minutos',
            '*/10 * * * *' => 'A cada 10 minutos',
            '*/15 * * * *' => 'A cada 15 minutos',
            '*/30 * * * *' => 'A cada 30 minutos',
            '0 * * * *' => 'A cada hora',
            '0 */2 * * *' => 'A cada 2 horas',
            '0 */6 * * *' => 'A cada 6 horas',
            '0 */12 * * *' => 'A cada 12 horas',
            '0 0 * * *' => 'Diariamente à meia-noite',
        ];

        return $descriptions[$cronExpression] ?? $cronExpression;
    }
}
