<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Dashboard;

use App\Models\Opportunity;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class DashboardScreen extends Screen
{
    protected DashboardRepositoryInterface $dashboardRepository;

    public function __construct(DashboardRepositoryInterface $dashboardRepository)
    {
        $this->dashboardRepository = $dashboardRepository;
    }

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $user = Auth::user();
        $isAdminOrPengurus = $user && ($user->inRole('administrator') || $user->inRole('pengurus'));

        if ($isAdminOrPengurus) {
            $adminMetrics = $this->dashboardRepository->getAdminMetrics();

            return [
                'isAdmin' => true,
                'metrics' => [
                    'total_anggota' => number_format((float) $adminMetrics['total_anggota']),
                    'anggota_aktif' => number_format((float) $adminMetrics['anggota_aktif']),
                    'total_usaha_aktif' => number_format((float) $adminMetrics['total_usaha_aktif']),
                    'total_peluang_aktif' => number_format((float) $adminMetrics['total_peluang_aktif']),
                    'total_referral' => number_format((float) $adminMetrics['total_referral']),
                    'total_event' => number_format((float) $adminMetrics['total_event']),
                ],
                'event_terdekat' => $adminMetrics['event_terdekat'],
                'peluang_terbaru' => $adminMetrics['peluang_terbaru'],
            ];
        }

        $memberMetrics = $this->dashboardRepository->getMemberMetrics($user->id);

        return [
            'isAdmin' => false,
            'user' => $memberMetrics['user'],
            'skills' => $memberMetrics['skills'],
            'usaha_aktif' => $memberMetrics['usaha_aktif'],
            'peluang_dibuat' => $memberMetrics['peluang_dibuat'],
            'event_diikuti' => $memberMetrics['event_diikuti'],
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        $user = Auth::user();
        if ($user && ($user->inRole('administrator') || $user->inRole('pengurus'))) {
            return 'Dashboard Administrator';
        }

        return 'Dashboard Anggota';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        $user = Auth::user();
        if ($user && ($user->inRole('administrator') || $user->inRole('pengurus'))) {
            return 'Ringkasan statistik dan aktivitas komunitas KPMI Pekalongan.';
        }

        return 'Selamat datang di Panel Anggota KPMI Pekalongan.';
    }

    /**
     * The screen's action buttons.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Lihat Profil Saya')
                ->route('platform.profile')
                ->icon('bs.person'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        $user = Auth::user();
        $isAdminOrPengurus = $user && ($user->inRole('administrator') || $user->inRole('pengurus'));

        if ($isAdminOrPengurus) {
            return [
                Layout::metrics([
                    'Total Anggota' => 'metrics.total_anggota',
                    'Anggota Aktif' => 'metrics.anggota_aktif',
                    'Total Usaha Aktif' => 'metrics.total_usaha_aktif',
                    'Total Peluang Aktif' => 'metrics.total_peluang_aktif',
                    'Total Referral' => 'metrics.total_referral',
                    'Total Event' => 'metrics.total_event',
                ]),

                Layout::columns([
                    Layout::view('platform.dashboard.event-terdekat'),
                    Layout::table('peluang_terbaru', [
                        TD::make('tipe', 'Tipe')->render(fn (Opportunity $o) => strtoupper($o->tipe)),
                        TD::make('judul', 'Peluang Terbaru'),
                        TD::make('user.name', 'Pembuat')->render(fn (Opportunity $o) => $o->user?->name ?? '-'),
                        TD::make('created_at', 'Tanggal')->render(fn (Opportunity $o) => $o->created_at?->format('d M Y')),
                    ]),
                ]),
            ];
        }

        return [
            Layout::view('platform.dashboard.member-summary'),
        ];
    }
}
