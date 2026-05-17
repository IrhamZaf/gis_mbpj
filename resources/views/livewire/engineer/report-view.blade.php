<div>
  <div class="row">
    <!-- Report Details -->
    <div class="col-md-6 mb-6">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between">
          <h5 class="mb-0">{{ $report->title }}</h5>
          {!! $report->status_badge !!}
        </div>
        <div class="card-body">
          <table class="table table-borderless">
            <tr><td class="fw-semibold" width="40%">No. Laporan</td><td><code>{{ $report->report_number }}</code></td></tr>
            <tr><td class="fw-semibold">Kategori</td><td>{{ $report->category->name ?? '-' }}</td></tr>
            <tr><td class="fw-semibold">Surveyor</td><td>{{ $report->user->name ?? '-' }}</td></tr>
            <tr><td class="fw-semibold">Lokasi</td><td>{{ $report->location_name ?? '-' }}</td></tr>
            <tr><td class="fw-semibold">Koordinat</td><td>{{ $report->latitude ?? '-' }}, {{ $report->longitude ?? '-' }}</td></tr>
            <tr><td class="fw-semibold">Tarikh Hantar</td><td>{{ $report->submitted_at?->format('d/m/Y H:i') ?? '-' }}</td></tr>
          </table>
          <hr>
          <h6 class="fw-semibold">Keterangan</h6>
          <p class="text-body">{{ $report->description ?? 'Tiada keterangan.' }}</p>
        </div>
      </div>
    </div>

    <!-- GIS Map -->
    <div class="col-md-6 mb-6">
      <div class="card h-100">
        <div class="card-header"><h5 class="mb-0"><i class="ti tabler-map me-2"></i>Peta GIS</h5></div>
        <div class="card-body p-0">
          <div id="report-map" style="height:450px;border-radius:0 0 8px 8px;" wire:ignore></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Attachments -->
  <div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="ti tabler-paperclip me-2"></i>Lampiran ({{ $report->attachments->count() }})</h5></div>
    <div class="card-body">
      @if ($report->attachments->count())
      <div class="table-responsive">
        <table class="table">
          <thead><tr><th>Fail</th><th>Jenis</th><th>Saiz</th><th>Muat Turun</th></tr></thead>
          <tbody>
            @foreach ($report->attachments as $att)
            <tr>
              <td><i class="icon-base {{ $att->file_icon }} me-2"></i>{{ $att->file_name }}</td>
              <td><small class="text-muted">{{ $att->file_type }}</small></td>
              <td>{{ $att->file_size_formatted }}</td>
              <td><a href="{{ asset('storage/' . $att->file_path) }}" class="btn btn-sm btn-outline-primary" download><i class="ti tabler-download me-1"></i>Muat Turun</a></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @else
      <p class="text-center text-muted py-4">Tiada lampiran.</p>
      @endif
    </div>
  </div>

  <div class="mt-4">
    <a href="{{ route('engineer.reports') }}" class="btn btn-outline-secondary"><i class="ti tabler-arrow-left me-1"></i>Kembali</a>
  </div>

  @push('styles')
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  @endpush
  @push('scripts')
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const lat = {{ $report->latitude ?? 3.1073 }};
      const lng = {{ $report->longitude ?? 101.6067 }};
      const map = L.map('report-map').setView([lat, lng], 16);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);

      @if ($report->latitude && $report->longitude)
      L.marker([lat, lng]).addTo(map).bindPopup('<strong>{{ $report->title }}</strong><br>{{ $report->location_name }}').openPopup();
      @endif

      @if ($report->gis_data)
      L.geoJSON({!! json_encode($report->gis_data) !!}, {
        style: { color: '#e74c3c', weight: 2, fillOpacity: 0.2 }
      }).addTo(map);
      @endif

      setTimeout(() => map.invalidateSize(), 300);
    });
  </script>
  @endpush
</div>
