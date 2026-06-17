<?php

namespace Corals\Modules\Sorteos\Transformers;

use Corals\Foundation\Transformers\FractalPresenter;

class OrderPresenter extends FractalPresenter
{
    public function getTransformer($extras = [])
    {
        return new OrderTransformer($extras);
    }
}
