<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    protected $fillable = [
        'id_categories',
        'code',
        'name',
        'description',
        'unit_price',
        'higher_price',
        'stock',
        'expiration_date',
        'promotion_discount',
        'state',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'id_categories');
    }
}
