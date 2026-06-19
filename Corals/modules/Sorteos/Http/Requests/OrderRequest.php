<?php

namespace Corals\Modules\Sorteos\Http\Requests;

use Corals\Foundation\Http\Requests\BaseRequest;
use Corals\Modules\Sorteos\Enums\PaymentMethod;
use Corals\Modules\Sorteos\Models\Order;

class OrderRequest extends BaseRequest
{
    public function authorize(): bool
    {
        $this->setModel(Order::class);
        return $this->isAuthorized();
    }

    public function rules(): array
    {
        $rules = parent::rules();

        if ($this->isStore() && $this->route()?->getName() === 'orders.store') {
            $rules['sorteo_id']      = 'required|integer|exists:sorteos_sorteos,id';
            $rules['asignado_id']    = 'nullable|integer|exists:sorteos_carteras_asignadas,id';
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

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        if ($this->route()?->getName() !== 'orders.store') {
            return;
        }

        $validator->after(function ($v) {
            $hasBoletos = !empty($this->boleto_ids)
                || filled($this->boleto_ids_text)
                || !empty($this->cartera_ids)
                || filled($this->cartera_codes_text);

            if (!$hasBoletos) {
                $v->errors()->add('boleto_ids', 'Debe seleccionar al menos una cartera y un boleto.');
            }
        });
    }
}
