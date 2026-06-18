<?php

namespace Corals\Modules\ClubPago\Models;

use Corals\Foundation\Models\BaseModel;
use Corals\Foundation\Transformers\PresentableTrait;
use Corals\Modules\Sorteos\Models\Order;
use Corals\User\Models\User;
use Spatie\Activitylog\Traits\LogsActivity;

class ClubPagoReference extends BaseModel
{
    use PresentableTrait, LogsActivity;

    protected $table = 'clubpago_references';

    public $config = 'clubpago.models.clubpago_reference';

    protected static $logAttributes = ['status', 'amount'];

    protected $guarded = ['id'];

    public function scopeMyReferences($query)
    {
        return $query->where('user_id', user()->id);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
