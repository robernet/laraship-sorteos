<?php

namespace Corals\Modules\Sorteos\Enums;

enum SorteoStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Finished = 'finished';

    public function label(): string
    {
        return match($this) {
            self::Active   => 'Activo',
            self::Paused   => 'Pausado',
            self::Finished => 'Finalizado',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Active   => 'badge-success',
            self::Paused   => 'badge-warning',
            self::Finished => 'badge-secondary',
        };
    }
}
