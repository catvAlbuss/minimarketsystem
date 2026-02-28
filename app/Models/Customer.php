<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
        protected $fillable = [
                'dni',
                'name',
                'last_name',
                'birthday',
                'email',
                'phone',
                'address',
                'score',
                'state',
        ];
}
