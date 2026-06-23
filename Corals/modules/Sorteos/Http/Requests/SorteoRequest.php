<?php

namespace Corals\Modules\Sorteos\Http\Requests;

use Corals\Foundation\Http\Requests\BaseRequest;
use Corals\Modules\Sorteos\Enums\SorteoStatus;
use Corals\Modules\Sorteos\Models\Sorteo;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class SorteoRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->setModel(Sorteo::class);

        return $this->isAuthorized();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->setModel(Sorteo::class);
        $rules = parent::rules();

        if ($this->isUpdate() || $this->isStore()) {
            $rules = array_merge($rules, [
                'name'              => ['required', 'string', 'max:191'],
                'description'       => ['nullable', 'string'],
                'prize_description' => ['nullable', 'string'],
                'cover_image'       => ['nullable', 'string'],
                'ticket_price'      => ['required', 'numeric', 'min:0'],
                'tiraje'            => ['nullable', 'integer', 'min:1'],
                'starts_at'         => ['nullable', 'date'],
                'ends_at'           => ['nullable', 'date', 'after_or_equal:starts_at'],
                'draw_date'         => ['nullable', 'date', 'after_or_equal:ends_at'],
                'status'            => ['required', new Enum(SorteoStatus::class)],
                'is_public'         => ['nullable', 'boolean'],
            ]);
        }

        if ($this->isStore()) {
            $rules = array_merge($rules, [
                'slug' => ['required', 'string', 'max:191', 'unique:sorteos_sorteos,slug'],
            ]);
        }

        if ($this->isUpdate()) {
            $sorteo = $this->route('sorteo');

            $rules = array_merge($rules, [
                'slug' => [
                    'required', 'string', 'max:191',
                    Rule::unique('sorteos_sorteos', 'slug')->ignore($sorteo->id),
                ],
            ]);
        }

        return $rules;
    }
}
