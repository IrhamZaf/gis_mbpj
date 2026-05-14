@extends('layouts/layoutMaster')

@section('title', $incident->incident_number)

@section('vendor-style')
@vite(['resources/assets/vendor/libs/leaflet/leaflet.scss', 'resources/assets/vendor/scss/pages/gis-mbpj.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/leaflet/leaflet.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/gis-incident-show.js'])
@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between gap-2 mb-4">
  <div>
    <h4 class="mb-1">{{ $incident->incident_number }}</h4>
    <p class="text-muted mb-0">{{ $incident->address }}</p>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a class="btn btn-label-primary" href="{{ route('reports.pdf', $incident) }}">Muat turun PDF</a>
    @if(auth()->id() === $incident->reported_by || auth()->user()->isAdmin())
    <a class="btn btn-primary" href="{{ route('incidents.edit', $incident) }}">Edit</a>
    @endif
  </div>
</div>

<div class="row g-4">
  <div class="col-xl-8">
    <div class="card mb-4">
      <div class="card-header"><h5 class="mb-0">Ringkasan</h5></div>
      <div class="card-body row g-3">
        <div class="col-sm-6"><strong>Kategori</strong><br>{{ $incident->category === 'sinkhole' ? 'Sinkhole' : 'Cerun' }}</div>
        <div class="col-sm-6"><strong>Tarikh</strong><br>{{ $incident->date_reported->format('d/m/Y') }}</div>
        <div class="col-sm-6"><strong>Risiko</strong><br><span class="badge bg-label-warning">{{ $incident->risk_level }}</span></div>
        <div class="col-sm-6"><strong>Status</strong><br>{{ $incident->status }}</div>
        <div class="col-12"><strong>Keterangan</strong><br>{{ $incident->description ?: '—' }}</div>
      </div>
    </div>

    @php
      $before = $incident->media->where('upload_phase', 'before')->where('type', 'image')->first();
      $after = $incident->media->where('upload_phase', 'after')->where('type', 'image')->first();
    @endphp
    @if($before && $after)
    <div class="card mb-4">
      <div class="card-header"><h5 class="mb-0">Perbandingan drone (sebelum / selepas)</h5></div>
      <div class="card-body">
        <div class="position-relative" style="max-height: 360px; overflow: hidden;">
          <img src="{{ asset('storage/'.$after->file_path) }}" alt="selepas" class="w-100 rounded" id="gisAfterImg" />
          <div class="position-absolute top-0 start-0 h-100 overflow-hidden rounded" id="gisBeforeClip" style="width: 50%;">
            <img src="{{ asset('storage/'.$before->file_path) }}" alt="sebelum" class="h-100 rounded" style="object-fit: cover; max-width: none; width: auto; min-width: 100%;" id="gisBeforeImg" />
          </div>
        </div>
        <label class="form-label mt-3" for="gisCompareRange">Leret perbandingan</label>
        <input type="range" min="0" max="100" value="50" class="form-range gis-compare-range" id="gisCompareRange" />
      </div>
    </div>
    @endif

    <div class="card">
      <div class="card-header"><h5 class="mb-0">Garis masa</h5></div>
      <ul class="timeline mb-0 p-4">
        @foreach ($incident->timeline as $ev)
        <li class="timeline-item pb-4">
          <span class="timeline-indicator timeline-indicator-primary"><i class="icon-base ti tabler-circle-check"></i></span>
          <div class="timeline-event">
            <div class="timeline-header mb-2">
              <h6 class="mb-0">{{ $ev->action }}</h6>
              <small class="text-muted">{{ $ev->created_at->format('d/m/Y H:i') }}</small>
            </div>
            <p class="mb-0 small">{{ $ev->description }}</p>
            @if($ev->performer)
            <small class="text-muted">Oleh {{ $ev->performer->name }}</small>
            @endif
          </div>
        </li>
        @endforeach
      </ul>
    </div>
  </div>

  <div class="col-xl-4">
    <div class="card mb-4">
      <div class="card-header"><h5 class="mb-0">Lokasi</h5></div>
      <div class="card-body p-0">
        <div id="gisIncidentShowMap" class="gis-leaflet-mini"></div>
      </div>
    </div>
    <div class="card">
      <div class="card-header"><h5 class="mb-0">Media</h5></div>
      <div class="list-group list-group-flush">
        @forelse ($incident->media as $m)
        <a class="list-group-item list-group-item-action" href="{{ asset('storage/'.$m->file_path) }}" target="_blank">
          {{ $m->caption }} <small class="text-muted">({{ $m->type }}, {{ $m->upload_phase }})</small>
        </a>
        @empty
        <div class="list-group-item text-muted">Tiada media.</div>
        @endforelse
      </div>
    </div>
  </div>
</div>

<script>
  window.gisIncidentShow = @json([
    'lat' => $incident->latitude,
    'lng' => $incident->longitude,
  ]);
</script>
@endsection
