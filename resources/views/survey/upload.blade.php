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
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Borang hantaran surveyor</h5>
        @isset($parent)
        <p class="small text-muted mb-0 mt-1">Versi baharu berdasarkan hantaran #{{ $parent->id }} (v{{ $parent->version }}).</p>
        @endisset
      </div>
      <div class="card-body">
        <form action="{{ route('survey.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          @isset($parent)
          <input type="hidden" name="parent_survey_id" value="{{ $parent->id }}" />
          @endisset

          <div class="mb-3">
            <label class="form-label">Pautan insiden</label>
            <select name="incident_id" class="form-select" id="survey_incident_id">
              <option value="">— Tiada —</option>
              @php
                $selId = $prefillIncidentId ?? ($parent->incident_id ?? null);
              @endphp
              @foreach ($incidents as $inc)
              <option value="{{ $inc->id }}" @selected((string) $selId === (string) $inc->id)>{{ $inc->incident_number }} — {{ $inc->address }}</option>
              @endforeach
            </select>
            <div class="form-text">Pautkan survey kepada insiden MBPJ untuk semakan jurutera.</div>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label">Tarikh survey</label>
              <input type="date" name="survey_date" class="form-control" required value="{{ old('survey_date', now()->format('Y-m-d')) }}" />
            </div>
            <div class="col-md-6">
              <label class="form-label">Jenis survey</label>
              <input type="text" name="survey_type" class="form-control" placeholder="Contoh: Tapak, drone, geoteknik" value="{{ old('survey_type') }}" />
            </div>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label">Nama organisasi surveyor</label>
              <input type="text" name="vendor_name" class="form-control" value="{{ old('vendor_name') }}" placeholder="Syarikat / organisasi (jika berkenaan)" />
            </div>
            <div class="col-md-6">
              <label class="form-label">Nama surveyor di tapak</label>
              <input type="text" name="surveyor_name" class="form-control" value="{{ old('surveyor_name') }}" />
            </div>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-md-4">
              <label class="form-label">Lat (WGS84)</label>
              <input type="number" step="any" name="latitude" class="form-control" id="survey_lat" value="{{ old('latitude') }}" />
            </div>
            <div class="col-md-4">
              <label class="form-label">Lng (WGS84)</label>
              <input type="number" step="any" name="longitude" class="form-control" id="survey_lng" value="{{ old('longitude') }}" />
            </div>
            <div class="col-md-4">
              <label class="form-label">Nota CRS input</label>
              <input type="text" name="coordinate_crs" class="form-control" placeholder="EPSG:4326" value="{{ old('coordinate_crs', 'EPSG:4326') }}" />
            </div>
          </div>
          <p class="small text-muted">Koordinat borang disimpan sebagai WGS84 (metadata CRS input direkod).</p>

          <hr class="my-4" />
          <h6 class="mb-3">Data GIS</h6>
          <div class="mb-3">
            <label class="form-label">GeoJSON</label>
            <input type="file" name="geojson_file" class="form-control" accept=".json,.geojson,application/geo+json,application/json" />
          </div>
          <div class="mb-3">
            <label class="form-label">KML / KMZ</label>
            <input type="file" name="kml_file" class="form-control" accept=".kml,.kmz,.xml" />
          </div>
          <div class="mb-3">
            <label class="form-label">Shapefile (ZIP)</label>
            <input type="file" name="shape_zip" class="form-control" accept=".zip" />
          </div>
          <div class="mb-3">
            <label class="form-label">GeoTIFF</label>
            <input type="file" name="geotiff" class="form-control" accept=".tif,.tiff" />
          </div>
          <div class="mb-3">
            <label class="form-label">Data kontur / lain-lain GIS</label>
            <input type="file" name="contour_file" class="form-control" />
          </div>

          <hr class="my-4" />
          <h6 class="mb-3">Dokumen & media</h6>
          <div class="mb-3">
            <label class="form-label">Laporan survey (PDF)</label>
            <input type="file" name="pdf_report" class="form-control" accept="application/pdf" />
          </div>
          <div class="mb-3">
            <label class="form-label">Laporan geoteknik (PDF)</label>
            <input type="file" name="pdf_geotech" class="form-control" accept="application/pdf" />
          </div>
          <div class="mb-3">
            <label class="form-label">TXT data bacaan survey</label>
            <input type="file" name="survey_readings_txt" class="form-control" accept=".txt,text/plain" />
            <div class="form-text">Log bacaan alat, koordinat ringkas, atau eksport teks (bukan fail GeoJSON — gunakan medan GeoJSON di atas).</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Excel data analisis (.xlsx / .xls)</label>
            <input type="file" name="excel_files[]" class="form-control" accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel" multiple />
            <div class="form-text">Boleh pilih lebih daripada satu fail sekaligus (contoh: jadual analisis MBPJ).</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Laporan pemeriksaan tapak (PDF)</label>
            <input type="file" name="pdf_inspection" class="form-control" accept="application/pdf" />
          </div>
          <div class="mb-3">
            <label class="form-label">Imej drone</label>
            <input type="file" name="drone_image" class="form-control" accept="image/*" />
          </div>
          <div class="mb-3">
            <label class="form-label">Video drone</label>
            <input type="file" name="drone_video" class="form-control" accept="video/mp4,video/webm,video/quicktime" />
          </div>
          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label">Gambar sebelum</label>
              <input type="file" name="photo_before" class="form-control" accept="image/*" />
            </div>
            <div class="col-md-6">
              <label class="form-label">Gambar selepas</label>
              <input type="file" name="photo_after" class="form-control" accept="image/*" />
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Nota ringkas</label>
            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Catatan teknikal</label>
            <textarea name="technical_notes" class="form-control" rows="3">{{ old('technical_notes') }}</textarea>
          </div>

          <button type="submit" class="btn btn-primary">Hantar</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header"><h5 class="mb-0">Pratonton GeoJSON (mini)</h5></div>
      <div class="card-body p-0">
        <div id="gisSurveyPreviewMap" class="gis-leaflet-mini"></div>
        <p class="small text-muted p-3 mb-0">Pratonton automatik apabila fail GeoJSON dimuat.</p>
      </div>
    </div>
  </div>
</div>
@endsection
