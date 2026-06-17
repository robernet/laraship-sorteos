<?php

namespace Corals\Modules\Sorteos\Policies;

use Corals\Foundation\Policies\BasePolicy;
use Corals\Modules\Sorteos\Models\Order;
use Corals\User\Models\User;

class OrderPolicy extends BasePolicy
{
    protected $administrationPermission = 'Administrations::admin.sorteos';

    public function view(User $user, Order $order): bool
    {
        return $user->can('Sorteos::order.view');
    }

    public function create(User $user): bool
    {
        return $user->can('Sorteos::order.create');
    }

    public function update(User $user, Order $order): bool
    {
        return $user->can('Sorteos::order.update');
    }

    public function destroy(User $user, Order $order): bool
    {
        return $user->can('Sorteos::order.delete');
    }
}
