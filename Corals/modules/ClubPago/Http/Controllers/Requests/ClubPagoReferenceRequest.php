<?php

namespace Corals\Modules\ClubPago\Http\Requests;

use Corals\Foundation\Http\Requests\BaseRequest;
use Corals\Modules\ClubPago\Models\ClubPagoReference;

class ClubPagoReferenceRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->setModel(ClubPagoReference::class);

        return $this->isAuthorized();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->setModel(ClubPagoReference::class);
        $rules = parent::rules();

        if ($this->isUpdate() || $this->isStore()) {
            $rules = array_merge($rules, [
            ]);
        }

        if ($this->isStore()) {
            $rules = array_merge($rules, [
            ]);
        }

        if ($this->isUpdate()) {
            $clubpago_reference = $this->route('clubpago-reference');

            $rules = array_merge($rules, [
            ]);
        }

        return $rules;
    }
}
