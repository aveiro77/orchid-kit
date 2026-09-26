<div class="bg-white rounded shadow-sm p-4 mb-3">
    <h5 class="fw-bold mb-3">🗓️ Event Terdekat</h5>
    @if(isset($event_terdekat) && $event_terdekat)
        <div class="border-start border-4 border-primary ps-3">
            <h6 class="fw-bold text-dark mb-1">{{ $event_terdekat->judul }}</h6>
            <p class="text-muted small mb-1">
                📍 <strong>Lokasi:</strong> {{ $event_terdekat->lokasi ?? 'Online / TBD' }}
            </p>
            <p class="text-muted small mb-1">
                🕒 <strong>Mulai:</strong> {{ $event_terdekat->tanggal_mulai?->format('d M Y H:i') }}
            </p>
            <p class="text-muted small mb-0">
                👥 <strong>Kuota:</strong> {{ $event_terdekat->kuota ?? 'Tidak terbatas' }}
            </p>
        </div>
    @else
        <p class="text-muted mb-0 small">Belum ada event mendatang yang dijadwalkan.</p>
    @endif
</div>
