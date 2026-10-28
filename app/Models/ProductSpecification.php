<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSpecification extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'processor',
        'ram_gb',
        'ram_type',
        'storage_capacity',
        'storage_type',
        'gpu',
        'screen_size',
        'screen_resolution',
        'physical_condition',
        'battery_condition',
        'operating_system',
        'warranty',
        'additional_specs',
    ];

    protected function casts(): array
    {
        return [
            'ram_gb' => 'integer',
            'screen_size' => 'decimal:1',
            'additional_specs' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

