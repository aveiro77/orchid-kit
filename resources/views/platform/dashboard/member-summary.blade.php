<div class="row g-4">
    <!-- Profil & Skill -->
    <div class="col-12 col-lg-5">
        <div class="bg-white rounded shadow-sm p-4 mb-4">
            <h5 class="fw-bold mb-3 border-bottom pb-2">👤 Profil Saya</h5>
            <div class="d-flex align-items-center mb-3">
                <div class="flex-shrink-0 me-3">
                    @if($user?->foto)
                        <img src="{{ asset($user->foto) }}" class="rounded-circle object-fit-cover" width="60" height="60" alt="{{ $user->name }}">
                    @else
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 60px; height: 60px;">
                            {{ strtoupper(substr($user?->name ?? 'A', 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0">{{ $user?->name }}</h6>
                    <small class="text-muted">{{ $user?->email }}</small><br>
                    <span class="badge {{ $user?->status_aktif ? 'bg-success' : 'bg-danger' }}">
                        {{ $user?->status_aktif ? 'Status: Aktif' : 'Status: Non-Aktif' }}
                    </span>
                </div>
            </div>

            <p class="text-muted small mb-2">📍 <strong>Kota:</strong> {{ $user?->kota ?? '-' }}</p>
            <p class="text-muted small mb-2">📱 <strong>WA:</strong> {{ $user?->nomor_wa ?? '-' }}</p>
            @if($user?->bio)
                <p class="text-secondary small mb-0 border-top pt-2"><em>{{ $user->bio }}</em></p>
            @endif
        </div>

        <div class="bg-white rounded shadow-sm p-4">
            <h5 class="fw-bold mb-3 border-bottom pb-2">💡 Skill & Layanan</h5>
            @forelse($skills as $sk)
                <span class="badge bg-primary bg-opacity-10 text-primary me-1 mb-2 px-2 py-1 fs-6 fw-normal">
                    {{ $sk->nama }}
                </span>
            @empty
                <p class="text-muted small mb-0">Belum ada skill yang ditambahkan.</p>
            @endforelse
        </div>
    </div>

    <!-- Usaha, Peluang, Event -->
    <div class="col-12 col-lg-7">
        <!-- Usaha Aktif -->
        <div class="bg-white rounded shadow-sm p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <h5 class="fw-bold mb-0">🏢 Usaha Saya (Aktif)</h5>
                <a href="{{ route('platform.my_businesses') }}" class="btn btn-sm btn-outline-primary">Kelola</a>
            </div>
            @forelse($usaha_aktif as $b)
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <div>
                        <strong class="text-dark">{{ $b->nama_usaha }}</strong>
                        <div class="text-muted small">{{ $b->category?->nama ?? 'Umum' }} • {{ $b->alamat ?? 'Pekalongan' }}</div>
                    </div>
                    <span class="badge bg-success">Aktif</span>
                </div>
            @empty
                <p class="text-muted small mb-0">Belum ada usaha aktif.</p>
            @endforelse
        </div>

        <!-- Peluang Dibuat -->
        <div class="bg-white rounded shadow-sm p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <h5 class="fw-bold mb-0">📢 Peluang Dibuat</h5>
                <a href="{{ route('platform.my_opportunities') }}" class="btn btn-sm btn-outline-primary">Kelola</a>
            </div>
            @forelse($peluang_dibuat as $opp)
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <div>
                        <strong class="text-dark">[{{ strtoupper($opp->tipe) }}] {{ $opp->judul }}</strong>
                        <div class="text-muted small">Dibuat: {{ $opp->created_at?->format('d M Y') }}</div>
                    </div>
                    <span class="badge bg-info text-dark">{{ ucfirst($opp->status) }}</span>
                </div>
            @empty
                <p class="text-muted small mb-0">Belum ada peluang yang dibuat.</p>
            @endforelse
        </div>

        <!-- Event Diikuti -->
        <div class="bg-white rounded shadow-sm p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <h5 class="fw-bold mb-0">🎟️ Event Yang Diikuti</h5>
                <a href="{{ route('platform.my_events') }}" class="btn btn-sm btn-outline-primary">Lihat</a>
            </div>
            @forelse($event_diikuti as $reg)
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <div>
                        <strong class="text-dark">{{ $reg->event?->judul ?? 'Event' }}</strong>
                        <div class="text-muted small">📍 {{ $reg->event?->lokasi ?? '-' }} • 🕒 {{ $reg->event?->tanggal_mulai?->format('d M Y H:i') }}</div>
                    </div>
                    <span class="badge bg-primary">Terdaftar</span>
                </div>
            @empty
                <p class="text-muted small mb-0">Belum terdaftar pada event apapun.</p>
            @endforelse
        </div>
    </div>
</div>
