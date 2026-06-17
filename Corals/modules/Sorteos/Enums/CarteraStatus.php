<?php

namespace Corals\Modules\Sorteos\Enums;

enum CarteraStatus: string
{
    case Available = 'available';
    case Partial   = 'partial';
    case Sold      = 'sold';

    public function label(): string
    {
        return match($this) {
            self::Available => 'Disponible',
            self::Partial   => 'Parcial',
            self::Sold      => 'Vendida',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Available => 'badge-success',
            self::Partial   => 'badge-warning',
            self::Sold      => 'badge-danger',
        };
    }
}
