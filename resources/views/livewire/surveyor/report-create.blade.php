<div>
  <div class="card mb-6">
    <div class="card-header"><h5 class="mb-0">Cipta Laporan Baru</h5></div>
    <div class="card-body">
      <form wire:submit="submit">
        <div class="row">
          <!-- Left column: form fields -->
          <div class="col-md-6">
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
              <input wire:model="location_name" type="text" class="form-control" placeholder="cth: Persimpangan Jalan SS2/24" />
            </div>
            <div class="row mb-4">
              <div class="col-6">
                <label class="form-label">Latitud</label>
                <input wire:model="latitude" type="number" step="0.0000001" class="form-control" readonly />
              </div>
              <div class="col-6">
                <label class="form-label">Longitud</label>
                <input wire:model="longitude" type="number" step="0.0000001" class="form-control" readonly />
              </div>
            </div>
          </div>

          <!-- Right column: GIS map -->
          <div class="col-md-6">
            <label class="form-label">Lokasi GIS <small class="text-muted">(Klik pada peta untuk tandakan lokasi)</small></label>
            <div id="gis-map" style="height:400px;border-radius:8px;border:1px solid var(--bs-border-color);" wire:ignore></div>
          </div>
        </div>

        <!-- File Upload -->
        <div class="mb-4 mt-4">
          <label class="form-label">Lampiran Dokumen <small class="text-muted">(PDF, Gambar, TXT, Excel — Maks 10MB setiap fail)</small></label>
          <input wire:model="files" type="file" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png,.gif,.txt,.csv,.xls,.xlsx" />
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
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" />
  @endpush

  @push('scripts')
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const map = L.map('gis-map').setView([3.1073, 101.6067], 14); // MBPJ default
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
      }).addTo(map);

      let marker = null;
      const drawnItems = new L.FeatureGroup();
      map.addLayer(drawnItems);

      const drawControl = new L.Control.Draw({
        draw: { polygon: true, polyline: false, rectangle: true, circle: false, circlemarker: false, marker: false },
        edit: { featureGroup: drawnItems }
      });
      map.addControl(drawControl);

      // Click to set marker
      map.on('click', function(e) {
        if (marker) map.removeLayer(marker);
        marker = L.marker(e.latlng).addTo(map);
        @this.call('setCoordinates', e.latlng.lat, e.latlng.lng);
      });

      // Draw polygon/rectangle for risk area
      map.on(L.Draw.Event.CREATED, function(e) {
        drawnItems.addLayer(e.layer);
        const geoJson = drawnItems.toGeoJSON();
        @this.call('setGisData', geoJson);
      });

      map.on(L.Draw.Event.DELETED, function() {
        const geoJson = drawnItems.toGeoJSON();
        @this.call('setGisData', geoJson.features.length ? geoJson : null);
      });

      setTimeout(() => map.invalidateSize(), 300);
    });
  </script>
  @endpush
</div>
