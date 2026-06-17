<?php

namespace Corals\Modules\Sorteos\Facades;

use Illuminate\Support\Facades\Facade;

class Sorteos extends Facade
{
    /**
     * @return mixed
     */
    protected static function getFacadeAccessor()
    {
        return \Corals\Modules\Sorteos\Classes\Sorteos::class;
    }
}
