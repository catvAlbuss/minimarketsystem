<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Products extends Model
{

    protected $fillable = [
        'id_categories',
        'code',
        'name',
        'description',
        'image',
        'unit_price',
        'higher_price',
        'stock',
        'expiration_date',
        'promotion_discount',
        'state',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'id_categories');
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

    public function imagePathForDeletion(): ?string
    {
        $raw = $this->getRawOriginal('image');
        if (! $raw) {
            return null;
        }

        if (str_starts_with($raw, 'http://') || str_starts_with($raw, 'https://') || str_contains($raw, '/storage/')) {
            $path = parse_url($raw, PHP_URL_PATH) ?? '';
            return $path ? ltrim(str_replace('/storage/', '', $path), '/') : null;
        }

        return ltrim($raw, '/');
    }

}
