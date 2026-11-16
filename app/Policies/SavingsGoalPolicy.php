<?php

namespace App\Policies;

use App\Models\SavingsGoal;
use App\Models\User;

class SavingsGoalPolicy
{
    /** A child may manage only their OWN goals. */
    public function manage(User $user, SavingsGoal $goal): bool
    {
        return $user->isChild() && $goal->user_id === $user->id;
    }
}