<?php

namespace Corals\Modules\Sorteos\Http\Requests;

use Corals\Foundation\Http\Requests\BaseRequest;

class ColaboradorRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return (bool) user();
    }

    public function rules(): array
    {
        $rules = parent::rules();

        if ($this->isStore() || $this->isUpdate()) {
            $rules['name']   = 'required|string|max:255';
            $rules['email']  = 'nullable|email|max:255';
            $rules['phone']  = 'nullable|string|max:50';
            $rules['type']   = 'nullable|in:persona,institucion';
            $rules['notes']  = 'nullable|string';
            $rules['status'] = 'nullable|string';
        }

        return $rules;
    }
}
