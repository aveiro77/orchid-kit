<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\User;

class BusinessPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Business $business): bool
    {
        return $business->user_id === $user->id || $user->hasAccess('platform.businesses');
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Business $business): bool
    {
        return $business->user_id === $user->id || $user->hasAccess('platform.businesses');
    }

    public function delete(User $user, Business $business): bool
    {
        return $business->user_id === $user->id || $user->hasAccess('platform.businesses');
    }

    public function restore(User $user, Business $business): bool
    {
        return $business->user_id === $user->id || $user->hasAccess('platform.businesses');
    }

    public function forceDelete(User $user, Business $business): bool
    {
        return $business->user_id === $user->id || $user->hasAccess('platform.businesses');
    }
}
