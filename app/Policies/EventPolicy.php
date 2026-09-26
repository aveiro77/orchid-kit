<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Event $event): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasAccess('platform.events');
    }

    public function update(User $user, Event $event): bool
    {
        return $user->hasAccess('platform.events');
    }

    public function delete(User $user, Event $event): bool
    {
        return $user->hasAccess('platform.events');
    }

    public function restore(User $user, Event $event): bool
    {
        return $user->hasAccess('platform.events');
    }

    public function forceDelete(User $user, Event $event): bool
    {
        return $user->hasAccess('platform.events');
    }
}
