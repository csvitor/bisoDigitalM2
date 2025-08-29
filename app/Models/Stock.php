<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $table = 'stocks';
    protected $fillable = [
        'product_id',
        'quantity',
        'is_in_stock',
        'sync_biso_digital',
        'stock_logs',
        'is_synced'
    ];

    protected $casts = [
        'stock_logs' => 'array',
        'sync_biso_digital' => 'boolean',
        'is_synced' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
};
