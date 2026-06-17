<?php

namespace Corals\Modules\Sorteos\Transformers;

use Corals\Foundation\Transformers\FractalPresenter;

class BoletoPresenter extends FractalPresenter
{
    public function getTransformer($extras = [])
    {
        return new BoletoTransformer($extras);
    }
}
