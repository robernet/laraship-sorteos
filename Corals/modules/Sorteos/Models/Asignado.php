<?php

namespace Corals\Modules\Sorteos\Models;

use Corals\Foundation\Models\BaseModel;
use Corals\Foundation\Transformers\PresentableTrait;
use Corals\Modules\Sorteos\Enums\AsignadoStatus;
use Spatie\Activitylog\Traits\LogsActivity;

class Asignado extends BaseModel
{
    use PresentableTrait;
    use LogsActivity;

    public $config = 'sorteos.models.asignado';

    protected $table = 'sorteos_carteras_asignadas';

    protected $guarded = ['id'];

    protected $casts = [
        'properties' => 'json',
        'status'     => AsignadoStatus::class,
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
