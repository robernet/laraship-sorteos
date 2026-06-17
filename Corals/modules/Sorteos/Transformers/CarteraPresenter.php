<?php

namespace Corals\Modules\Sorteos\Transformers;

use Corals\Foundation\Transformers\FractalPresenter;

class CarteraPresenter extends FractalPresenter
{
    public function getTransformer($extras = [])
    {
        return new CarteraTransformer($extras);
    }
}
