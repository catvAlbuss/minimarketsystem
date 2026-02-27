<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    protected $fillable = [
        'id_products',
        'ruc',
        'company_name',
        'contact_person',
        'phone',
        'email',
        'address',
        'category',
        'description_products',
        'status'
    ];

    public function products(){
        return $this->belongsTo(Products::class, 'id_products');
    }

}
