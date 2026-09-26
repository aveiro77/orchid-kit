@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 fw-bold text-dark mb-2">Direktori Anggota</h1>
            <p class="text-muted">Temukan dan terhubung dengan sesama anggota komunitas pengusaha muslim.</p>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('members.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="nama" class="form-label fw-semibold text-secondary">Nama Anggota</label>
                    <input type="text" class="form-control" id="nama" name="nama" value="{{ $nama }}" placeholder="Cari nama anggota...">
                </div>
                <div class="col-md-3">
                    <label for="kota" class="form-label fw-semibold text-secondary">Kota</label>
                    <input type="text" class="form-control" id="kota" name="kota" value="{{ $kota }}" placeholder="Cari kota...">
                </div>
                <div class="col-md-2">
                    <label for="peran" class="form-label fw-semibold text-secondary">Peran Profesi</label>
                    <select class="form-select" id="peran" name="peran">
                        <option value="">Semua Peran</option>
                        @foreach($professionalRoles as $role)
                            <option value="{{ $role->id }}" {{ (string)$roleId === (string)$role->id ? 'selected' : '' }}>
                                {{ $role->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="skill" class="form-label fw-semibold text-secondary">Skill / Layanan</label>
                    <select class="form-select" id="skill" name="skill">
                        <option value="">Semua Skill</option>
                        @foreach($skills as $sk)
                            <option value="{{ $sk->id }}" {{ (string)$skillId === (string)$sk->id ? 'selected' : '' }}>
                                {{ $sk->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        Cari
                    </button>
                    @if($nama || $kota || $skillId || $roleId)
                        <a href="{{ route('members.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Members Grid -->
    <div class="row g-4 mb-4">
        @forelse($members as $member)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0 me-3">
                                @if($member->foto)
                                    <img src="{{ asset($member->foto) }}" alt="{{ $member->name }}" class="rounded-circle object-fit-cover" width="60" height="60">
                                @else
                                    <div class="rounded-circle bg-secondary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 60px; height: 60px;">
                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <h2 class="h6 font-weight-bold text-dark text-truncate mb-1">{{ $member->name }}</h2>
                                @if($member->kota)
                                    <p class="text-muted small mb-0">
                                        📍 {{ $member->kota }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        @if($member->bio)
                            <p class="card-text text-secondary small mb-3 flex-grow-1 text-multiline-truncate" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ $member->bio }}
                            </p>
                        @else
                            <div class="flex-grow-1"></div>
                        @endif

                        <div class="mt-auto pt-3 border-top">
                            <a href="{{ route('members.show', $member->id) }}" class="btn btn-outline-primary btn-sm w-100 fw-semibold">
                                Lihat Profil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="p-5 bg-white rounded shadow-sm">
                    <p class="text-muted fs-5 mb-2">Tidak ada anggota yang ditemukan.</p>
                    <p class="text-muted small">Coba ubah kata kunci pencarian Anda.</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $members->links() }}
    </div>
</div>
@endsection
