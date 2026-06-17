<?php

namespace Corals\Modules\Sorteos\Transformers\API;

use Corals\Foundation\Transformers\FractalPresenter;

class SorteoPresenter extends FractalPresenter
{
    /**
     * @return SorteoTransformer
     */
    public function getTransformer()
    {
        return new SorteoTransformer();
    }
}
