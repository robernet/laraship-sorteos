<?php

namespace Corals\Modules\Sorteos\Policies;

use Corals\Foundation\Policies\BasePolicy;
use Corals\Modules\Sorteos\Models\Cartera;
use Corals\User\Models\User;

class CarteraPolicy extends BasePolicy
{
    protected $administrationPermission = 'Administrations::admin.sorteos';

    public function view(User $user): bool
    {
        return $user->can('Sorteos::cartera.view');
    }

    public function create(User $user): bool
    {
        return $user->can('Sorteos::cartera.create');
    }

    public function update(User $user, Cartera $cartera): bool
    {
        return $user->can('Sorteos::cartera.update');
    }

    public function destroy(User $user, Cartera $cartera): bool
    {
        return $user->can('Sorteos::cartera.delete');
    }
}
