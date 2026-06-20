<?php

namespace Corals\Modules\Sorteos\Enums;

enum ColaboradorStatus: string
{
    case Active   = 'active';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match($this) {
            self::Active   => 'Activo',
            self::Inactive => 'Inactivo',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Active   => 'badge-success',
            self::Inactive => 'badge-secondary',
        };
    }
}
