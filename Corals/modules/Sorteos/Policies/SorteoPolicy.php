<?php

namespace Corals\Modules\Sorteos\Policies;

use Corals\Foundation\Policies\BasePolicy;
use Corals\Modules\Sorteos\Models\Sorteo;
use Corals\User\Models\User;

class SorteoPolicy extends BasePolicy
{
    protected $administrationPermission = 'Administrations::admin.sorteos';

    /**
     * @param User $user
     * @return bool
     */
    public function view(User $user)
    {
        if ($user->can('Sorteos::sorteo.view')) {
            return true;
        }

        return false;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->can('Sorteos::sorteo.create');
    }

    /**
     * @param User $user
     * @param Sorteo $sorteo
     * @return bool
     */
    public function update(User $user, Sorteo $sorteo)
    {
        if ($user->can('Sorteos::sorteo.update')) {
            return true;
        }

        return false;
    }

    /**
     * @param User $user
     * @param Sorteo $sorteo
     * @return bool
     */
    public function destroy(User $user, Sorteo $sorteo)
    {
        if ($user->can('Sorteos::sorteo.delete')) {
            return true;
        }

        return false;
    }
}
