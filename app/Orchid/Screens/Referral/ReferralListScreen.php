<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Referral;

use App\Models\Referral;
use App\Models\User;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ReferralListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'referrals' => Referral::with(['pemberi', 'penerima'])
                ->filters()
                ->defaultSort('id', 'desc')
                ->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Daftar Semua Referral';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Kelola dan lihat seluruh data referral antar anggota untuk laporan & statistik komunitas KPMI.';
    }

    /**
     * Permissions required to view this screen.
     */
    public function permission(): ?iterable
    {
        return [
            'platform.referrals',
        ];
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Tambah Referral Admin')
                ->modal('referralModal')
                ->method('save')
                ->icon('bs.plus-circle'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('referrals', [
                TD::make('id', 'ID')->sort()->cantHide(),

                TD::make('pemberi_referral_id', 'Pemberi Referral')->sort()->render(
                    fn (Referral $r) => e($r->pemberi?->name ?? 'N/A')
                ),

                TD::make('penerima_referral_id', 'Penerima Referral')->sort()->render(
                    fn (Referral $r) => e($r->penerima?->name ?? 'N/A')
                ),

                TD::make('client_name', 'Nama Klien')->sort()->filter(Input::make()),
                TD::make('project_name', 'Nama Proyek')->sort()->filter(Input::make()),

                TD::make('nilai_estimasi', 'Nilai Estimasi (Rp)')->sort()->render(
                    fn (Referral $r) => 'Rp ' . number_format((float) $r->nilai_estimasi, 0, ',', '.')
                ),

                TD::make('status', 'Status')->sort()->filter(
                    Select::make('status')
                        ->options([
                            'introduced'  => 'Introduced',
                            'follow_up'   => 'Follow Up',
                            'negotiation' => 'Negotiation',
                            'won'         => 'Won',
                            'lost'        => 'Lost',
                        ])
                        ->empty('Semua Status')
                )->render(function (Referral $r) {
                    $badges = [
                        'introduced'  => 'bg-info text-dark',
                        'follow_up'   => 'bg-warning text-dark',
                        'negotiation' => 'bg-primary',
                        'won'         => 'bg-success',
                        'lost'        => 'bg-danger',
                    ];
                    $label = ucfirst(str_replace('_', ' ', $r->status));
                    $badgeClass = $badges[$r->status] ?? 'bg-secondary';

                    return "<span class=\"badge {$badgeClass}\">{$label}</span>";
                }),

                TD::make('catatan', 'Catatan')->render(
                    fn (Referral $r) => e($r->catatan ?? '-')
                ),

                TD::make('created_at', 'Tanggal Dibuat')->sort()->render(
                    fn (Referral $r) => $r->created_at?->format('Y-m-d H:i') ?? '-'
                ),

                TD::make('Actions', 'Aksi')->alignRight()->render(function (Referral $r) {
                    return Group::make([
                        ModalToggle::make('Edit')
                            ->modal('referralModal')
                            ->method('save')
                            ->asyncParameters(['referral' => $r->id])
                            ->icon('bs.pencil'),
                        Button::make('Hapus')
                            ->method('remove')
                            ->parameters(['id' => $r->id])
                            ->confirm('Apakah Anda yakin ingin menghapus data referral ini?')
                            ->type(Color::DANGER)
                            ->icon('bs.trash'),
                    ]);
                }),
            ]),

            Layout::modal('referralModal', Layout::rows([
                Input::make('referral.id')->type('hidden'),

                Select::make('referral.pemberi_referral_id')
                    ->title('Pemberi Referral')
                    ->options(
                        User::where('status_aktif', true)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->empty('Pilih Member Pemberi')
                    ->required(),

                Select::make('referral.penerima_referral_id')
                    ->title('Penerima Referral')
                    ->options(
                        User::where('status_aktif', true)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->empty('Pilih Member Penerima')
                    ->required(),

                Input::make('referral.client_name')
                    ->title('Nama Klien')
                    ->placeholder('Contoh: PT Sukses Mandiri')
                    ->required(),

                Input::make('referral.project_name')
                    ->title('Nama Proyek')
                    ->placeholder('Contoh: Pembuatan Website E-Commerce')
                    ->required(),

                Input::make('referral.nilai_estimasi')
                    ->title('Nilai Estimasi (Rp)')
                    ->type('number')
                    ->step('0.01')
                    ->placeholder('0'),

                Select::make('referral.status')
                    ->title('Status')
                    ->options([
                        'introduced'  => 'Introduced',
                        'follow_up'   => 'Follow Up',
                        'negotiation' => 'Negotiation',
                        'won'         => 'Won',
                        'lost'        => 'Lost',
                    ])
                    ->required(),

                TextArea::make('referral.catatan')
                    ->title('Catatan')
                    ->rows(3),
            ]))->title('Data Referral')->async('asyncGetReferral'),
        ];
    }

    /**
     * Get async data for modal form.
     */
    public function asyncGetReferral(Referral $referral): iterable
    {
        return [
            'referral' => $referral,
        ];
    }

    /**
     * Save referral.
     */
    public function save(Request $request): void
    {
        $data = $request->validate([
            'referral.id'                   => 'nullable|integer|exists:referrals,id',
            'referral.pemberi_referral_id'  => 'required|integer|exists:users,id',
            'referral.penerima_referral_id' => 'required|integer|exists:users,id|different:referral.pemberi_referral_id',
            'referral.client_name'          => 'required|string|max:255',
            'referral.project_name'         => 'required|string|max:255',
            'referral.nilai_estimasi'       => 'nullable|numeric|min:0',
            'referral.status'               => 'required|in:introduced,follow_up,negotiation,won,lost',
            'referral.catatan'              => 'nullable|string',
        ]);

        $rData = $data['referral'];
        $id = $rData['id'] ?? null;

        if ($id) {
            $referral = Referral::findOrFail($id);
            $referral->update($rData);
        } else {
            Referral::create($rData);
        }

        Toast::info('Referral berhasil disimpan.');
    }

    /**
     * Soft delete referral.
     */
    public function remove(Request $request): void
    {
        $referral = Referral::findOrFail($request->get('id'));
        $referral->delete();

        Toast::info('Referral berhasil dihapus.');
    }
}
