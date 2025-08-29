<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $fillable = [
        'name',
        'm2_id',
        'biso_id',
        'biso_sku',
        'm2_sku',
        'm2_data',
        'request_data',
        'response_data',
        'is_synced',
        'log',
    ];

    protected $casts = [
        'm2_data' => 'array',
        'request_data' => 'array',
        'response_data' => 'array',
        'is_synced' => 'boolean',
    ];

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }
}
