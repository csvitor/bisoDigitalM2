<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'm2_id',
        'biso_id',
        'order_number',
        'm2_status',
        'm2_state',
        'total_amount',
        'currency',
        'order_date',
        'm2_data',
        'request_data',
        'response_data',
        'is_synced_to_biso',
        'sync_attempts',
        'last_sync_attempt',
        'is_paid',
        'is_paid_synced_to_biso',
        'log',
    ];

    protected $casts = [
        'm2_data' => 'array',
        'request_data' => 'array',
        'response_data' => 'array',
        'is_synced_to_biso' => 'boolean',
        'is_paid' => 'boolean',
        'is_paid_synced_to_biso' => 'boolean',
        'order_date' => 'datetime',
        'last_sync_attempt' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Reset sync attempts to allow resynchronization
     */
    public function resetSyncAttempts(): void
    {
        $this->update([
            'sync_attempts' => 0,
            'last_sync_attempt' => null,
            'is_synced_to_biso' => false,
            'log' => $this->log . "\n[" . now() . "] Tentativas de sincronização resetadas manualmente pelo usuário."
        ]);
    }

    /**
     * Check if order has reached maximum sync attempts
     */
    public function hasReachedMaxSyncAttempts(): bool
    {
        return $this->sync_attempts >= 3;
    }

    /**
     * Check if order can be resynced (not synced or max attempts reached)
     */
    public function canBeResynced(): bool
    {
        return !$this->is_synced_to_biso;
    }
}
