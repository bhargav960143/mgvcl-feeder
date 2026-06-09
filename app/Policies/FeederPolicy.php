<?php

namespace App\Policies;

use App\Models\Feeder;
use App\Models\User;

class FeederPolicy
{
    public function updateStatus(User $user, Feeder $feeder): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('circle')) {
            return $feeder->substation->subDivision->division->circle_id === $user->jurisdiction_id;
        }

        return false;
    }

    public function manage(User $user, Feeder $feeder): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('circle')) {
            return $feeder->substation->subDivision->division->circle_id === $user->jurisdiction_id;
        }

        return false;
    }
}
