<?php

namespace Corals\Modules\Sorteos\Models;

use Corals\Foundation\Models\BaseModel;
use Corals\Foundation\Transformers\PresentableTrait;
use Corals\Modules\Sorteos\Enums\ColaboradorStatus;
use Spatie\Activitylog\Traits\LogsActivity;

class Colaborador extends BaseModel
{
    use PresentableTrait;
    use LogsActivity;

    public $config = 'sorteos.models.colaborador';

    protected $table = 'sorteos_colaboradores';

    protected $guarded = ['id'];

    protected $casts = [
        'properties' => 'json',
        'status'     => ColaboradorStatus::class,
    ];

    public function carteras()
    {
        return $this->hasMany(Cartera::class);
    }

    public function getIdentifier($key = null): string
    {
        return $this->name;
    }
}
