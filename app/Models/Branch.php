<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_users',
        'name',
        'address',
        'opening_time',
        'closing_time',
        'state',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_users');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'id_branches');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'branch_id');
    }
}
