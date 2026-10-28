<?php

namespace App\Enums;

enum ProductCondition: string
{
    case NEW = 'new';
    case USED = 'used';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'Baru',
            self::USED => 'Bekas',
        };
    }
}

