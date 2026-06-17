<?php

namespace Corals\Modules\Sorteos\Models;

use Corals\Foundation\Models\BaseModel;
use Corals\Foundation\Transformers\PresentableTrait;
use Corals\Modules\Sorteos\Enums\BoletoStatus;
use Spatie\Activitylog\Traits\LogsActivity;

class Boleto extends BaseModel
{
    use PresentableTrait;
    use LogsActivity;

    public $config = 'sorteos.models.boleto';

    protected $table = 'sorteos_boletos';

    protected $guarded = ['id'];

    protected $casts = [
        'properties'      => 'json',
        'status'          => BoletoStatus::class,
        'physical_number' => 'integer',
        'digital_number'  => 'integer',
    ];

    public function sorteo()
    {
        return $this->belongsTo(Sorteo::class);
    }

    public function cartera()
    {
        return $this->belongsTo(Cartera::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getIdentifier($key = null)
    {
        return '#' . $this->digital_number;
    }
}
