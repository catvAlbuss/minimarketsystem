<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'id_customers');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_users');
    }

    public function saleDetails(): HasMany
    {
        return $this->hasMany(SaleDetail::class, 'id_sales');
    }
}
