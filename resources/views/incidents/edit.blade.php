@extends('layouts/layoutMaster')

@section('title', 'Kemas kini Insiden')

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
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">{{ $incident->incident_number }}</h5>
    <a href="{{ route('incidents.show', $incident) }}" class="btn btn-sm btn-label-secondary">Lihat</a>
  </div>
  <div class="card-body">
    <form action="{{ route('incidents.update', $incident) }}" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')
      <div class="row g-4">
        <div class="col-md-6">
          <label class="form-label">No. rujukan</label>
          <input type="text" name="incident_number" class="form-control @error('incident_number') is-invalid @enderror"
            maxlength="96" required value="{{ old('incident_number', $incident->incident_number) }}"
            pattern="[A-Za-z0-9\-]+" title="Huruf, nombor dan sempang (-) sahaja" />
          <div class="form-text">Contoh: CN1-ATC5A, SH2-ZONE3. Mesti unik dalam sistem.</div>
          @error('incident_number')
          <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="col-md-6">
          <label class="form-label">Kategori</label>
          <select name="category" class="form-select" required>
            <option value="sinkhole" @selected($incident->category === 'sinkhole')>Sinkhole</option>
            <option value="slope" @selected($incident->category === 'slope')>Cerun</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Tarikh dilaporkan</label>
          <input type="text" class="form-control" name="date_reported" id="date_reported" required
            value="{{ old('date_reported', $incident->date_reported->format('Y-m-d')) }}" />
        </div>
        <div class="col-md-6">
          <label class="form-label">Latitud</label>
          <input type="number" step="any" class="form-control" name="latitude" id="latitude" required
            value="{{ old('latitude', $incident->latitude) }}" />
        </div>
        <div class="col-md-6">
          <label class="form-label">Longitud</label>
          <input type="number" step="any" class="form-control" name="longitude" id="longitude" required
            value="{{ old('longitude', $incident->longitude) }}" />
        </div>
        <div class="col-12">
          <label class="form-label">Alamat</label>
          <input type="text" class="form-control" name="address" value="{{ old('address', $incident->address) }}" />
        </div>
        <div class="col-md-4">
          <label class="form-label">Risiko</label>
          <select name="risk_level" class="form-select" required>
            <option value="selamat" @selected($incident->risk_level === 'selamat')>Selamat</option>
            <option value="pemantauan" @selected($incident->risk_level === 'pemantauan')>Pemantauan</option>
            <option value="kritikal" @selected($incident->risk_level === 'kritikal')>Kritikal</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Status</label>
          <select name="status" class="form-select" required>
            @foreach (['baru_dilaporkan','dalam_siasatan','dalam_pemantauan','tindakan_diperlukan','selesai'] as $st)
            <option value="{{ $st }}" @selected($incident->status === $st)>{{ $st }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Jurutera</label>
          <select name="assigned_engineer" class="form-select">
            <option value="">—</option>
            @foreach ($engineers as $eng)
            <option value="{{ $eng->id }}" @selected($incident->assigned_engineer == $eng->id)>{{ $eng->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Keterangan</label>
          <textarea name="description" class="form-control" rows="4">{{ old('description', $incident->description) }}</textarea>
        </div>
        <div class="col-12">
          <label class="form-label">Peta</label>
          <div id="gisIncidentMapPicker" class="gis-leaflet-mini border rounded"></div>
        </div>
        <div class="col-md-4">
          <label class="form-label">Tambah gambar</label>
          <input type="file" name="images[]" class="form-control" multiple accept="image/*" />
        </div>
        <div class="col-md-4">
          <label class="form-label">Tambah video</label>
          <input type="file" name="videos[]" class="form-control" multiple accept="video/mp4,video/webm" />
        </div>
        <div class="col-md-4">
          <label class="form-label">Tambah PDF</label>
          <input type="file" name="reports[]" class="form-control" multiple accept="application/pdf" />
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary">Kemas kini</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection
