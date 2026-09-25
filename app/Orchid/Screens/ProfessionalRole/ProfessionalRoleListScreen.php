<?php

declare(strict_types=1);

namespace App\Orchid\Screens\ProfessionalRole;

use App\Models\ProfessionalRole;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ProfessionalRoleListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'professional_roles' => ProfessionalRole::filters()->defaultSort('id', 'asc')->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Peran Profesi';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Kelola master data Peran Profesi.';
    }

    /**
     * Permissions required to view this screen.
     */
    public function permission(): ?iterable
    {
        return [
            'platform.master.professional_roles',
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
            ModalToggle::make('Tambah Peran Profesi')
                ->modal('professionalRoleModal')
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
            Layout::table('professional_roles', [
                TD::make('id', 'ID')->sort()->cantHide(),
                TD::make('nama', 'Nama Peran Profesi')->sort()->filter(Input::make()),
                TD::make('created_at', 'Tanggal Dibuat')->sort()->render(fn (ProfessionalRole $role) => $role->created_at?->format('Y-m-d H:i') ?? '-'),
                TD::make('Actions', 'Aksi')->alignRight()->render(fn (ProfessionalRole $role) => Group::make([
                    ModalToggle::make('Edit')
                        ->modal('professionalRoleModal')
                        ->method('save')
                        ->asyncParameters(['professionalRole' => $role->id])
                        ->icon('bs.pencil'),
                    Button::make('Hapus')
                        ->method('remove')
                        ->parameters(['id' => $role->id])
                        ->confirm('Apakah Anda yakin ingin menghapus peran profesi ini?')
                        ->icon('bs.trash'),
                ])),
            ]),

            Layout::modal('professionalRoleModal', Layout::rows([
                Input::make('professionalRole.id')->type('hidden'),
                Input::make('professionalRole.nama')
                    ->title('Nama Peran Profesi')
                    ->placeholder('Contoh: Pengusaha, Freelancer')
                    ->required(),
            ]))->title('Peran Profesi')->async('asyncGetProfessionalRole'),
        ];
    }

    /**
     * Get async data for modal form.
     */
    public function asyncGetProfessionalRole(ProfessionalRole $professionalRole): iterable
    {
        return [
            'professionalRole' => $professionalRole,
        ];
    }

    /**
     * Save professional role.
     */
    public function save(Request $request): void
    {
        $data = $request->validate([
            'professionalRole.id'   => 'nullable|integer|exists:professional_roles,id',
            'professionalRole.nama' => 'required|string|max:255',
        ]);

        $roleData = $data['professionalRole'];
        $id = $roleData['id'] ?? null;

        ProfessionalRole::updateOrCreate(
            ['id' => $id],
            ['nama' => $roleData['nama']]
        );

        Toast::info('Peran profesi berhasil disimpan.');
    }

    /**
     * Delete professional role.
     */
    public function remove(Request $request): void
    {
        $role = ProfessionalRole::findOrFail($request->get('id'));
        $role->delete();

        Toast::info('Peran profesi berhasil dihapus.');
    }
}
