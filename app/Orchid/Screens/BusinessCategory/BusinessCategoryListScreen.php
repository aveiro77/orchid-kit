<?php

declare(strict_types=1);

namespace App\Orchid\Screens\BusinessCategory;

use App\Models\BusinessCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class BusinessCategoryListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'categories' => BusinessCategory::filters()->defaultSort('id', 'asc')->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Kategori Usaha';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Kelola master data Kategori Usaha.';
    }

    /**
     * Permissions required to view this screen.
     */
    public function permission(): ?iterable
    {
        return [
            'platform.master.business_categories',
        ];
    }

    /**
     * The screen's action buttons.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Tambah Kategori')
                ->modal('categoryModal')
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
            Layout::table('categories', [
                TD::make('id', 'ID')->sort()->cantHide(),
                TD::make('nama', 'Nama Kategori')->sort()->filter(Input::make()),
                TD::make('slug', 'Slug')->sort()->filter(Input::make()),
                TD::make('created_at', 'Tanggal Dibuat')->sort()->render(fn (BusinessCategory $cat) => $cat->created_at?->format('Y-m-d H:i') ?? '-'),
                TD::make('Actions', 'Aksi')->alignRight()->render(fn (BusinessCategory $cat) => Group::make([
                    ModalToggle::make('Edit')
                        ->modal('categoryModal')
                        ->method('save')
                        ->asyncParameters(['category' => $cat->id])
                        ->icon('bs.pencil'),
                    Button::make('Hapus')
                        ->method('remove')
                        ->parameters(['id' => $cat->id])
                        ->confirm('Apakah Anda yakin ingin menghapus kategori ini?')
                        ->icon('bs.trash'),
                ])),
            ]),

            Layout::modal('categoryModal', Layout::rows([
                Input::make('category.id')->type('hidden'),
                Input::make('category.nama')
                    ->title('Nama Kategori')
                    ->placeholder('Contoh: Kuliner, Fashion Muslim')
                    ->required(),
                Input::make('category.slug')
                    ->title('Slug')
                    ->placeholder('Dibiarkan kosong untuk auto-generate dari nama'),
            ]))->title('Kategori Usaha')->async('asyncGetCategory'),
        ];
    }

    /**
     * Get async data for modal form.
     */
    public function asyncGetCategory(BusinessCategory $category): iterable
    {
        return [
            'category' => $category,
        ];
    }

    /**
     * Save category.
     */
    public function save(Request $request): void
    {
        $data = $request->validate([
            'category.id' => 'nullable|integer|exists:business_categories,id',
            'category.nama' => 'required|string|max:255',
            'category.slug' => 'nullable|string|max:255',
        ]);

        $catData = $data['category'];
        $id = $catData['id'] ?? null;
        $slug = ! empty($catData['slug']) ? Str::slug($catData['slug']) : Str::slug($catData['nama']);

        BusinessCategory::updateOrCreate(
            ['id' => $id],
            [
                'nama' => $catData['nama'],
                'slug' => $slug,
            ]
        );

        Toast::info('Kategori usaha berhasil disimpan.');
    }

    /**
     * Delete category.
     */
    public function remove(Request $request): void
    {
        $cat = BusinessCategory::findOrFail($request->get('id'));
        $cat->delete();

        Toast::info('Kategori usaha berhasil dihapus.');
    }
}
