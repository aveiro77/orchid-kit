<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Business;

use App\Models\Business;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class BusinessListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'businesses' => Business::with(['user', 'category'])
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
        return 'Daftar Semua Usaha';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Kelola dan pantau seluruh data usaha milik anggota KPMI.';
    }

    /**
     * Permissions required to view this screen.
     */
    public function permission(): ?iterable
    {
        return [
            'platform.businesses',
        ];
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
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
                TD::make('user.name', 'Pemilik')->render(fn (Business $b) => $b->user?->name ?? '-'),
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

                    return Group::make([
                        Button::make($toggleText)
                            ->method('toggleStatus')
                            ->parameters(['id' => $b->id])
                            ->type($toggleColor),
                        Button::make('Hapus')
                            ->method('remove')
                            ->parameters(['id' => $b->id])
                            ->confirm('Apakah Anda yakin ingin menghapus data usaha ini?')
                            ->type(Color::DANGER)
                            ->icon('bs.trash'),
                    ]);
                }),
            ]),
        ];
    }

    /**
     * Toggle business active status.
     */
    public function toggleStatus(Request $request): void
    {
        $business = Business::findOrFail($request->get('id'));
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
        $business = Business::findOrFail($request->get('id'));
        $business->delete();

        Toast::info('Usaha berhasil dihapus.');
    }
}
