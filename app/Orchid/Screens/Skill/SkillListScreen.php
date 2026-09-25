<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Skill;

use App\Models\Skill;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class SkillListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'skills' => Skill::filters()->defaultSort('id', 'asc')->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Skill & Layanan';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Kelola master data Skill & Layanan.';
    }

    /**
     * Permissions required to view this screen.
     */
    public function permission(): ?iterable
    {
        return [
            'platform.master.skills',
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
            ModalToggle::make('Tambah Skill')
                ->modal('skillModal')
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
            Layout::table('skills', [
                TD::make('id', 'ID')->sort()->cantHide(),
                TD::make('nama', 'Nama Skill')->sort()->filter(Input::make()),
                TD::make('created_at', 'Tanggal Dibuat')->sort()->render(fn (Skill $skill) => $skill->created_at?->format('Y-m-d H:i') ?? '-'),
                TD::make('Actions', 'Aksi')->alignRight()->render(fn (Skill $skill) => Group::make([
                    ModalToggle::make('Edit')
                        ->modal('skillModal')
                        ->method('save')
                        ->asyncParameters(['skill' => $skill->id])
                        ->icon('bs.pencil'),
                    Button::make('Hapus')
                        ->method('remove')
                        ->parameters(['id' => $skill->id])
                        ->confirm('Apakah Anda yakin ingin menghapus skill ini?')
                        ->icon('bs.trash'),
                ])),
            ]),

            Layout::modal('skillModal', Layout::rows([
                Input::make('skill.id')->type('hidden'),
                Input::make('skill.nama')
                    ->title('Nama Skill')
                    ->placeholder('Contoh: Web Development, Graphic Design')
                    ->required(),
            ]))->title('Skill & Layanan')->async('asyncGetSkill'),
        ];
    }

    /**
     * Get async data for modal form.
     */
    public function asyncGetSkill(Skill $skill): iterable
    {
        return [
            'skill' => $skill,
        ];
    }

    /**
     * Save skill.
     */
    public function save(Request $request): void
    {
        $data = $request->validate([
            'skill.id'   => 'nullable|integer|exists:skills,id',
            'skill.nama' => 'required|string|max:255',
        ]);

        $skillData = $data['skill'];
        $id = $skillData['id'] ?? null;

        Skill::updateOrCreate(
            ['id' => $id],
            ['nama' => $skillData['nama']]
        );

        Toast::info('Skill berhasil disimpan.');
    }

    /**
     * Delete skill.
     */
    public function remove(Request $request): void
    {
        $skill = Skill::findOrFail($request->get('id'));
        $skill->delete();

        Toast::info('Skill berhasil dihapus.');
    }
}
