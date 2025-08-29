<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaginationMagento extends Model
{
    protected $table = 'pagination_magento';
    protected $fillable = [
        'page',
        'page_size',
        'sort_by',
    ];
}
