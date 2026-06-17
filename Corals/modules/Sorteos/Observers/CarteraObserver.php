<?php

namespace Corals\Modules\Sorteos\Observers;

use Corals\Modules\Sorteos\Models\Cartera;
use Corals\Modules\Sorteos\Services\CarteraService;

class CarteraObserver
{
    public function creating(Cartera $cartera): void
    {
        $tickets = CarteraService::TICKETS_PER_WALLET;
        $cartera->physical_end = $cartera->physical_start + $tickets - 1;
        $cartera->digital_end  = $cartera->digital_start  + $tickets - 1;
    }
}
