<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuyDetail extends Model
{
    protected $fillable = [
        'id_buys',
        'id_products',
        'quantity',
        'unit_price',
        'sub_total',
    ];

    public function buy()
    {
        return $this->belongsTo(Buy::class, 'id_buys');
    }
    public function product()
    {
        return $this->belongsTo(Products::class, 'id_products');
    }
}
