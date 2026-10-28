<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case SETTLEMENT = 'settlement';
    case CAPTURE = 'capture';
    case DENY = 'deny';
    case EXPIRE = 'expire';
    case CANCEL = 'cancel';
    case REFUND = 'refund';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Pembayaran',
            self::SETTLEMENT => 'Berhasil (Settlement)',
            self::CAPTURE => 'Capture',
            self::DENY => 'Ditolak',
            self::EXPIRE => 'Kedaluwarsa',
            self::CANCEL => 'Dibatalkan',
            self::REFUND => 'Dikembalikan (Refund)',
        };
    }
}

