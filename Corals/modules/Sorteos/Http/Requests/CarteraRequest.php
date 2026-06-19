<?php

namespace Corals\Modules\Sorteos\Http\Requests;

use Corals\Foundation\Http\Requests\BaseRequest;
use Corals\Modules\Sorteos\Models\Cartera;
use Illuminate\Validation\Rule;

class CarteraRequest extends BaseRequest
{
    public function authorize(): bool
    {
        $this->setModel(Cartera::class);
        return $this->isAuthorized();
    }

    public function rules(): array
    {
        $rules = parent::rules();

        if ($this->isStore()) {
            $rules['sorteo_id']      = 'required|integer|exists:sorteos_sorteos,id';
            $rules['code']           = [
                'required', 'string', 'max:20',
                Rule::unique('sorteos_carteras', 'code')->where('sorteo_id', $this->input('sorteo_id')),
            ];
            $rules['physical_start'] = 'required|integer|min:1';
            $rules['digital_start']  = 'required|integer|min:1';
        }

        if ($this->isUpdate()) {
            $cartera = $this->route('cartera');
            $rules['code'] = [
                'required', 'string', 'max:20',
                Rule::unique('sorteos_carteras', 'code')
                    ->where('sorteo_id', $cartera?->sorteo_id)
                    ->ignore($cartera?->id),
            ];
            $rules['asignado_id'] = 'nullable|integer|exists:sorteos_carteras_asignadas,id';
            $rules['status']      = 'nullable|string';
        }

        return $rules;
    }
}
