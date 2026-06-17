<?php

namespace Corals\Modules\Sorteos\Enums;

enum BoletoStatus: string
{
    case Available = 'available';
    case Reserved  = 'reserved';
    case Sold      = 'sold';

    public function label(): string
    {
        return match($this) {
            self::Available => 'Disponible',
            self::Reserved  => 'Reservado',
            self::Sold      => 'Vendido',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Available => 'badge-success',
            self::Reserved  => 'badge-warning',
            self::Sold      => 'badge-danger',
        };
    }
}
