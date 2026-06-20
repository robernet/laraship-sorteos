<?php

namespace Corals\Modules\Sorteos\Policies;

use Corals\Foundation\Policies\BasePolicy;
use Corals\Modules\Sorteos\Models\Colaborador;
use Corals\User\Models\User;

class ColaboradorPolicy extends BasePolicy
{
    protected $administrationPermission = 'Administrations::admin.sorteos';

    public function view(User $user): bool
    {
        return $user->can('Sorteos::colaborador.view');
    }

    public function create(User $user): bool
    {
        return $user->can('Sorteos::colaborador.create');
    }

    public function update(User $user, Colaborador $colaborador): bool
    {
        return $user->can('Sorteos::colaborador.update');
    }

    public function destroy(User $user, Colaborador $colaborador): bool
    {
        return $user->can('Sorteos::colaborador.delete');
    }
}
