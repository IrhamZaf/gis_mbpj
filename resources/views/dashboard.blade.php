@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Dashboard GIS')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/apex-charts/apex-charts.scss',
'resources/assets/vendor/libs/leaflet/leaflet.scss',
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/scss/pages/gis-mbpj.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/apex-charts/apexcharts.js',
'resources/assets/vendor/libs/leaflet/leaflet.js',
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'
])
@endsection

@section('page-script')
@vite('resources/assets/js/gis-dashboard.js')
@endsection

@section('content')
<div class="row g-4 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="card card-border-shadow-primary h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2 pb-1">
          <div class="avatar me-2">
            <span class="avatar-initial rounded bg-label-primary"><i class="ti tabler-hole ti-md"></i></span>
          </div>
          <h4 class="ms-1 mb-0" id="stat-sinkholes">{{ $stats['sinkholes'] }}</h4>
        </div>
        <p class="mb-1">Jumlah Sinkhole</p>
        <p class="mb-0 text-muted small">
          <span class="text-success me-1"><i class="ti tabler-trending-up ti-xs"></i> 100%</span>
          <span class="text-muted">Since last month</span>
        </p>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card card-border-shadow-info h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2 pb-1">
          <div class="avatar me-2">
            <span class="avatar-initial rounded bg-label-info"><i class="ti tabler-mountain ti-md"></i></span>
          </div>
          <h4 class="ms-1 mb-0" id="stat-slopes">{{ $stats['active_slopes'] }}</h4>
        </div>
        <p class="mb-1">Insiden Cerun</p>
        <p class="mb-0 text-muted small">
          <span class="text-info me-1"><i class="ti tabler-activity ti-xs"></i> Aktif</span>
          <span class="text-muted">Real-time tracking</span>
        </p>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card card-border-shadow-danger h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2 pb-1">
          <div class="avatar me-2">
            <span class="avatar-initial rounded bg-label-danger"><i class="ti tabler-alert-triangle ti-md"></i></span>
          </div>
          <h4 class="ms-1 mb-0 text-danger" id="stat-critical">{{ $stats['critical_locations'] }}</h4>
        </div>
        <p class="mb-1">Lokasi Kritikal</p>
        <p class="mb-0 text-muted small">
          <span class="text-danger me-1"><i class="ti tabler-flame ti-xs"></i> Perlu Tindakan</span>
        </p>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card card-border-shadow-warning h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2 pb-1">
          <div class="avatar me-2">
            <span class="avatar-initial rounded bg-label-warning"><i class="ti tabler-clock ti-md"></i></span>
          </div>
          <h4 class="ms-1 mb-0 text-warning" id="stat-pending">{{ $stats['pending_reports'] }}</h4>
        </div>
        <p class="mb-1">Laporan Pending</p>
        <p class="mb-0 text-muted small">
          <span class="text-muted">Menunggu semakan</span>
        </p>
      </div>
    </div>
  </div>
</div>

@if(auth()->user()->isEngineer() || auth()->user()->isAdmin())
<div class="row g-4 mb-4">
  <div class="col-12">
    <div class="card border-primary border shadow-none">
      <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3 py-4">
        <div class="d-flex align-items-start gap-3">
          <div class="avatar avatar-md">
            <span class="avatar-initial rounded bg-label-primary"><i class="ti tabler-files ti-md"></i></span>
          </div>
          <div>
            <h5 class="mb-1">Fail laporan surveyor</h5>
            <p class="mb-0 text-muted small">
              {{ number_format($surveyForEngineer['files']) }} fail dimuat naik ·
              {{ number_format($surveyForEngineer['pending_review']) }} hantaran menunggu semakan jurutera
            </p>
          </div>
        </div>
        <a href="{{ route('engineer.index', ['tab' => 'files']) }}" class="btn btn-primary">
          <i class="ti tabler-list-details me-1"></i>Lihat senarai fail
        </a>
      </div>
    </div>
  </div>
</div>
@endif

<div class="row g-4 mb-4">
  <div class="col-xl-9">
    <div class="card gis-map-card overflow-hidden">
      <div class="card-header d-flex justify-content-between align-items-center border-bottom">
        <div>
          <h5 class="mb-0">Peta Ringkasan PJ</h5>
          <span class="badge bg-label-success mt-1"><i class="ti tabler-circle-filled ti-xs me-1 pulse"></i> Live Monitoring</span>
        </div>
        <a href="{{ route('gis.map') }}" class="btn btn-sm btn-primary">
          <i class="ti tabler-maximize me-1"></i>Peta Penuh
        </a>
      </div>
      <div class="card-body p-0">
        <div id="gisDashboardMiniMap" class="gis-leaflet-mini"></div>
      </div>
    </div>
  </div>
  <div class="col-xl-3">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="mb-0">Notifikasi kritikal</h5>
      </div>
      <div class="card-body p-0">
        <ul class="list-group list-group-flush">
          @forelse ($notifications as $note)
          @php $d = $note->data; @endphp
          <li class="list-group-item">
            <a href="{{ $d['url'] ?? '#' }}" class="stretched-link text-body">{{ $d['title'] ?? 'GIS' }}</a>
            <small class="d-block text-body-secondary">{{ \Illuminate\Support\Str::limit($d['message'] ?? '', 80) }}</small>
          </li>
          @empty
          <li class="list-group-item text-muted small">Tiada notifikasi.</li>
          @endforelse
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  <div class="col-xl-8">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Trend insiden (12 bulan)</h5>
      </div>
      <div class="card-body">
        <div id="gisIncidentTrendChart"></div>
      </div>
    </div>
  </div>
  <div class="col-xl-4">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="mb-0">Ringkasan risiko</h5>
      </div>
      <div class="card-body">
        <p class="mb-2"><span class="badge bg-success me-2">Selamat</span> Kawasan stabil</p>
        <p class="mb-2"><span class="badge bg-warning me-2">Pemantauan</span> Perlu pemantauan berkala</p>
        <p class="mb-0"><span class="badge bg-danger me-2">Kritikal</span> Tindakan segera</p>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header d-flex justify-content-between">
    <h5 class="mb-0">Insiden terkini</h5>
    <a href="{{ route('incidents.index') }}" class="btn btn-sm btn-label-primary">Lihat semua</a>
  </div>
  <div class="card-datatable table-responsive">
    <table class="table" id="gisDashboardIncidentsTable">
      <thead>
        <tr>
          <th>No. Rujukan</th>
          <th>Kategori</th>
          <th>Risiko</th>
          <th>Status</th>
          <th>Tarikh</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($recentIncidents as $inc)
        <tr>
          <td><a href="{{ route('incidents.show', $inc) }}">{{ $inc->incident_number }}</a></td>
          <td>{{ $inc->category === 'sinkhole' ? 'Sinkhole' : 'Cerun' }}</td>
          <td>{{ $inc->risk_level }}</td>
          <td>{{ $inc->status }}</td>
          <td>{{ $inc->date_reported->format('d/m/Y') }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<script>
  window.gisDashboardStats = @json($stats);
</script>
@endsection
