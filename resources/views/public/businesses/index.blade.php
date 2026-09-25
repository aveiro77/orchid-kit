@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 fw-bold text-dark mb-2">Direktori Usaha</h1>
            <p class="text-muted">Temukan berbagai usaha dan bisnis dari sesama anggota KPMI Pekalongan.</p>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('businesses.index') }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="q" class="form-label fw-semibold text-secondary">Cari Usaha</label>
                    <input type="text" class="form-control" id="q" name="q" value="{{ $search }}" placeholder="Cari nama usaha...">
                </div>
                <div class="col-md-5">
                    <label for="kategori" class="form-label fw-semibold text-secondary">Kategori</label>
                    <select class="form-select" id="kategori" name="kategori">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (string)$categoryId === (string)$category->id ? 'selected' : '' }}>
                                {{ $category->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        Cari
                    </button>
                    @if($search || $categoryId)
                        <a href="{{ route('businesses.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Businesses Grid -->
    <div class="row g-4 mb-4">
        @forelse($businesses as $business)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill small">
                                {{ $business->category?->nama ?? 'Umum' }}
                            </span>
                        </div>

                        <h2 class="h5 font-weight-bold text-dark mb-2">{{ $business->nama_usaha }}</h2>

                        <p class="text-muted small mb-2">
                            👤 Pemilik: <a href="{{ route('members.show', $business->user->id) }}" class="text-decoration-none fw-semibold text-dark">{{ $business->user?->name }}</a>
                        </p>

                        @if($business->deskripsi)
                            <p class="card-text text-secondary small mb-3 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ $business->deskripsi }}
                            </p>
                        @else
                            <div class="flex-grow-1"></div>
                        @endif

                        @if($business->alamat)
                            <p class="text-muted small mb-2">
                                📍 {{ $business->alamat }}
                            </p>
                        @endif

                        <div class="mt-auto pt-3 border-top">
                            <a href="{{ route('businesses.show', $business->id) }}" class="btn btn-outline-primary btn-sm w-100 fw-semibold">
                                Lihat Detail Usaha
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="p-5 bg-white rounded shadow-sm">
                    <p class="text-muted fs-5 mb-2">Tidak ada usaha yang ditemukan.</p>
                    <p class="text-muted small">Coba ubah kata kunci atau kategori pencarian Anda.</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $businesses->links() }}
    </div>
</div>
@endsection
