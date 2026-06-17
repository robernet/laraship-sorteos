<?php

namespace Corals\Modules\Sorteos\Enums;

enum PaymentMethod: string
{
    case Cash      = 'cash';
    case Transfer  = 'transfer';
    case ClubPago  = 'clubpago';

    public function label(): string
    {
        return match($this) {
            self::Cash     => 'Efectivo',
            self::Transfer => 'Transferencia',
            self::ClubPago => 'ClubPago',
        };
    }
}
