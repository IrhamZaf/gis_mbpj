<div>
  <div class="card mb-6">
    <div class="card-header"><h5 class="mb-0">Edit Laporan: {{ $report->report_number }}</h5></div>
    <div class="card-body">
      <form wire:submit="submit">
        <div class="row">
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
              <input wire:model="title" type="text" class="form-control @error('title') is-invalid @enderror" />
              @error('title')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="mb-4">
              <label class="form-label">Keterangan <span class="text-danger">*</span></label>
              <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="4"></textarea>
              @error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="mb-4">
              <label class="form-label">Nama Lokasi</label>
              <input wire:model="location_name" type="text" class="form-control" />
            </div>
            <div class="row mb-4">
              <div class="col-6"><label class="form-label">Latitud</label><input wire:model="latitude" type="number" step="0.0000001" class="form-control" readonly /></div>
              <div class="col-6"><label class="form-label">Longitud</label><input wire:model="longitude" type="number" step="0.0000001" class="form-control" readonly /></div>
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Lokasi GIS</label>
            <div id="gis-map-edit" style="height:400px;border-radius:8px;border:1px solid var(--bs-border-color);" wire:ignore></div>
          </div>
        </div>

        <!-- Existing attachments -->
        @if ($attachments->count())
        <div class="mb-4">
          <label class="form-label">Lampiran Sedia Ada</label>
          <ul class="list-group">
            @foreach ($attachments as $att)
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span><i class="icon-base {{ $att->file_icon }} me-2"></i>{{ $att->file_name }} <small class="text-muted">({{ $att->file_size_formatted }})</small></span>
              <button type="button" wire:click="deleteAttachment({{ $att->id }})" wire:confirm="Padam fail ini?" class="btn btn-sm btn-icon btn-text-danger"><i class="ti tabler-trash"></i></button>
            </li>
            @endforeach
          </ul>
        </div>
        @endif

        <!-- New file upload -->
        <div class="mb-4">
          <label class="form-label">Tambah Lampiran Baru</label>
          <input wire:model="newFiles" type="file" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png,.gif,.txt,.csv,.xls,.xlsx" />
          <div wire:loading wire:target="newFiles" class="text-muted small mt-1">Memuat naik...</div>
        </div>

        <div class="d-flex gap-2">
          <button type="button" wire:click="saveDraft" class="btn btn-outline-warning"><i class="ti tabler-device-floppy me-1"></i>Simpan Draf</button>
          <button type="submit" class="btn btn-primary"><i class="ti tabler-send me-1"></i>Hantar Laporan</button>
          <a href="{{ route('surveyor.reports') }}" class="btn btn-outline-secondary">Batal</a>
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
      const lat = {{ $latitude ?? 3.1073 }};
      const lng = {{ $longitude ?? 101.6067 }};
      const map = L.map('gis-map-edit').setView([lat, lng], 15);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);

      let marker = L.marker([lat, lng]).addTo(map);
      const drawnItems = new L.FeatureGroup();
      map.addLayer(drawnItems);

      @if ($gis_data)
      L.geoJSON({!! json_encode($gis_data) !!}).eachLayer(l => drawnItems.addLayer(l));
      @endif

      const drawControl = new L.Control.Draw({ draw: { polygon: true, polyline: false, rectangle: true, circle: false, circlemarker: false, marker: false }, edit: { featureGroup: drawnItems } });
      map.addControl(drawControl);

      map.on('click', function(e) {
        if (marker) map.removeLayer(marker);
        marker = L.marker(e.latlng).addTo(map);
        @this.call('setCoordinates', e.latlng.lat, e.latlng.lng);
      });
      map.on(L.Draw.Event.CREATED, function(e) { drawnItems.addLayer(e.layer); @this.call('setGisData', drawnItems.toGeoJSON()); });
      setTimeout(() => map.invalidateSize(), 300);
    });
  </script>
  @endpush
</div>
