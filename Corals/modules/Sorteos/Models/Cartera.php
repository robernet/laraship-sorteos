<?php

namespace Corals\Modules\Sorteos\Models;

use Corals\Foundation\Models\BaseModel;
use Corals\Foundation\Transformers\PresentableTrait;
use Corals\Modules\Sorteos\Enums\CarteraStatus;
use Spatie\Activitylog\Traits\LogsActivity;

class Cartera extends BaseModel
{
    use PresentableTrait;
    use LogsActivity;

    public $config = 'sorteos.models.cartera';

    protected $table = 'sorteos_carteras';

    protected $guarded = ['id'];

    protected $casts = [
        'properties'     => 'json',
        'status'         => CarteraStatus::class,
        'physical_start' => 'integer',
        'physical_end'   => 'integer',
        'digital_start'  => 'integer',
        'digital_end'    => 'integer',
    ];

    public function sorteo()
    {
        return $this->belongsTo(Sorteo::class);
    }

    public function boletos()
    {
        return $this->hasMany(Boleto::class);
    }

    public function getIdentifier($key = null)
    {
        return $this->code;
    }
}
