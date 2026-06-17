<?php

namespace Corals\Modules\Sorteos\Transformers;

use Corals\Foundation\Transformers\FractalPresenter;

class SorteoPresenter extends FractalPresenter
{
    /**
     * @return SorteoTransformer
     */
    public function getTransformer($extras = [])
    {
        return new SorteoTransformer($extras);
    }
}
