<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'magento_code',
        'biso_payment_method',
        'biso_forms_of_payment',
        'max_installments',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_installments' => 'integer',
    ];

    /**
     * Busca um método de pagamento pelo código do Magento
     */
    public static function findByMagentoCode(string $magentoCode): ?self
    {
        return self::where('magento_code', $magentoCode)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Scope para métodos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}