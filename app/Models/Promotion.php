<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Promotion extends Model
{
    protected $fillable = [
        'id_products',
        'name_promotion',
        'image',
        'price',
        'state'
    ];

    public function products(): BelongsTo
    {
        return $this->belongsTo(Products::class, 'id_products');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Products::class, 'id_products');
    }

    public function getImageAttribute(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (str_starts_with($value, '/storage/')) {
            return $value;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            $path = parse_url($value, PHP_URL_PATH) ?? '';
            if ($path && str_starts_with($path, '/storage/')) {
                return $path;
            }

            return $value;
        }

        $relative = ltrim(str_replace('\\', '/', $value), '/');

        if (str_starts_with($relative, 'public/')) {
            $relative = ltrim(substr($relative, 7), '/');
        }

        if (str_starts_with($relative, 'storage/')) {
            return '/' . $relative;
        }

        return '/storage/' . $relative;
    }
}
