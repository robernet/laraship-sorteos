<?php

namespace Corals\Modules\Sorteos\Http\Requests;

use Corals\Foundation\Http\Requests\BaseRequest;
use Corals\Modules\Sorteos\Enums\PaymentMethod;

class OrderRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return $this->isAuthorized();
    }

    public function rules(): array
    {
        $rules = parent::rules();

        if ($this->isStore()) {
            $rules['sorteo_id']      = 'required|integer|exists:sorteos_sorteos,id';
            $rules['buyer_name']     = 'required|string|max:255';
            $rules['buyer_email']    = 'required|email|max:255';
            $rules['buyer_phone']    = 'required|string|max:50';
            $rules['payment_method'] = 'required|string|in:' . implode(',', array_column(PaymentMethod::cases(), 'value'));
            $rules['boleto_ids']          = 'nullable|array';
            $rules['boleto_ids.*']        = 'integer|exists:sorteos_boletos,id';
            $rules['cartera_ids']         = 'nullable|array';
            $rules['cartera_ids.*']       = 'integer|exists:sorteos_carteras,id';
            $rules['boleto_ids_text']     = 'nullable|string|max:2000';
            $rules['cartera_codes_text']  = 'nullable|string|max:2000';
            $rules['notes']               = 'nullable|string|max:1000';
            $rules['buyer_city']          = 'nullable|string|max:100';
            $rules['buyer_state']         = 'nullable|string|max:100';
        }

        return $rules;
    }
}
