<?php

namespace Corals\Modules\Sorteos\Policies;

use Corals\Foundation\Policies\BasePolicy;
use Corals\Modules\Sorteos\Models\Boleto;
use Corals\User\Models\User;

class BoletoPolicy extends BasePolicy
{
    protected $administrationPermission = 'Administrations::admin.sorteos';

    public function view(User $user): bool
    {
        return $user->can('Sorteos::boleto.view');
    }

    public function create(User $user): bool
    {
        return $user->can('Sorteos::boleto.create');
    }

    public function update(User $user, Boleto $boleto): bool
    {
        return $user->can('Sorteos::boleto.update');
    }

    public function destroy(User $user, Boleto $boleto): bool
    {
        return $user->can('Sorteos::boleto.delete');
    }
}
