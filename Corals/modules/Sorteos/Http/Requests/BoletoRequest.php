<?php

namespace Corals\Modules\Sorteos\Http\Requests;

use Corals\Foundation\Http\Requests\BaseRequest;
use Corals\Modules\Sorteos\Models\Boleto;

class BoletoRequest extends BaseRequest
{
    public function authorize(): bool
    {
        $this->setModel(Boleto::class);
        return $this->isAuthorized();
    }

    public function rules(): array
    {
        return parent::rules();
    }
}
