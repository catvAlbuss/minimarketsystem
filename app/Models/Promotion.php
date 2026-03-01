<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    //


    protected $fillable = [
        'id_products',
        'name_promotion',
        'price',
        'state'
    ];

    public function products()
    {
        return $this->belongsTo(Products::class, 'id_products');
    }
}
