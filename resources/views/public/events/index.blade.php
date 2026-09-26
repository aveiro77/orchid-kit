@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 fw-bold text-dark mb-2">Event Komunitas</h1>
            <p class="text-muted">Ikuti berbagai kegiatan, seminar, workshop, dan silaturahmi komunitas KPMI Pekalongan.</p>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('events.index') }}" class="row g-3 align-items-end">
                <div class="col-md-10">
                    <label for="q" class="form-label fw-semibold text-secondary">Cari Event</label>
                    <input type="text" class="form-control" id="q" name="q" value="{{ $search }}" placeholder="Cari judul, lokasi, atau deskripsi event...">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        Cari
                    </button>
                    @if($search)
                        <a href="{{ route('events.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Events Grid -->
    <div class="row g-4 mb-4">
        @forelse($events as $event)
            @php
                $sisaKuota = max(0, $event->kuota - $event->registrations_count);
            @endphp
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge {{ $sisaKuota > 0 ? 'bg-success' : 'bg-danger' }} bg-opacity-10 {{ $sisaKuota > 0 ? 'text-success' : 'text-danger' }} px-3 py-2 rounded-pill small fw-semibold">
                                {{ $sisaKuota > 0 ? "Sisa Kuota: {$sisaKuota}" : 'Kuota Penuh' }}
                            </span>
                        </div>

                        <h2 class="h5 font-weight-bold text-dark mb-2">{{ $event->judul }}</h2>

                        <p class="text-muted small mb-1">
                            📅 {{ $event->tanggal_mulai?->format('d M Y, H:i') }} WIB
                        </p>

                        <p class="text-muted small mb-3">
                            📍 {{ $event->lokasi ?? 'Online / TBD' }}
                        </p>

                        @if($event->deskripsi)
                            <p class="card-text text-secondary small mb-3 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                {!! strip_tags($event->deskripsi) !!}
                            </p>
                        @else
                            <div class="flex-grow-1"></div>
                        @endif

                        <div class="mt-auto pt-3 border-top">
                            <a href="{{ route('events.show', $event->id) }}" class="btn btn-outline-primary btn-sm w-100 fw-semibold">
                                Lihat Detail Event
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="p-5 bg-white rounded shadow-sm">
                    <p class="text-muted fs-5 mb-2">Tidak ada event yang ditemukan.</p>
                    <p class="text-muted small">Coba ubah kata kunci pencarian Anda.</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $events->links() }}
    </div>
</div>
@endsection
