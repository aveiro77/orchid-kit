@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <a href="{{ route('events.index') }}" class="btn btn-link text-decoration-none p-0 mb-3 text-secondary">
                &larr; Kembali ke Daftar Event
            </a>

            @php
                $sisaKuota = max(0, $event->kuota - $event->registrations_count);
            @endphp

            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge {{ $sisaKuota > 0 ? 'bg-success' : 'bg-danger' }} bg-opacity-10 {{ $sisaKuota > 0 ? 'text-success' : 'text-danger' }} px-3 py-2 rounded-pill small fw-semibold">
                            {{ $sisaKuota > 0 ? "Sisa Kuota: {$sisaKuota} dari {$event->kuota}" : 'Kuota Penuh' }}
                        </span>
                    </div>

                    <h1 class="h2 fw-bold text-dark mb-4">{{ $event->judul }}</h1>

                    <div class="bg-light p-3 rounded-3 mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <span class="text-muted d-block small">Waktu Pelaksanaan</span>
                                <strong class="text-dark">📅 {{ $event->tanggal_mulai?->format('d M Y, H:i') }} WIB - {{ $event->tanggal_selesai?->format('H:i') }} WIB</strong>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted d-block small">Lokasi</span>
                                <strong class="text-dark">📍 {{ $event->lokasi ?? 'Online / TBD' }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h2 class="h5 fw-bold text-dark mb-3">Deskripsi Event</h2>
                        <div class="text-secondary leading-relaxed">
                            {!! $event->deskripsi ?? '<p class="text-muted">Tidak ada deskripsi rinci untuk event ini.</p>' !!}
                        </div>
                    </div>

                    <div class="border-top pt-4 text-center">
                        <p class="text-muted small mb-3">Untuk mendaftar event ini, Anda harus masuk ke akun anggota KPMI terlebih dahulu.</p>
                        <a href="{{ route('platform.my_events') }}" class="btn btn-primary btn-lg px-4 fw-semibold">
                            Daftar Sekarang
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
