<?php

namespace Corals\Modules\Sorteos\Http\Requests;

use Corals\Foundation\Http\Requests\BaseRequest;

class BoletoRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return $this->isAuthorized();
    }

    public function rules(): array
    {
        return parent::rules();
    }
}
