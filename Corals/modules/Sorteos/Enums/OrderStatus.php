<?php

namespace Corals\Modules\Sorteos\Enums;

enum OrderStatus: string
{
    case Pending   = 'pending';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Pending   => 'Pendiente',
            self::Confirmed => 'Confirmada',
            self::Cancelled => 'Cancelada',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Pending   => 'badge-warning',
            self::Confirmed => 'badge-success',
            self::Cancelled => 'badge-danger',
        };
    }
}
