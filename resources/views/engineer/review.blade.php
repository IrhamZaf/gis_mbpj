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
      <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">{{ $incident->incident_number }}</h5>
        <a href="{{ route('incidents.show', $incident) }}" class="btn btn-sm btn-label-secondary">Halaman penuh</a>
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
  </div>
  <div class="col-lg-5">
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
