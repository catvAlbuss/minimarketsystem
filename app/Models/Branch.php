<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; //
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Branch extends Model
{
    use HasFactory; //

    protected $fillable = [
        "id_users",     
        "name",
        "address",
        "opening_time",
        "closing_time",
        "state",
    ];


    public function user(): BelongsTo
    {
        // El segundo parámetro indica la llave foránea personalizada
        return $this->belongsTo(User::class, 'id_users');
    }
}