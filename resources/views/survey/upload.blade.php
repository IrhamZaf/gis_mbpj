@extends('layouts/layoutMaster')

@section('title', 'Muat Naik Survey')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/leaflet/leaflet.scss', 'resources/assets/vendor/scss/pages/gis-mbpj.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/leaflet/leaflet.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/gis-survey-upload.js'])
@endsection

@section('content')
<div class="row g-4">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header"><h5 class="mb-0">Borang upload</h5></div>
      <div class="card-body">
        <form action="{{ route('survey.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="mb-3">
            <label class="form-label">Pautan insiden (pilihan)</label>
            <select name="incident_id" class="form-select">
              <option value="">—</option>
              @foreach ($incidents as $inc)
              <option value="{{ $inc->id }}">{{ $inc->incident_number }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Tarikh survey</label>
            <input type="date" name="survey_date" class="form-control" required value="{{ now()->format('Y-m-d') }}" />
          </div>
          <div class="row g-2 mb-3">
            <div class="col">
              <label class="form-label">Lat</label>
              <input type="number" step="any" name="latitude" class="form-control" id="survey_lat" />
            </div>
            <div class="col">
              <label class="form-label">Lng</label>
              <input type="number" step="any" name="longitude" class="form-control" id="survey_lng" />
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">GeoJSON</label>
            <input type="file" name="geojson_file" class="form-control" accept=".json,.geojson,.txt" />
          </div>
          <div class="mb-3">
            <label class="form-label">KML</label>
            <input type="file" name="kml_file" class="form-control" accept=".kml,.kmz,.xml" />
          </div>
          <div class="mb-3">
            <label class="form-label">Shapefile (ZIP)</label>
            <input type="file" name="shape_zip" class="form-control" accept=".zip" />
          </div>
          <div class="mb-3">
            <label class="form-label">Imej drone</label>
            <input type="file" name="drone_image" class="form-control" accept="image/*" />
          </div>
          <div class="mb-3">
            <label class="form-label">PDF</label>
            <input type="file" name="pdf_report" class="form-control" accept="application/pdf" />
          </div>
          <div class="mb-3">
            <label class="form-label">Nota</label>
            <textarea name="notes" class="form-control" rows="3"></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header"><h5 class="mb-0">Pratonton GeoJSON (mini)</h5></div>
      <div class="card-body p-0">
        <div id="gisSurveyPreviewMap" class="gis-leaflet-mini"></div>
        <p class="small text-muted p-3 mb-0">Pratonton automatik apabila fail GeoJSON dimuat (pembangunan lanjut).</p>
      </div>
    </div>
  </div>
</div>
@endsection
