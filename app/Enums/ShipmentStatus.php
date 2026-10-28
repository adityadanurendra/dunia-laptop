<?php

namespace App\Enums;

enum ShipmentStatus: string
{
    case PENDING = 'pending';
    case PICKED_UP = 'picked_up';
    case IN_TRANSIT = 'in_transit';
    case DELIVERED = 'delivered';
    case RETURNED = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Penjemputan',
            self::PICKED_UP => 'Sudah Diambil Kurir',
            self::IN_TRANSIT => 'Dalam Pengiriman',
            self::DELIVERED => 'Terkirim',
            self::RETURNED => 'Dikembalikan ke Penjual',
        };
    }
}

