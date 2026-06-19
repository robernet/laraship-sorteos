<?php

namespace Corals\Modules\Sorteos\Transformers;

use Corals\Foundation\Transformers\FractalPresenter;

class AsignadoPresenter extends FractalPresenter
{
    public function getTransformer($extras = [])
    {
        return new AsignadoTransformer($extras);
    }
}
