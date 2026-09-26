<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Event;

use App\Models\Event;
use Illuminate\Http\Request;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class EventEditScreen extends Screen
{
    /**
     * @var Event|null
     */
    public $event;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Event $event): iterable
    {
        return [
            'event' => $event,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return $this->event->exists ? 'Edit Event' : 'Tambah Event Baru';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Kelola rincian event komunitas KPMI Pekalongan.';
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
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Simpan')
                ->icon('bs.check-circle')
                ->type(Color::PRIMARY)
                ->method('save'),

            Button::make('Hapus')
                ->icon('bs.trash')
                ->type(Color::DANGER)
                ->method('remove')
                ->canSee($this->event->exists)
                ->confirm('Apakah Anda yakin ingin menghapus event ini?'),
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
            Layout::rows([
                Input::make('event.judul')
                    ->title('Judul Event')
                    ->placeholder('Masukkan judul event')
                    ->required(),

                Input::make('event.lokasi')
                    ->title('Lokasi')
                    ->placeholder('Lokasi / Tempat Pelaksanaan')
                    ->required(),

                DateTimer::make('event.tanggal_mulai')
                    ->title('Tanggal & Waktu Mulai')
                    ->enableTime()
                    ->format('Y-m-d H:i:s')
                    ->required(),

                DateTimer::make('event.tanggal_selesai')
                    ->title('Tanggal & Waktu Selesai')
                    ->enableTime()
                    ->format('Y-m-d H:i:s')
                    ->required(),

                Input::make('event.kuota')
                    ->title('Kuota Peserta')
                    ->type('number')
                    ->placeholder('Jumlah kuota peserta')
                    ->required(),

                Select::make('event.status')
                    ->title('Status Event')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'finished' => 'Finished',
                    ])
                    ->required(),

                Quill::make('event.deskripsi')
                    ->title('Deskripsi Event')
                    ->placeholder('Rincian mengenai agenda, pembicara, materi, dll.'),
            ]),
        ];
    }

    /**
     * Save or update event.
     */
    public function save(Event $event, Request $request)
    {
        $validated = $request->validate([
            'event.judul' => 'required|string|max:255',
            'event.lokasi' => 'required|string|max:255',
            'event.tanggal_mulai' => 'required|date',
            'event.tanggal_selesai' => 'required|date|after_or_equal:event.tanggal_mulai',
            'event.kuota' => 'required|integer|min:1',
            'event.status' => 'required|in:draft,published,finished',
            'event.deskripsi' => 'nullable|string',
        ]);

        $event->fill($validated['event'])->save();

        Toast::info('Data event berhasil disimpan.');

        return redirect()->route('platform.events');
    }

    /**
     * Soft delete event.
     */
    public function remove(Event $event)
    {
        $event->delete();

        Toast::info('Event berhasil dihapus.');

        return redirect()->route('platform.events');
    }
}
