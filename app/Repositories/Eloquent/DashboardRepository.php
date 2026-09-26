<?php

namespace App\Repositories\Eloquent;

use App\Models\Business;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Opportunity;
use App\Models\Referral;
use App\Models\User;
use App\Repositories\Contracts\DashboardRepositoryInterface;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function getAdminMetrics(): array
    {
        return [
            'total_anggota' => User::count(),
            'anggota_aktif' => User::where('status_aktif', true)->count(),
            'total_usaha_aktif' => Business::where('status', 'aktif')->count(),
            'total_peluang_aktif' => Opportunity::where('status', 'published')->count(),
            'total_referral' => Referral::count(),
            'total_event' => Event::count(),
            'event_terdekat' => Event::where('tanggal_mulai', '>=', now())
                ->orderBy('tanggal_mulai', 'asc')
                ->first(),
            'peluang_terbaru' => Opportunity::with('user')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get(),
        ];
    }

    public function getMemberMetrics(int $userId): array
    {
        $user = User::with(['skills', 'professionalRoles'])->find($userId);

        return [
            'user' => $user,
            'skills' => $user?->skills ?? collect(),
            'usaha_aktif' => Business::where('user_id', $userId)
                ->where('status', 'aktif')
                ->get(),
            'peluang_dibuat' => Opportunity::where('user_id', $userId)
                ->orderBy('id', 'desc')
                ->take(5)
                ->get(),
            'event_diikuti' => EventRegistration::with('event')
                ->where('user_id', $userId)
                ->orderBy('id', 'desc')
                ->take(5)
                ->get(),
        ];
    }
}
