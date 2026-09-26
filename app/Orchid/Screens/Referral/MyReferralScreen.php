<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Referral;

use App\Models\Referral;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

class MyReferralScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $userId = Auth::id();

        return [
            'referrals' => Referral::with(['pemberi', 'penerima'])
                ->where(function ($q) use ($userId) {
                    $q->where('pemberi_referral_id', $userId)
                      ->orWhere('penerima_referral_id', $userId);
                })
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
        return 'Referral Saya';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Kelola referral bisnis yang Anda berikan atau terima.';
    }

    /**
     * Permissions required to view this screen (Self-service member screen).
     */
    public function permission(): ?iterable
    {
        return null;
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Buat Referral Baru')
                ->modal('createReferralModal')
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

                TD::make('pemberi', 'Pemberi Referral')->render(function (Referral $r) {
                    if ($r->pemberi_referral_id === Auth::id()) {
                        return '<strong>Saya</strong>';
                    }
                    return e($r->pemberi?->name ?? 'N/A');
                }),

                TD::make('penerima', 'Penerima Referral')->render(function (Referral $r) {
                    if ($r->penerima_referral_id === Auth::id()) {
                        return '<strong>Saya</strong>';
                    }
                    return e($r->penerima?->name ?? 'N/A');
                }),

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
                    $actions = [
                        ModalToggle::make('Edit / Update Status')
                            ->modal('editReferralModal')
                            ->method('save')
                            ->asyncParameters(['referral' => $r->id])
                            ->icon('bs.pencil'),
                    ];

                    if ($r->pemberi_referral_id === Auth::id()) {
                        $actions[] = Button::make('Hapus')
                            ->method('remove')
                            ->parameters(['id' => $r->id])
                            ->confirm('Apakah Anda yakin ingin menghapus referral ini?')
                            ->type(Color::DANGER)
                            ->icon('bs.trash');
                    }

                    return Group::make($actions);
                }),
            ]),

            Layout::modal('createReferralModal', Layout::rows([
                Select::make('referral.penerima_referral_id')
                    ->title('Penerima Referral')
                    ->options(
                        User::where('status_aktif', true)
                            ->where('id', '!=', Auth::id())
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->empty('Pilih Anggota Penerima')
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
                    ->default('introduced')
                    ->required(),

                TextArea::make('referral.catatan')
                    ->title('Catatan')
                    ->rows(3)
                    ->placeholder('Catatan atau instruksi tambahan...'),
            ]))->title('Buat Referral Baru'),

            Layout::modal('editReferralModal', Layout::rows([
                Input::make('referral.id')->type('hidden'),

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

                Input::make('referral.client_name')
                    ->title('Nama Klien')
                    ->required(),

                Input::make('referral.project_name')
                    ->title('Nama Proyek')
                    ->required(),

                Input::make('referral.nilai_estimasi')
                    ->title('Nilai Estimasi (Rp)')
                    ->type('number')
                    ->step('0.01'),

                TextArea::make('referral.catatan')
                    ->title('Catatan')
                    ->rows(3),
            ]))->title('Edit / Update Status Referral')->async('asyncGetReferral'),
        ];
    }

    /**
     * Get async data for modal edit.
     */
    public function asyncGetReferral(Referral $referral): iterable
    {
        $userId = Auth::id();
        if ($referral->exists && $referral->pemberi_referral_id !== $userId && $referral->penerima_referral_id !== $userId) {
            abort(403);
        }

        return [
            'referral' => $referral,
        ];
    }

    /**
     * Save referral.
     */
    public function save(Request $request): void
    {
        $userId = Auth::id();

        $data = $request->validate([
            'referral.id'                   => 'nullable|integer|exists:referrals,id',
            'referral.penerima_referral_id' => 'required_without:referral.id|nullable|integer|exists:users,id',
            'referral.client_name'          => 'required|string|max:255',
            'referral.project_name'         => 'required|string|max:255',
            'referral.nilai_estimasi'       => 'nullable|numeric|min:0',
            'referral.status'               => 'required|in:introduced,follow_up,negotiation,won,lost',
            'referral.catatan'              => 'nullable|string',
        ]);

        $rData = $data['referral'];
        $id = $rData['id'] ?? null;

        if ($id) {
            $referral = Referral::where(function ($q) use ($userId) {
                $q->where('pemberi_referral_id', $userId)
                  ->orWhere('penerima_referral_id', $userId);
            })->findOrFail($id);

            // Prevent updating pemberi/penerima IDs on edit
            unset($rData['pemberi_referral_id'], $rData['penerima_referral_id']);

            $referral->update($rData);
        } else {
            $rData['pemberi_referral_id'] = $userId;
            Referral::create($rData);
        }

        Toast::info('Referral berhasil disimpan.');
    }

    /**
     * Soft delete referral.
     */
    public function remove(Request $request): void
    {
        $userId = Auth::id();
        $referral = Referral::where('pemberi_referral_id', $userId)->findOrFail($request->get('id'));
        $referral->delete();

        Toast::info('Referral berhasil dihapus.');
    }
}
