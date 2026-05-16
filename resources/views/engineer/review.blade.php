@extends('layouts/layoutMaster')

@section('title', 'Semakan Jurutera')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/leaflet/leaflet.scss', 'resources/assets/vendor/scss/pages/gis-mbpj.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/leaflet/leaflet.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/gis-engineer-review.js'])
@endsection

@section('content')
<div class="row g-4">
  <div class="col-lg-7">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between flex-wrap gap-2">
        <h5 class="mb-0">{{ $incident->incident_number }}</h5>
        <div class="d-flex flex-wrap gap-2">
          <a href="{{ route('engineer.index', ['tab' => 'files']) }}" class="btn btn-sm btn-label-primary">Senarai fail surveyor</a>
          <a href="{{ route('incidents.show', $incident) }}" class="btn btn-sm btn-label-secondary">Halaman penuh</a>
        </div>
      </div>
      <div class="card-body">
        <p class="mb-2"><strong>Risiko:</strong> {{ $incident->risk_level }} · <strong>Status:</strong> {{ $incident->status }}</p>
        <p class="mb-0">{{ $incident->description }}</p>
      </div>
    </div>
    <div class="card mb-4">
      <div class="card-header"><h6 class="mb-0">Lokasi</h6></div>
      <div class="card-body p-0">
        <div id="gisEngineerReviewMap" class="gis-leaflet-mini"></div>
      </div>
    </div>
    @php
      $before = $incident->media->where('upload_phase', 'before')->where('type', 'image')->first();
      $after = $incident->media->where('upload_phase', 'after')->where('type', 'image')->first();
    @endphp
    @if($before && $after)
    <div class="card mb-4">
      <div class="card-header"><h6 class="mb-0">Sebelum / selepas</h6></div>
      <div class="card-body">
        <div class="position-relative" style="max-height: 280px; overflow: hidden;">
          <img src="{{ asset('storage/'.$after->file_path) }}" alt="selepas" class="w-100 rounded" />
          <div class="position-absolute top-0 start-0 h-100 overflow-hidden rounded" id="gisEngBeforeClip" style="width: 50%;">
            <img src="{{ asset('storage/'.$before->file_path) }}" alt="sebelum" class="h-100 rounded" style="object-fit: cover; width: auto; min-width: 100%; max-width: none;" />
          </div>
        </div>
        <input type="range" min="0" max="100" value="50" class="form-range mt-3" id="gisEngCompareRange" />
      </div>
    </div>
    @endif
    <div class="card">
      <div class="card-header"><h6 class="mb-0">Garis masa</h6></div>
      <ul class="list-group list-group-flush">
        @foreach ($incident->timeline as $ev)
        <li class="list-group-item small">
          <strong>{{ $ev->action }}</strong> — {{ $ev->created_at->format('d/m/Y H:i') }}<br>{{ $ev->description }}
        </li>
        @endforeach
      </ul>
    </div>

    <div class="card mt-4">
      <div class="card-header"><h6 class="mb-0">Semakan hantaran surveyor</h6></div>
      <div class="card-body">
        @forelse ($incident->surveys as $survey)
        <div class="border rounded p-3 mb-3">
          <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
            <div>
              <strong>Versi {{ $survey->version }}</strong>
              <span class="badge bg-label-info ms-1">{{ $survey->reviewStatusLabel() }}</span>
            </div>
            <div class="small text-muted">{{ $survey->survey_date->format('d/m/Y') }}</div>
          </div>
          <p class="small mb-1"><strong>Organisasi:</strong> {{ $survey->vendor_name ?: '—' }} · <strong>Surveyor:</strong> {{ $survey->surveyor_name ?: $survey->surveyor?->name }}</p>
          <p class="small mb-1"><strong>Jenis:</strong> {{ $survey->survey_type ?: '—' }}</p>
          @if($survey->gps_coordinates)
          <p class="small mb-1"><strong>GPS:</strong> {{ $survey->gps_coordinates['lat'] ?? '—' }}, {{ $survey->gps_coordinates['lng'] ?? '—' }}</p>
          @endif
          @if($survey->technical_notes)
          <p class="small mb-2"><strong>Catatan teknikal:</strong> {{ $survey->technical_notes }}</p>
          @endif

          @if($survey->uploads->isNotEmpty())
          <p class="small fw-semibold mb-1">Fail dimuat naik</p>
          <ul class="list-unstyled small mb-0">
            @foreach ($survey->uploads as $up)
            <li class="d-flex flex-wrap justify-content-between align-items-center gap-2 py-1 border-bottom">
              <span>{{ $up->labelMs() }} — {{ \Illuminate\Support\Str::limit($up->original_name ?? basename($up->file_path), 40) }}</span>
              <span class="d-flex flex-wrap gap-1 justify-content-end flex-shrink-0">
                @if($up->isPreviewableInBrowser())
                <a href="{{ route('engineer.survey.view', $up) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary">Lihat</a>
                @endif
                <a href="{{ route('engineer.survey.download', $up) }}" class="btn btn-sm btn-label-primary">Muat turun</a>
              </span>
            </li>
            @endforeach
          </ul>
          @else
          <p class="small text-muted mb-0">Tiada rekod fail berasingan (data lama / hanya GeoJSON dalam rekod).</p>
          @endif

          @if($survey->review_status === \App\Models\SurveyData::REVIEW_PENDING)
          <div class="row g-2 mt-3">
            <div class="col-md-6">
              <form method="POST" action="{{ route('engineer.survey.approve', $survey) }}" class="d-grid">
                @csrf
                <button type="submit" class="btn btn-sm btn-success">Lulus laporan survey</button>
              </form>
            </div>
            <div class="col-md-6">
              <form method="POST" action="{{ route('engineer.survey.reject', $survey) }}">
                @csrf
                <label class="form-label small">Tolak dengan sebab</label>
                <textarea name="survey_reject_notes" class="form-control form-control-sm mb-2" rows="2" required></textarea>
                <button type="submit" class="btn btn-sm btn-outline-danger w-100">Tolak laporan survey</button>
              </form>
            </div>
          </div>
          @endif
        </div>
        @empty
        <p class="text-muted small mb-0">Tiada data surveyor untuk insiden ini.</p>
        @endforelse
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card mb-4">
      <div class="card-header"><h6 class="mb-0">Minta survey tambahan</h6></div>
      <div class="card-body">
        <form method="POST" action="{{ route('engineer.survey.request_additional', $incident) }}">
          @csrf
          <div class="mb-3">
            <label class="form-label">Arahan / skop tambahan</label>
            <textarea name="additional_survey_message" class="form-control" rows="4" required placeholder="Contoh: Sila ulang ukur kontur dan kemukakan laporan geoteknik dikemas kini."></textarea>
          </div>
          <button type="submit" class="btn btn-outline-primary w-100">Hantar permintaan kepada surveyor</button>
        </form>
      </div>
    </div>
    <div class="card mb-4">
      <div class="card-header"><h6 class="mb-0">Luluskan</h6></div>
      <div class="card-body">
        <form method="POST" action="{{ route('engineer.approve', $incident) }}">
          @csrf
          <div class="mb-3">
            <label class="form-label">Penilaian risiko</label>
            <textarea name="risk_assessment" class="form-control" rows="3" required></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Cadangan</label>
            <textarea name="recommendation" class="form-control" rows="2"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Nota</label>
            <textarea name="notes" class="form-control" rows="2"></textarea>
          </div>
          <button type="submit" class="btn btn-success w-100">Sahkan & lulus</button>
        </form>
      </div>
    </div>
    <div class="card">
      <div class="card-header"><h6 class="mb-0">Tolak / minta maklumat</h6></div>
      <div class="card-body">
        <form method="POST" action="{{ route('engineer.reject', $incident) }}">
          @csrf
          <div class="mb-3">
            <label class="form-label">Catatan</label>
            <textarea name="notes" class="form-control" rows="3" required></textarea>
          </div>
          <button type="submit" class="btn btn-outline-danger w-100">Hantar maklum balas</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  window.gisEngineerReviewMap = @json(['lat' => $incident->latitude, 'lng' => $incident->longitude]);
</script>
@endsection
