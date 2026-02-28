<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{
    protected $fillable = [
        'id_sales',
        'id_products',
        'quantity',
        'discount',
        'sub_total',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'id_sales');
    }
    public function product()
    {
        return $this->belongsTo(Products::class, 'id_products');
    }
}
