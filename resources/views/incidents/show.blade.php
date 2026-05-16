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
    @if(auth()->user()->isEngineer() || auth()->user()->isAdmin())
    <a class="btn btn-label-secondary" href="{{ route('engineer.review', $incident) }}">Semakan jurutera</a>
    @endif
    @if(auth()->user()->isEngineer() || auth()->user()->isAdmin() || auth()->id() === $incident->reported_by)
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

    <div class="card mb-4">
      <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0">Laporan survey (surveyor dilantik)</h5>
        @if(auth()->user()->isEngineer() || auth()->user()->isAdmin())
        <span class="small text-muted">Semakan penuh &amp; lulus/tolak: halaman semakan jurutera</span>
        @endif
      </div>
      <div class="card-body">
        @forelse ($incident->surveys as $survey)
        <div class="border rounded p-3 mb-3 @if($loop->last) mb-0 @endif">
          <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
            <div>
              <strong>Versi {{ $survey->version }}</strong>
              <span class="badge bg-label-secondary ms-1">{{ $survey->reviewStatusLabel() }}</span>
            </div>
            <span class="small text-muted">{{ $survey->survey_date->format('d/m/Y') }}</span>
          </div>
          <p class="small mb-1"><strong>Organisasi:</strong> {{ $survey->vendor_name ?: '—' }} · <strong>Surveyor tapak:</strong> {{ $survey->surveyor_name ?: '—' }}</p>
          <p class="small mb-1"><strong>Akaun hantar:</strong> {{ $survey->surveyor?->name ?? '—' }} ({{ $survey->surveyor?->email ?? '—' }})</p>
          <p class="small mb-1"><strong>Jenis survey:</strong> {{ $survey->survey_type ?: '—' }}</p>
          @if($survey->gps_coordinates)
          <p class="small mb-1"><strong>GPS:</strong> {{ $survey->gps_coordinates['lat'] ?? '—' }}, {{ $survey->gps_coordinates['lng'] ?? '—' }}</p>
          @endif
          @if($survey->notes)
          <p class="small mb-1"><strong>Nota:</strong> {{ $survey->notes }}</p>
          @endif
          @if($survey->technical_notes)
          <p class="small mb-2"><strong>Catatan teknikal:</strong> {{ $survey->technical_notes }}</p>
          @endif

          @if($survey->uploads->isNotEmpty())
          <p class="small fw-semibold mb-1">Fail dimuat naik</p>
          <ul class="list-unstyled small mb-0">
            @foreach ($survey->uploads as $up)
            <li class="d-flex flex-wrap justify-content-between align-items-center gap-2 py-1 border-bottom">
              <span>{{ $up->labelMs() }} — {{ \Illuminate\Support\Str::limit($up->original_name ?? basename($up->file_path), 56) }}</span>
              <span class="d-flex flex-wrap gap-1 justify-content-end flex-shrink-0">
                @if($up->isPreviewableInBrowser())
                <a href="{{ route('incidents.survey.view', [$incident, $up]) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary">Lihat</a>
                @endif
                <a href="{{ route('incidents.survey.download', [$incident, $up]) }}" class="btn btn-sm btn-label-primary">Muat turun</a>
              </span>
            </li>
            @endforeach
          </ul>
          @else
          <p class="small text-muted mb-0">Tiada rekod fail berasingan (data mungkin hanya GeoJSON dalam rekod).</p>
          @endif
        </div>
        @empty
        <p class="text-muted small mb-0">Tiada laporan survey dihantar untuk insiden ini.</p>
        @endforelse
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
        @php
          $actionLabel = match ($ev->action) {
              'survey_vendor_approved', 'survey_surveyor_approved' => 'Laporan surveyor diluluskan',
              'survey_vendor_rejected', 'survey_surveyor_rejected' => 'Laporan surveyor ditolak',
              default => $ev->action,
          };
          $desc = str_ireplace(
              ['survey vendor', 'vendor/surveyor', 'kepada vendor'],
              ['surveyor', 'surveyor', 'kepada surveyor'],
              $ev->description
          );
        @endphp
        <li class="timeline-item pb-4">
          <span class="timeline-indicator timeline-indicator-primary"><i class="icon-base ti tabler-circle-check"></i></span>
          <div class="timeline-event">
            <div class="timeline-header mb-2">
              <h6 class="mb-0">{{ $actionLabel }}</h6>
              <small class="text-muted">{{ $ev->created_at->format('d/m/Y H:i') }}</small>
            </div>
            <p class="mb-0 small">{{ $desc }}</p>
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
