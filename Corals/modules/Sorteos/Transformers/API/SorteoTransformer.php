<?php

namespace Corals\Modules\Sorteos\Transformers\API;

use Corals\Foundation\Transformers\APIBaseTransformer;
use Corals\Modules\Sorteos\Models\Sorteo;

class SorteoTransformer extends APIBaseTransformer
{
    /**
     * @param Sorteo $sorteo
     * @return array
     * @throws \Throwable
     */
    public function transform(Sorteo $sorteo)
    {
        $transformedArray = [
            'id' => $sorteo->id,
            'name' => $sorteo->name,
            'created_at' => format_date($sorteo->created_at),
            'updated_at' => format_date($sorteo->updated_at),
        ];

        return parent::transformResponse($transformedArray);
    }
}
