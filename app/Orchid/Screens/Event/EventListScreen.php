<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Event;

use App\Models\Event;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class EventListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'events' => Event::withCount('registrations')
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
        return 'Daftar Semua Event';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Kelola dan pantau seluruh data event komunitas KPMI Pekalongan.';
    }

    /**
     * Permissions required to view this screen.
     */
    public function permission(): ?iterable
    {
        return [
            'platform.events',
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
            Link::make('Tambah Event')
                ->icon('bs.plus-circle')
                ->route('platform.events.create'),
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
            Layout::table('events', [
                TD::make('id', 'ID')->sort()->cantHide(),
                TD::make('judul', 'Judul Event')->sort()->filter(Input::make()),
                TD::make('lokasi', 'Lokasi')->sort()->filter(Input::make()),
                TD::make('tanggal_mulai', 'Tanggal Mulai')->sort()->render(fn (Event $e) => $e->tanggal_mulai?->format('Y-m-d H:i') ?? '-'),
                TD::make('tanggal_selesai', 'Tanggal Selesai')->sort()->render(fn (Event $e) => $e->tanggal_selesai?->format('Y-m-d H:i') ?? '-'),
                TD::make('kuota', 'Kuota & Pendaftar')->render(fn (Event $e) => "{$e->registrations_count} / {$e->kuota}"),
                TD::make('status', 'Status')->sort()->filter(
                    Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'published' => 'Published',
                            'finished' => 'Finished',
                        ])
                        ->empty('Semua Status')
                )->render(function (Event $e) {
                    if ($e->status === 'published') {
                        return '<span class="badge bg-success">Published</span>';
                    }
                    if ($e->status === 'finished') {
                        return '<span class="badge bg-info text-dark">Finished</span>';
                    }
                    return '<span class="badge bg-secondary">Draft</span>';
                }),
                TD::make('Actions', 'Aksi')->alignRight()->render(function (Event $e) {
                    return Group::make([
                        Link::make('Edit')
                            ->route('platform.events.edit', $e->id)
                            ->icon('bs.pencil')
                            ->type(Color::PRIMARY),

                        Button::make('Hapus')
                            ->method('remove')
                            ->parameters(['id' => $e->id])
                            ->confirm('Apakah Anda yakin ingin menghapus event ini?')
                            ->type(Color::DANGER)
                            ->icon('bs.trash'),
                    ]);
                }),
            ]),
        ];
    }

    /**
     * Soft delete event.
     */
    public function remove(Request $request): void
    {
        $event = Event::findOrFail($request->get('id'));
        $event->delete();

        Toast::info('Event berhasil dihapus.');
    }
}
