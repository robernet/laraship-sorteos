<?php

namespace Corals\Modules\ClubPago\Transformers;

use Corals\Foundation\Transformers\FractalPresenter;

class ClubPagoReferencePresenter extends FractalPresenter
{

    /**
     * @return ClubPagoReferenceTransformer
     */
    public function getTransformer($extras = [])
    {
        return new ClubPagoReferenceTransformer($extras);
    }
}
