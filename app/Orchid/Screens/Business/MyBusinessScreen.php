<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Business;

use App\Models\Business;
use App\Models\BusinessCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Action;
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

class MyBusinessScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'businesses' => Business::where('user_id', Auth::id())
                ->with('category')
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
        return 'Usaha Saya';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Kelola daftar usaha milik Anda.';
    }

    /**
     * Permissions required to view this screen (No permission required for member self-service).
     */
    public function permission(): ?iterable
    {
        return null;
    }

    /**
     * The screen's action buttons.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Tambah Usaha')
                ->modal('businessModal')
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
            Layout::table('businesses', [
                TD::make('id', 'ID')->sort()->cantHide(),
                TD::make('nama_usaha', 'Nama Usaha')->sort()->filter(Input::make()),
                TD::make('category.nama', 'Kategori Usaha')->render(fn (Business $b) => $b->category?->nama ?? '-'),
                TD::make('status', 'Status')->sort()->render(function (Business $b) {
                    return $b->status === 'aktif'
                        ? '<span class="badge bg-success">Aktif</span>'
                        : '<span class="badge bg-secondary">Non-Aktif</span>';
                }),
                TD::make('created_at', 'Tanggal Dibuat')->sort()->render(fn (Business $b) => $b->created_at?->format('Y-m-d H:i') ?? '-'),
                TD::make('Actions', 'Aksi')->alignRight()->render(function (Business $b) {
                    $toggleText = $b->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan';
                    $toggleColor = $b->status === 'aktif' ? Color::WARNING : Color::SUCCESS;
                    $toggleIcon = $b->status === 'aktif' ? 'bs.pause-circle' : 'bs.play-circle';

                    return Group::make([
                        ModalToggle::make('Edit')
                            ->modal('businessModal')
                            ->method('save')
                            ->asyncParameters(['business' => $b->id])
                            ->icon('bs.pencil'),
                        Button::make($toggleText)
                            ->method('toggleStatus')
                            ->parameters(['id' => $b->id])
                            ->type($toggleColor)
                            ->icon($toggleIcon),
                        Button::make('Hapus')
                            ->method('remove')
                            ->parameters(['id' => $b->id])
                            ->confirm('Apakah Anda yakin ingin menghapus usaha ini?')
                            ->type(Color::DANGER)
                            ->icon('bs.trash'),
                    ]);
                }),
            ]),

            Layout::modal('businessModal', Layout::rows([
                Input::make('business.id')->type('hidden'),
                Input::make('business.nama_usaha')
                    ->title('Nama Usaha')
                    ->placeholder('Contoh: Batik Pekalongan Jaya')
                    ->required(),
                Select::make('business.business_category_id')
                    ->title('Kategori Usaha')
                    ->fromModel(BusinessCategory::class, 'nama')
                    ->empty('Pilih Kategori Usaha')
                    ->required(),
                TextArea::make('business.deskripsi')
                    ->title('Deskripsi Usaha')
                    ->rows(4)
                    ->placeholder('Jelaskan produk/jasa usaha Anda...'),
                Input::make('business.alamat')
                    ->title('Alamat Usaha')
                    ->placeholder('Alamat lokasi usaha...'),
                Input::make('business.website')
                    ->title('Website / Social Media')
                    ->placeholder('https://...'),
                Select::make('business.status')
                    ->title('Status Usaha')
                    ->options([
                        'aktif' => 'Aktif',
                        'non_aktif' => 'Non-Aktif',
                    ])
                    ->required(),
            ]))->title('Usaha Saya')->async('asyncGetBusiness'),
        ];
    }

    /**
     * Get async data for modal form.
     */
    public function asyncGetBusiness(Business $business): iterable
    {
        // Ensure user can only edit their own business
        if ($business->exists && $business->user_id !== Auth::id()) {
            abort(403);
        }

        return [
            'business' => $business,
        ];
    }

    /**
     * Save business.
     */
    public function save(Request $request): void
    {
        $data = $request->validate([
            'business.id' => 'nullable|integer|exists:businesses,id',
            'business.nama_usaha' => 'required|string|max:255',
            'business.business_category_id' => 'required|integer|exists:business_categories,id',
            'business.deskripsi' => 'nullable|string',
            'business.alamat' => 'nullable|string|max:255',
            'business.website' => 'nullable|string|max:255',
            'business.status' => 'required|in:aktif,non_aktif',
        ]);

        $bData = $data['business'];
        $id = $bData['id'] ?? null;

        if ($id) {
            $business = Business::where('user_id', Auth::id())->findOrFail($id);
            $business->update($bData);
        } else {
            $bData['user_id'] = Auth::id();
            Business::create($bData);
        }

        Toast::info('Usaha berhasil disimpan.');
    }

    /**
     * Toggle business active status without soft deleting.
     */
    public function toggleStatus(Request $request): void
    {
        $business = Business::where('user_id', Auth::id())->findOrFail($request->get('id'));
        $business->status = $business->status === 'aktif' ? 'non_aktif' : 'aktif';
        $business->save();

        $statusText = $business->status === 'aktif' ? 'diaktifkan' : 'dinonaktifkan';
        Toast::info("Usaha berhasil {$statusText}.");
    }

    /**
     * Soft delete business.
     */
    public function remove(Request $request): void
    {
        $business = Business::where('user_id', Auth::id())->findOrFail($request->get('id'));
        $business->delete();

        Toast::info('Usaha berhasil dihapus.');
    }
}
