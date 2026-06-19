<?php

namespace Corals\Modules\Sorteos\Policies;

use Corals\Foundation\Policies\BasePolicy;
use Corals\Modules\Sorteos\Models\Asignado;
use Corals\User\Models\User;

class AsignadoPolicy extends BasePolicy
{
    protected $administrationPermission = 'Administrations::admin.sorteos';

    public function view(User $user): bool
    {
        return $user->can('Sorteos::asignado.view');
    }

    public function create(User $user): bool
    {
        return $user->can('Sorteos::asignado.create');
    }

    public function update(User $user, Asignado $asignado): bool
    {
        return $user->can('Sorteos::asignado.update');
    }

    public function destroy(User $user, Asignado $asignado): bool
    {
        return $user->can('Sorteos::asignado.delete');
    }
}
