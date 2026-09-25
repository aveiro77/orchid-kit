@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <a href="{{ route('businesses.index') }}" class="btn btn-link text-decoration-none p-0 text-secondary">
            &larr; Kembali ke Direktori Usaha
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm p-4 mb-4">
                <div class="d-flex align-items-center mb-3">
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fs-6 fw-normal me-2">
                        {{ $business->category?->nama ?? 'Umum' }}
                    </span>
                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fs-6 fw-normal">
                        Usaha Aktif
                    </span>
                </div>

                <h1 class="h3 fw-bold mb-3 text-dark">{{ $business->nama_usaha }}</h1>

                <div class="border-top pt-3 mt-2">
                    <h2 class="h5 fw-bold text-dark mb-3">Deskripsi Usaha</h2>
                    @if($business->deskripsi)
                        <p class="text-secondary" style="white-space: pre-line;">{{ $business->deskripsi }}</p>
                    @else
                        <p class="text-muted italic">Belum ada deskripsi untuk usaha ini.</p>
                    @endif
                </div>

                @if($business->alamat)
                    <div class="border-top pt-3 mt-3">
                        <h2 class="h5 fw-bold text-dark mb-2">Alamat Usaha</h2>
                        <p class="text-secondary mb-0">📍 {{ $business->alamat }}</p>
                    </div>
                @endif

                @if($business->website)
                    <div class="border-top pt-3 mt-3">
                        <h2 class="h5 fw-bold text-dark mb-2">Website / Media Sosial</h2>
                        <p class="mb-0">
                            <a href="{{ str_starts_with($business->website, 'http') ? $business->website : 'https://' . $business->website }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm">
                                🌐 Kunjungi Website
                            </a>
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm p-4">
                <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3">Informasi Pemilik</h2>

                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0 me-3">
                        @if($business->user?->foto)
                            <img src="{{ asset($business->user->foto) }}" alt="{{ $business->user->name }}" class="rounded-circle object-fit-cover" width="60" height="60">
                        @else
                            <div class="rounded-circle bg-secondary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 60px; height: 60px;">
                                {{ strtoupper(substr($business->user?->name ?? 'A', 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <h3 class="h6 font-weight-bold text-dark mb-1">{{ $business->user?->name }}</h3>
                        @if($business->user?->kota)
                            <p class="text-muted small mb-0">📍 {{ $business->user->kota }}</p>
                        @endif
                    </div>
                </div>

                @if($business->user?->nomor_wa)
                    <div class="d-grid mb-3">
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $business->user->nomor_wa) }}" target="_blank" rel="noopener noreferrer" class="btn btn-success fw-semibold">
                            💬 Hubungi Pemilik via WA
                        </a>
                    </div>
                @endif

                <a href="{{ route('members.show', $business->user_id) }}" class="btn btn-outline-secondary btn-sm w-100 fw-semibold">
                    Lihat Profil Pemilik
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
