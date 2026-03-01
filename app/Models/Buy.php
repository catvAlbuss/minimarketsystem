<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Buy extends Model
{
    protected $fillable = [
        'id_provider',
        'id_users',
        'voucher_number',
        'total',
        'payment_method',
        'payment_status',
        'date_time',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'id_provider');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'id_users');
    }
}
