<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'id_customers',
        'id_users',
        'voucher_number',
        'igv',
        'total',
        'payment_method',
        'voucher',
        'document',
        'date_time',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_customers');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'id_users');
    }
}
