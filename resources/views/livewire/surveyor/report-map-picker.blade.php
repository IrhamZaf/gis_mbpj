<div wire:ignore class="report-map-picker-root">
  <label class="form-label">Lokasi GIS</label>
  <div class="gis-location-search-wrap mb-2 position-relative">
    <div class="input-group">
      <span class="input-group-text"><i class="ti tabler-search"></i></span>
      <input type="text" id="gis-location-search" class="form-control" placeholder="Cari alamat di Petaling Jaya..." autocomplete="off" />
      <button type="button" id="gis-location-search-btn" class="btn btn-outline-primary">Cari</button>
    </div>
    <ul id="gis-location-results" class="list-group position-absolute w-100 shadow-sm d-none gis-location-results"></ul>
  </div>
  <small class="text-muted d-block mb-2">Cari lokasi atau klik peta untuk tetapkan tapak laporan.</small>
  <div id="gis-map" class="gis-report-map" style="height:400px;min-height:400px;border-radius:8px;border:1px solid var(--bs-border-color);"></div>
  <div class="row g-2 mt-2 mb-2">
    <div class="col-6">
      <label class="form-label small mb-1">Latitud</label>
      <input id="report-anchor-lat" type="text" class="form-control form-control-sm" readonly placeholder="—" />
    </div>
    <div class="col-6">
      <label class="form-label small mb-1">Longitud</label>
      <input id="report-anchor-lng" type="text" class="form-control form-control-sm" readonly placeholder="—" />
    </div>
  </div>
  <div class="d-flex flex-wrap gap-2 mt-2 small text-muted">
    <span><span class="gis-legend-risk-area d-inline-block align-middle"></span> Kawasan risiko</span>
    <span><span style="width:10px;height:10px;border-radius:50%;background:#3498db;display:inline-block;"></span> Titik 3D</span>
    <span><span style="width:10px;height:10px;border-radius:50%;background:#d73027;display:inline-block;"></span> Sesaran tinggi 2D</span>
  </div>
</div>

@push('styles')
<style>
  .gis-report-map { position: relative; z-index: 1; }
  .gis-report-map .leaflet-container { height: 100% !important; min-height: 400px; width: 100%; }
  .gis-legend-risk-area { width: 14px; height: 14px; border: 2px solid #e74c3c; background: rgba(231, 76, 60, 0.15); }
  .gis-location-search-wrap { z-index: 1050; }
  .gis-location-results {
    top: calc(100% + 4px); left: 0; right: 0; z-index: 1060; max-height: 240px; overflow-y: auto;
    background-color: #fff; border: 1px solid var(--bs-border-color, #d9dee3); border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.18);
  }
</style>
@endpush

@assets
@vite(['resources/js/report-map-picker.js'])
@endassets
