<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Opportunity;

use App\Models\Opportunity;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class OpportunityListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'opportunities' => Opportunity::with('user')
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
        return 'Daftar Semua Peluang';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Lihat dan moderasi seluruh data peluang bisnis anggota KPMI.';
    }

    /**
     * Permissions required to view this screen.
     */
    public function permission(): ?iterable
    {
        return [
            'platform.opportunities',
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
            ModalToggle::make('Posting Peluang')
                ->modal('adminOpportunityModal')
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
            Layout::table('opportunities', [
                TD::make('id', 'ID')->sort()->cantHide(),
                TD::make('tipe', 'Tipe')->sort()->filter(
                    Select::make('tipe')
                        ->options([
                            'need'        => 'Need',
                            'offer'       => 'Offer',
                            'collaborate' => 'Collaborate',
                        ])
                        ->empty('Semua Tipe')
                )->render(function (Opportunity $o) {
                    $badges = [
                        'need'        => 'bg-danger',
                        'offer'       => 'bg-success',
                        'collaborate' => 'bg-info text-dark',
                    ];
                    $label = ucfirst($o->tipe);
                    $badgeClass = $badges[$o->tipe] ?? 'bg-secondary';

                    return "<span class=\"badge {$badgeClass}\">{$label}</span>";
                }),
                TD::make('judul', 'Judul')->sort()->filter(Input::make()),
                TD::make('user.name', 'Pembuat')->render(fn (Opportunity $o) => $o->user?->name ?? '-'),
                TD::make('lokasi', 'Lokasi')->sort()->filter(Input::make()),
                TD::make('tanggal_expired', 'Tanggal Expired')->sort()->render(
                    fn (Opportunity $o) => $o->tanggal_expired?->format('Y-m-d') ?? '-'
                ),
                TD::make('status', 'Status')->sort()->filter(
                    Select::make('status')
                        ->options([
                            'draft'     => 'Draft',
                            'published' => 'Published',
                            'closed'    => 'Closed',
                        ])
                        ->empty('Semua Status')
                )->render(function (Opportunity $o) {
                    $badges = [
                        'draft'     => 'bg-secondary',
                        'published' => 'bg-primary',
                        'closed'    => 'bg-dark',
                    ];
                    $label = ucfirst($o->status);
                    $badgeClass = $badges[$o->status] ?? 'bg-secondary';

                    return "<span class=\"badge {$badgeClass}\">{$label}</span>";
                }),
                TD::make('created_at', 'Tanggal Dibuat')->sort()->render(
                    fn (Opportunity $o) => $o->created_at?->format('Y-m-d H:i') ?? '-'
                ),
                TD::make('Actions', 'Aksi')->alignRight()->render(function (Opportunity $o) {
                    return Group::make([
                        ModalToggle::make('Edit')
                            ->modal('adminOpportunityModal')
                            ->method('save')
                            ->asyncParameters(['opportunity' => $o->id])
                            ->icon('bs.pencil'),
                        Button::make('Hapus')
                            ->method('remove')
                            ->parameters(['id' => $o->id])
                            ->confirm('Apakah Anda yakin ingin menghapus peluang ini?')
                            ->type(Color::DANGER)
                            ->icon('bs.trash'),
                    ]);
                }),
            ]),

            Layout::modal('adminOpportunityModal', Layout::rows([
                Input::make('opportunity.id')->type('hidden'),
                Select::make('opportunity.tipe')
                    ->title('Tipe Peluang')
                    ->options([
                        'need'        => 'Need (Kebutuhan)',
                        'offer'       => 'Offer (Penawaran)',
                        'collaborate' => 'Collaborate (Kolaborasi)',
                    ])
                    ->required(),
                Input::make('opportunity.judul')
                    ->title('Judul Peluang')
                    ->placeholder('Contoh: Mencari Supplier Kain Batik Pekalongan')
                    ->required(),
                TextArea::make('opportunity.deskripsi')
                    ->title('Deskripsi Peluang')
                    ->rows(4)
                    ->placeholder('Jelaskan rincian peluang/kebutuhan/penawaran/kolaborasi...'),
                Input::make('opportunity.lokasi')
                    ->title('Lokasi')
                    ->placeholder('Contoh: Pekalongan / Remote / Online'),
                DateTimer::make('opportunity.tanggal_expired')
                    ->title('Tanggal Expired')
                    ->format('Y-m-d')
                    ->allowInput(),
                Select::make('opportunity.status')
                    ->title('Status Moderasi')
                    ->options([
                        'draft'     => 'Draft',
                        'published' => 'Published',
                        'closed'    => 'Closed',
                    ])
                    ->required(),
            ]))->title('Moderasi / Edit Peluang')->async('asyncGetOpportunity'),
        ];
    }

    /**
     * Get async data for modal form.
     */
    public function asyncGetOpportunity(Opportunity $opportunity): iterable
    {
        return [
            'opportunity' => $opportunity,
        ];
    }

    /**
     * Save/Update opportunity by Admin/Pengurus.
     */
    public function save(Request $request): void
    {
        $data = $request->validate([
            'opportunity.id'              => 'nullable|integer|exists:opportunities,id',
            'opportunity.tipe'            => 'required|in:need,offer,collaborate',
            'opportunity.judul'           => 'required|string|max:255',
            'opportunity.deskripsi'       => 'nullable|string',
            'opportunity.lokasi'          => 'nullable|string|max:255',
            'opportunity.tanggal_expired' => 'nullable|date',
            'opportunity.status'          => 'required|in:draft,published,closed',
        ]);

        $oData = $data['opportunity'];
        $id = $oData['id'] ?? null;

        if ($id) {
            $opportunity = Opportunity::findOrFail($id);
            $opportunity->update($oData);
        } else {
            $oData['user_id'] = auth()->id();
            Opportunity::create($oData);
        }

        Toast::info('Peluang berhasil diperbarui.');
    }

    /**
     * Soft delete opportunity.
     */
    public function remove(Request $request): void
    {
        $opportunity = Opportunity::findOrFail($request->get('id'));
        $opportunity->delete();

        Toast::info('Peluang berhasil dihapus.');
    }
}
