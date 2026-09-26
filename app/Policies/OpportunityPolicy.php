<?php

namespace App\Policies;

use App\Models\Opportunity;
use App\Models\User;

class OpportunityPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Opportunity $opportunity): bool
    {
        return $opportunity->user_id === $user->id || $user->hasAccess('platform.opportunities');
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Opportunity $opportunity): bool
    {
        return $opportunity->user_id === $user->id || $user->hasAccess('platform.opportunities');
    }

    public function delete(User $user, Opportunity $opportunity): bool
    {
        return $opportunity->user_id === $user->id || $user->hasAccess('platform.opportunities');
    }

    public function restore(User $user, Opportunity $opportunity): bool
    {
        return $opportunity->user_id === $user->id || $user->hasAccess('platform.opportunities');
    }

    public function forceDelete(User $user, Opportunity $opportunity): bool
    {
        return $opportunity->user_id === $user->id || $user->hasAccess('platform.opportunities');
    }
}
