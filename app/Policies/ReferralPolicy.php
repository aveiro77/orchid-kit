<?php

namespace App\Policies;

use App\Models\Referral;
use App\Models\User;

class ReferralPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Referral $referral): bool
    {
        return $referral->pemberi_referral_id === $user->id
            || $referral->penerima_referral_id === $user->id
            || $user->hasAccess('platform.referrals');
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Referral $referral): bool
    {
        return $referral->pemberi_referral_id === $user->id
            || $referral->penerima_referral_id === $user->id
            || $user->hasAccess('platform.referrals');
    }

    public function delete(User $user, Referral $referral): bool
    {
        return $referral->pemberi_referral_id === $user->id
            || $user->hasAccess('platform.referrals');
    }

    public function restore(User $user, Referral $referral): bool
    {
        return $referral->pemberi_referral_id === $user->id
            || $user->hasAccess('platform.referrals');
    }

    public function forceDelete(User $user, Referral $referral): bool
    {
        return $referral->pemberi_referral_id === $user->id
            || $user->hasAccess('platform.referrals');
    }
}
