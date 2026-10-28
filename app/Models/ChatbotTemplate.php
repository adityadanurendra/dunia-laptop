<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'trigger_keywords',
        'response_type',
        'static_response',
        'dynamic_handler',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}

