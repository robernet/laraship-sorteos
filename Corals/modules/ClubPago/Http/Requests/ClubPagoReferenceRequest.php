<?php

namespace Corals\Modules\ClubPago\Http\Requests;

use Corals\Foundation\Http\Requests\BaseRequest;

class ClubPagoReferenceRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return (bool) user();
    }

    public function rules(): array
    {
        return parent::rules();
    }
}
