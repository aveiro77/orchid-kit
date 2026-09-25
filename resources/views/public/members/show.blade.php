@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <a href="{{ route('members.index') }}" class="btn btn-link text-decoration-none p-0 text-secondary">
            &larr; Kembali ke Direktori Anggota
        </a>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm text-center p-4">
                <div class="mb-3 d-flex justify-content-center">
                    @if($member->foto)
                        <img src="{{ asset($member->foto) }}" alt="{{ $member->name }}" class="rounded-circle object-fit-cover shadow-sm" width="140" height="140">
                    @else
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold fs-1" style="width: 140px; height: 140px;">
                            {{ strtoupper(substr($member->name, 0, 1)) }}
                        </div>
                    @endif
                </div>

                <h1 class="h4 fw-bold mb-1 text-dark">{{ $member->name }}</h1>

                @if($member->kota)
                    <p class="text-muted mb-3">📍 {{ $member->kota }}</p>
                @endif

                @if($member->nomor_wa)
                    <div class="d-grid mb-3">
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $member->nomor_wa) }}" target="_blank" rel="noopener noreferrer" class="btn btn-success fw-semibold">
                            💬 Hubungi via WhatsApp
                        </a>
                    </div>
                @endif

                <div class="d-flex justify-content-center gap-2 mt-2">
                    @if($member->linkedin)
                        <a href="{{ $member->linkedin }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm" title="LinkedIn">
                            LinkedIn
                        </a>
                    @endif

                    @if($member->website)
                        <a href="{{ $member->website }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary btn-sm" title="Website">
                            Website
                        </a>
                    @endif

                    @if($member->instagram)
                        @php
                            $instaUrl = str_starts_with($member->instagram, 'http')
                                ? $member->instagram
                                : 'https://instagram.com/' . ltrim($member->instagram, '@');
                        @endphp
                        <a href="{{ $instaUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-danger btn-sm" title="Instagram">
                            Instagram
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3">Tentang {{ $member->name }}</h2>
                @if($member->bio)
                    <p class="text-secondary style-bio" style="white-space: pre-line;">{{ $member->bio }}</p>
                @else
                    <p class="text-muted italic">Anggota ini belum menambahkan informasi bio.</p>
                @endif
            </div>

            <div class="card border-0 shadow-sm p-4">
                <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3">Informasi Kontak</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <span class="text-muted d-block small">Email</span>
                        <span class="fw-medium text-dark">{{ $member->email }}</span>
                    </div>
                    @if($member->nomor_wa)
                        <div class="col-md-6">
                            <span class="text-muted d-block small">Nomor WhatsApp</span>
                            <span class="fw-medium text-dark">{{ $member->nomor_wa }}</span>
                        </div>
                    @endif
                    @if($member->kota)
                        <div class="col-md-6">
                            <span class="text-muted d-block small">Kota</span>
                            <span class="fw-medium text-dark">{{ $member->kota }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
