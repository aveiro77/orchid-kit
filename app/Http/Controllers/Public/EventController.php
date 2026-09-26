<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    /**
     * Display a listing of published events.
     */
    public function index(Request $request): View
    {
        $search = $request->query('q');

        $query = Event::where('status', 'published')
            ->withCount('registrations');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $events = $query->orderBy('tanggal_mulai', 'asc')->paginate(12)->withQueryString();

        return view('public.events.index', compact('events', 'search'));
    }

    /**
     * Display the specified published event detail.
     */
    public function show(Event $event): View
    {
        abort_unless($event->status === 'published', 404);

        $event->loadCount('registrations');

        return view('public.events.show', compact('event'));
    }
}
