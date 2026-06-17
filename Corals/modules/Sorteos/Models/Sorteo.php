<?php

namespace Corals\Modules\Sorteos\Models;

use Corals\Foundation\Models\BaseModel;
use Corals\Foundation\Transformers\PresentableTrait;
use Corals\Modules\Sorteos\Enums\SorteoStatus;
use Spatie\Activitylog\Traits\LogsActivity;

class Sorteo extends BaseModel
{
    use PresentableTrait;
    use LogsActivity;

    /**
     *  Model configuration.
     * @var string
     */
    public $config = 'sorteos.models.sorteo';

    protected $table = 'sorteos_sorteos';

    protected $guarded = ['id'];

    protected $casts = [
        'properties'   => 'json',
        'ticket_price' => 'decimal:2',
        'starts_at'    => 'date',
        'ends_at'      => 'date',
        'draw_date'    => 'date',
        'status'       => SorteoStatus::class,
        'is_public'    => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', SorteoStatus::Active);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeAvailable($query)
    {
        return $query->active()->public();
    }

    public function carteras()
    {
        return $this->hasMany(Cartera::class);
    }

    public function boletos()
    {
        return $this->hasMany(Boleto::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
