<div>
  <div class="card mb-6">
    <div class="card-header"><h5 class="mb-0">Cipta Laporan Baru</h5></div>
    <div class="card-body">
      <form wire:submit="submit">
        <div class="row">
          <div class="col-md-6" data-survey-form-fields>
            @if ($surveyMetadataApplied)
            <div class="alert alert-info py-2 small mb-3">Kategori (CNâ†’Cerun, SHâ†’Sinkhole), tajuk, keterangan dan lokasi diisi automatik daripada fail survei.</div>
            @endif
            <div class="mb-4">
              <label class="form-label">Kategori Laporan <span class="text-danger">*</span></label>
              <select wire:model="category_id" class="form-select @error('category_id') is-invalid @enderror">
                <option value="0">-- Pilih Kategori --</option>
                @foreach ($categories as $cat)
                  <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
              </select>
              @error('category_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="mb-4">
              <label class="form-label">Tajuk Laporan <span class="text-danger">*</span></label>
              <input wire:model="title" type="text" class="form-control @error('title') is-invalid @enderror" placeholder="cth: Sinkhole di Jalan SS2/24" />
              @error('title')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="mb-4">
              <label class="form-label">Keterangan <span class="text-danger">*</span></label>
              <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="4" placeholder="Keterangan ringkas laporan..."></textarea>
              @error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="mb-4">
              <label class="form-label">Nama Lokasi</label>
              <input wire:model="location_name" type="text" class="form-control" placeholder="cth: ATC5A, Persimpangan Jalan SS2/24" />
              <small class="text-muted">Boleh diisi automatik daripada carian atau fail survei.</small>
            </div>
          </div>

          <div class="col-md-6" data-survey-map-card>
            <label class="form-label">Lokasi GIS</label>
            <div class="gis-location-search-wrap mb-2 position-relative">
              <div class="input-group">
                <span class="input-group-text"><i class="ti tabler-search"></i></span>
                <input type="text" id="gis-location-search" class="form-control" placeholder="Cari alamat di Petaling Jaya..." autocomplete="off" />
                <button type="button" id="gis-location-search-btn" class="btn btn-outline-primary">Cari</button>
              </div>
              <ul id="gis-location-results" class="list-group position-absolute w-100 shadow-sm d-none gis-location-results"></ul>
            </div>
            <small class="text-muted d-block mb-2">Cari lokasi di atas, atau klik peta untuk tandakan tapak.</small>
            <div id="gis-map" style="height:400px;border-radius:8px;border:1px solid var(--bs-border-color);" wire:ignore></div>
            <div class="row g-2 mt-2 mb-2">
              <div class="col-6">
                <label class="form-label small mb-1">Latitud</label>
                <input id="report-anchor-lat" wire:model="latitude" type="number" step="0.0000001" class="form-control form-control-sm @error('latitude') is-invalid @enderror" readonly placeholder="—" />
                @error('latitude')<span class="invalid-feedback d-block small">{{ $message }}</span>@enderror
              </div>
              <div class="col-6">
                <label class="form-label small mb-1">Longitud</label>
                <input id="report-anchor-lng" wire:model="longitude" type="number" step="0.0000001" class="form-control form-control-sm @error('longitude') is-invalid @enderror" readonly placeholder="—" />
                @error('longitude')<span class="invalid-feedback d-block small">{{ $message }}</span>@enderror
              </div>
            </div>
            <div id="survey-anchor-warning" class="alert alert-warning py-2 small mt-2 mb-0 d-none">
              Sila cari lokasi atau klik peta untuk tetapkan tapak sebelum muat naik fail survei CSV/TXT.
            </div>
            <div class="d-flex flex-wrap gap-2 mt-2 small text-muted">
              <span><span class="gis-legend-risk-area d-inline-block align-middle"></span> Kawasan risiko</span>
              <span><span style="width:10px;height:10px;border-radius:50%;background:#3498db;display:inline-block;"></span> Titik 3D</span>
              <span><span style="width:10px;height:10px;border-radius:50%;background:#d73027;display:inline-block;"></span> Sesaran tinggi 2D</span>
            </div>
          </div>
        </div>

        <div class="mb-4 mt-4">
          <label class="form-label">Dokumen Survei <small class="text-muted">(3 jenis â€” Maks 20MB setiap fail)</small></label>
          <ul class="small text-muted mb-2 ps-3">
            <li>Kategori: <strong>CN</strong> â†’ Cerun, <strong>SH</strong> â†’ Sinkhole (cth. CN1, SH1 dalam nama fail).</li>
            <li>Nama lokasi: kod tempat <strong>ATC5A</strong>, <strong>ATC5B</strong>, dll. (bukan CN/SH).</li>
            <li>Tajuk dan keterangan juga boleh diisi automatik daripada nama/kandungan fail.</li>
            <li><strong>3D</strong> â€” CSV dengan lajur <code>Xb, Yb, Zb</code></li>
            <li><strong>2D</strong> â€” CSV/TXT dengan lajur <code>DAY, POINT, Xb, Yb</code> (sesaran mengikut hari)</li>
            <li><strong>1D</strong> â€” PDF laporan graf & analisis</li>
          </ul>
          <input wire:model="files" data-survey-files type="file" class="form-control" multiple accept=".csv,.txt,.pdf,.jpg,.jpeg,.png" />
          <div wire:loading wire:target="files" class="text-muted small mt-1">Memuat naik fail...</div>
          @error('files.*')<span class="text-danger small">{{ $message }}</span>@enderror
          @if (count($files))
            <ul class="list-group mt-2">
              @foreach ($files as $i => $f)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <span>{{ $f->getClientOriginalName() }} <small class="text-muted">({{ number_format($f->getSize()/1024, 1) }} KB)</small></span>
                  <button type="button" wire:click="removeFile({{ $i }})" class="btn btn-sm btn-icon btn-text-danger"><i class="ti tabler-x"></i></button>
                </li>
              @endforeach
            </ul>
          @endif
        </div>

        <div class="d-flex gap-2">
          <button type="button" wire:click="saveDraft" class="btn btn-outline-warning"><i class="ti tabler-device-floppy me-1"></i>Simpan Draf</button>
          <button type="submit" class="btn btn-primary"><i class="ti tabler-send me-1"></i>Hantar Laporan</button>
        </div>
      </form>
    </div>
  </div>

  @push('styles')
  <style>
    .gis-legend-risk-area {
      width: 14px; height: 14px;
      border: 2px solid #e74c3c;
      background: rgba(231, 76, 60, 0.15);
    }
    .gis-location-search-wrap {
      z-index: 1050;
    }
    .gis-location-results {
      top: calc(100% + 4px);
      left: 0;
      right: 0;
      z-index: 1060;
      max-height: 240px;
      overflow-y: auto;
      overflow-x: hidden;
      background-color: #fff;
      border: 1px solid var(--bs-border-color, #d9dee3);
      border-radius: 0.375rem;
      box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.18);
    }
    .gis-location-results .list-group-item {
      background-color: #fff;
      color: var(--bs-body-color, #566a7f);
      border-color: var(--bs-border-color, #d9dee3);
    }
    .gis-location-results .list-group-item-action:hover,
    .gis-location-results .list-group-item-action:focus {
      background-color: #f5f5f9;
      color: var(--bs-body-color, #566a7f);
    }
  </style>
  @endpush

  @push('scripts')
  @vite(['resources/js/gis-map-picker.js', 'resources/js/gis-survey-preview.js'])
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const livewire = @this;
      window.surveyReportLivewire = livewire;
      window.gisMapPicker.initMapPicker({
        mapElementId: 'gis-map',
        initialLat: {{ $latitude ?? 'null' }},
        initialLng: {{ $longitude ?? 'null' }},
        initialZoom: {{ $latitude ? 16 : 14 }},
        initialGisData: @json($gis_data),
        initialLocationLabel: @json($location_name ?: ''),
        onCoordinatesChange(lat, lng, label) { livewire.call('setCoordinates', lat, lng, label); },
        onGisDataChange(data) { livewire.call('setGisData', data); },
      });
    });
  </script>
  @endpush
</div>
