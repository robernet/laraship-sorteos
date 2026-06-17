<?php

namespace Corals\Modules\Sorteos\Models;

use Corals\Foundation\Models\BaseModel;
use Spatie\Activitylog\Traits\LogsActivity;

class OrderItem extends BaseModel
{
    use LogsActivity;

    protected $table = 'sorteos_order_items';

    protected $guarded = ['id'];

    protected $casts = [
        'properties' => 'json',
        'price'      => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function boleto()
    {
        return $this->belongsTo(Boleto::class);
    }
}
