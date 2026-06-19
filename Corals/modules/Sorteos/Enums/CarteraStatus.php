<?php

namespace Corals\Modules\Sorteos\Enums;

enum CarteraStatus: string
{
    case Available = 'available';
    case Partial   = 'partial';
    case Sold      = 'sold';
    case Asignado  = 'asignado';
    case Entregado = 'entregado';

    public function label(): string
    {
        return match($this) {
            self::Available => 'Disponible',
            self::Partial   => 'Parcial',
            self::Sold      => 'Vendida',
            self::Asignado  => 'Asignado',
            self::Entregado => 'Entregado',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Available => 'badge-success',
            self::Partial   => 'badge-warning',
            self::Sold      => 'badge-danger',
            self::Asignado  => 'badge-info',
            self::Entregado => 'badge-dark',
        };
    }
}
