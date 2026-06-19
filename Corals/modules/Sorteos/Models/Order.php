<?php

namespace Corals\Modules\Sorteos\Models;

use Corals\Foundation\Models\BaseModel;
use Corals\Foundation\Transformers\PresentableTrait;
use Corals\Modules\ClubPago\Models\ClubPagoReference;
use Corals\Modules\Sorteos\Enums\OrderStatus;
use Corals\Modules\Sorteos\Enums\PaymentMethod;
use Spatie\Activitylog\Traits\LogsActivity;

class Order extends BaseModel
{
    use PresentableTrait;
    use LogsActivity;

    public $config = 'sorteos.models.order';

    protected $table = 'sorteos_orders';

    protected $guarded = ['id'];

    protected $casts = [
        'properties'       => 'json',
        'status'           => OrderStatus::class,
        'payment_method'   => PaymentMethod::class,
        'total_amount'     => 'decimal:2',
        'payment_reference' => 'string',
    ];

    public function sorteo()
    {
        return $this->belongsTo(Sorteo::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function boletos()
    {
        return $this->hasManyThrough(Boleto::class, OrderItem::class, 'order_id', 'id', 'id', 'boleto_id');
    }

    public function asignado()
    {
        return $this->belongsTo(\Corals\Modules\Sorteos\Models\Asignado::class);
    }

    public function clubPagoReference()
    {
        return $this->hasOne(ClubPagoReference::class);
    }

    public function getIdentifier($key = null): string
    {
        return '#' . $this->id . ' — ' . $this->buyer_name;
    }

    public function isPending(): bool
    {
        return $this->status === OrderStatus::Pending;
    }

    public function isConfirmed(): bool
    {
        return $this->status === OrderStatus::Confirmed;
    }

    public function isCancelled(): bool
    {
        return $this->status === OrderStatus::Cancelled;
    }
}
