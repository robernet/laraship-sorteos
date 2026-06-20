<?php

namespace Corals\Modules\Sorteos\Transformers;

use Corals\Foundation\Transformers\FractalPresenter;

class ColaboradorPresenter extends FractalPresenter
{
    public function getTransformer($extras = [])
    {
        return new ColaboradorTransformer($extras);
    }
}
