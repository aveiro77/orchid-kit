<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Event;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class MyEventScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $userId = Auth::id();

        // Published events available for registration
        $availableEvents = Event::where('status', 'published')
            ->withCount('registrations')
            ->orderBy('tanggal_mulai', 'asc')
            ->paginate(10, ['*'], 'available_page');

        // Events user has registered for
        $myRegistrations = EventRegistration::where('user_id', $userId)
            ->with('event')
            ->orderBy('id', 'desc')
            ->paginate(10, ['*'], 'my_page');

        return [
            'availableEvents' => $availableEvents,
            'myRegistrations' => $myRegistrations,
            'registeredEventIds' => EventRegistration::where('user_id', $userId)->pluck('event_id')->toArray(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Event Saya';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Daftar event komunitas yang tersedia dan status pendaftaran Anda.';
    }

    /**
     * Permissions required to view this screen.
     */
    public function permission(): ?iterable
    {
        // Self-service screen for members, no explicit permission required
        return null;
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
            Layout::block(
                Layout::table('availableEvents', [
                    TD::make('judul', 'Judul Event'),
                    TD::make('lokasi', 'Lokasi'),
                    TD::make('tanggal_mulai', 'Tanggal Mulai')->render(fn (Event $e) => $e->tanggal_mulai?->format('Y-m-d H:i') ?? '-'),
                    TD::make('kuota', 'Sisa Kuota')->render(function (Event $e) {
                        $sisa = max(0, $e->kuota - $e->registrations_count);
                        return "{$sisa} dari {$e->kuota}";
                    }),
                    TD::make('action', 'Aksi')->alignRight()->render(function (Event $e) {
                        $userId = Auth::id();
                        $isRegistered = EventRegistration::where('event_id', $e->id)->where('user_id', $userId)->exists();
                        $isFull = $e->registrations_count >= $e->kuota;

                        if ($isRegistered) {
                            return '<span class="badge bg-success">Sudah Terdaftar</span>';
                        }

                        if ($isFull) {
                            return '<span class="badge bg-danger">Kuota Penuh</span>';
                        }

                        return Button::make('Daftar Event')
                            ->method('register')
                            ->parameters(['event_id' => $e->id])
                            ->type(Color::PRIMARY)
                            ->icon('bs.check-circle');
                    }),
                ])
            )
            ->title('Event Komunitas Tersedia')
            ->description('Daftar event yang sedang dibuka untuk pendaftaran.'),

            Layout::block(
                Layout::table('myRegistrations', [
                    TD::make('event.judul', 'Judul Event')->render(fn (EventRegistration $reg) => $reg->event?->judul ?? '-'),
                    TD::make('event.lokasi', 'Lokasi')->render(fn (EventRegistration $reg) => $reg->event?->lokasi ?? '-'),
                    TD::make('event.tanggal_mulai', 'Waktu Pelaksanaan')->render(fn (EventRegistration $reg) => $reg->event?->tanggal_mulai?->format('Y-m-d H:i') ?? '-'),
                    TD::make('created_at', 'Waktu Mendaftar')->render(fn (EventRegistration $reg) => $reg->created_at?->format('Y-m-d H:i') ?? '-'),
                    TD::make('checkin_at', 'Status Check-in')->render(fn (EventRegistration $reg) => $reg->checkin_at ? '<span class="badge bg-success">Checked In ('.$reg->checkin_at->format('Y-m-d H:i').')</span>' : '<span class="badge bg-secondary">Belum Check-in</span>'),
                    TD::make('action', 'Aksi')->alignRight()->render(function (EventRegistration $reg) {
                        return Button::make('Batal Daftar')
                            ->method('cancelRegistration')
                            ->parameters(['registration_id' => $reg->id])
                            ->confirm('Apakah Anda yakin ingin membatalkan pendaftaran event ini?')
                            ->type(Color::DANGER)
                            ->icon('bs.x-circle');
                    }),
                ])
            )
            ->title('Pendaftaran Event Saya')
            ->description('Riwayat event yang telah Anda daftarkan.'),
        ];
    }

    /**
     * Register current user to event.
     */
    public function register(Request $request)
    {
        $eventId = (int) $request->get('event_id');
        $userId = Auth::id();

        $event = Event::withCount('registrations')->findOrFail($eventId);

        if ($event->status !== 'published') {
            Toast::error('Event ini tidak sedang menerima pendaftaran.');
            return redirect()->back();
        }

        // Cek apakah sudah pernah daftar
        $alreadyRegistered = EventRegistration::where('event_id', $eventId)
            ->where('user_id', $userId)
            ->exists();

        if ($alreadyRegistered) {
            Toast::warning('Anda sudah terdaftar pada event ini.');
            return redirect()->back();
        }

        // Cek kuota
        if ($event->registrations_count >= $event->kuota) {
            Toast::error('Pendaftaran gagal. Kuota event sudah penuh.');
            return redirect()->back();
        }

        EventRegistration::create([
            'event_id' => $eventId,
            'user_id' => $userId,
        ]);

        Toast::success('Berhasil mendaftar ke event!');

        return redirect()->back();
    }

    /**
     * Cancel event registration.
     */
    public function cancelRegistration(Request $request)
    {
        $registrationId = (int) $request->get('registration_id');
        $userId = Auth::id();

        $registration = EventRegistration::where('id', $registrationId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $registration->delete();

        Toast::info('Pendaftaran event berhasil dibatalkan.');

        return redirect()->back();
    }
}
