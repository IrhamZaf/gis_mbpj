@extends('layouts/layoutMaster')

@section('title', 'Tambah Insiden')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/leaflet/leaflet.scss',
'resources/assets/vendor/scss/pages/gis-mbpj.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/leaflet/leaflet.js'
])
@endsection

@section('page-script')
@vite(['resources/assets/js/gis-incident-form.js'])
@endsection

@section('content')
<div class="card mb-4">
  <div class="card-header"><h5 class="mb-0">Maklumat insiden</h5></div>
  <div class="card-body">
    <form action="{{ route('incidents.store') }}" method="POST" enctype="multipart/form-data" id="gisIncidentForm">
      @csrf
      <div class="row g-4">
        <div class="col-md-6">
          <label class="form-label">Kategori</label>
          <select name="category" class="form-select" required>
            <option value="sinkhole">Sinkhole</option>
            <option value="slope">Cerun / tanah runtuh</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Tarikh dilaporkan</label>
          <input type="text" class="form-control" name="date_reported" id="date_reported" required value="{{ old('date_reported', now()->format('Y-m-d')) }}" />
        </div>
        <div class="col-md-6">
          <label class="form-label">Latitud</label>
          <input type="number" step="any" class="form-control" name="latitude" id="latitude" required value="{{ old('latitude', '3.104') }}" />
        </div>
        <div class="col-md-6">
          <label class="form-label">Longitud</label>
          <input type="number" step="any" class="form-control" name="longitude" id="longitude" required value="{{ old('longitude', '101.606') }}" />
        </div>
        <div class="col-12">
          <label class="form-label">Alamat ringkas</label>
          <input type="text" class="form-control" name="address" value="{{ old('address') }}" />
        </div>
        <div class="col-md-4">
          <label class="form-label">Tahap risiko</label>
          <select name="risk_level" class="form-select" required>
            <option value="selamat">Selamat</option>
            <option value="pemantauan">Pemantauan</option>
            <option value="kritikal">Kritikal</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Status</label>
          <select name="status" class="form-select" required>
            <option value="baru_dilaporkan">Baru dilaporkan</option>
            <option value="dalam_siasatan">Dalam siasatan</option>
            <option value="dalam_pemantauan">Dalam pemantauan</option>
            <option value="tindakan_diperlukan">Tindakan diperlukan</option>
            <option value="selesai">Selesai</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Jurutera ditugaskan</label>
          <select name="assigned_engineer" class="form-select">
            <option value="">—</option>
            @foreach ($engineers as $eng)
            <option value="{{ $eng->id }}">{{ $eng->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Keterangan</label>
          <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
        </div>
        <div class="col-12">
          <label class="form-label">Pilih lokasi pada peta</label>
          <div id="gisIncidentMapPicker" class="gis-leaflet-mini border rounded"></div>
          <small class="text-muted">Klik pada peta untuk menetapkan koordinat.</small>
        </div>
        <div class="col-md-4">
          <label class="form-label">Gambar tapak</label>
          <input type="file" name="images[]" class="form-control" multiple accept="image/*" />
        </div>
        <div class="col-md-4">
          <label class="form-label">Video drone</label>
          <input type="file" name="videos[]" class="form-control" multiple accept="video/mp4,video/webm" />
        </div>
        <div class="col-md-4">
          <label class="form-label">PDF laporan</label>
          <input type="file" name="reports[]" class="form-control" multiple accept="application/pdf" />
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary">Simpan</button>
          <a href="{{ route('incidents.index') }}" class="btn btn-label-secondary">Batal</a>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection
