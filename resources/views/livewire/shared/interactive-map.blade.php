<div>
  <div class="card mb-4">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
      <h5 class="mb-0"><i class="ti tabler-map me-2"></i>Peta Interaktif</h5>
      <span class="badge bg-label-primary">{{ count($markers) }} laporan berkoordinat</span>
    </div>
    <div class="card-body pb-0">
      <div class="row g-3 mb-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label small text-muted mb-1">Carian</label>
          <input wire:model.live.debounce.300ms="search" type="text" class="form-control"
            placeholder="Tajuk / no. laporan / lokasi..." />
        </div>
        <div class="col-md-2">
          <label class="form-label small text-muted mb-1">Status</label>
          <select wire:model.live="filterStatus" class="form-select">
            <option value="">Semua</option>
            <option value="draft">Draf</option>
            <option value="submitted">Dihantar</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small text-muted mb-1">Kategori</label>
          <select wire:model.live="filterCategory" class="form-select">
            <option value="">Semua</option>
            @foreach ($categories as $cat)
              <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-5">
          <label class="form-label small text-muted mb-1">Paparan peta</label>
          <div class="btn-group w-100" role="group">
            <button type="button" class="btn btn-outline-primary" data-map-view="markers"
              onclick="window.gisOverviewMap?.setViewMode('markers')">
              <i class="ti tabler-map-pin me-1"></i>Tanda
            </button>
            <button type="button" class="btn btn-outline-primary" data-map-view="heatmap"
              onclick="window.gisOverviewMap?.setViewMode('heatmap')">
              <i class="ti tabler-flame me-1"></i>Heatmap
            </button>
            <button type="button" class="btn btn-outline-primary active" data-map-view="both"
              onclick="window.gisOverviewMap?.setViewMode('both')">
              <i class="ti tabler-layers-intersect me-1"></i>Kedua-dua
            </button>
          </div>
        </div>
      </div>

      <div class="d-flex flex-wrap gap-3 mb-3 small align-items-center">
        <span class="text-muted">Legenda tanda:</span>
        <span class="d-inline-flex align-items-center gap-1">
          <span class="gis-legend-risk-area"></span>
          Kawasan risiko
        </span>
        @foreach ($categories as $cat)
          @php
            $colors = ['#e74c3c', '#3498db', '#2ecc71', '#f39c12', '#9b59b6', '#1abc9c', '#e67e22', '#34495e'];
            $color = $colors[$cat->id % count($colors)];
          @endphp
          <span class="d-inline-flex align-items-center gap-1">
            <span style="width:10px;height:10px;border-radius:50%;background:{{ $color }};display:inline-block;border:1px solid rgba(0,0,0,.15);"></span>
            {{ $cat->name }}
          </span>
        @endforeach
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body p-0 position-relative">
      <div id="map-empty-state"
        class="position-absolute top-50 start-50 translate-middle text-center text-muted {{ count($markers) ? 'd-none' : '' }}"
        style="z-index:1000;pointer-events:none;">
        <i class="ti tabler-map-off icon-48px mb-2 d-block"></i>
        Tiada laporan berkoordinat untuk dipaparkan.
      </div>
      <div id="heatmap-legend"
        class="position-absolute bottom-0 start-0 m-3 px-3 py-2 rounded shadow-sm bg-white small"
        style="z-index:1000;pointer-events:none;">
        <span class="text-muted d-block mb-1">Kepadatan insiden</span>
        <div class="heatmap-legend-bar"></div>
        <div class="d-flex justify-content-between mt-1" style="font-size:10px;">
          <span>Rendah</span>
          <span>Tinggi</span>
        </div>
      </div>
      <div id="gis-overview-map" wire:ignore style="height:calc(100vh - 320px);min-height:480px;border-radius:0 0 8px 8px;"></div>
    </div>
  </div>

  @push('styles')
  <style>
    .gis-map-tiles-grayscale {
      filter: grayscale(100%) contrast(1.08) brightness(1.06);
    }
    .gis-marker-icon { background: transparent !important; border: none !important; }
    .gis-cluster-icon {
      background: #696cff !important;
      border: 3px solid #fff !important;
      border-radius: 50% !important;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.45);
      display: flex !important;
      align-items: center;
      justify-content: center;
    }
    .gis-cluster-count {
      color: #fff;
      font-weight: 700;
      font-size: 14px;
      line-height: 1;
    }
    .gis-legend-risk-area {
      width: 14px;
      height: 14px;
      border: 2px solid #e74c3c;
      background: rgba(231, 76, 60, 0.15);
      display: inline-block;
      flex-shrink: 0;
    }
    .heatmap-legend-bar {
      width: 140px;
      height: 10px;
      border-radius: 4px;
      background: linear-gradient(to right, #313695, #4575b4, #74add1, #abd9e9, #fee090, #fdae61, #f46d43, #d73027);
    }
    .leaflet-heatmap-layer { z-index: 450 !important; }
    .gis-popup { min-width: 180px; }
    #gis-overview-map { z-index: 1; }
  </style>
  @endpush

  @push('scripts')
  @vite(['resources/js/gis-overview-map.js'])
  <script>
    window.reportMarkers = @json($markers);
  </script>
  @endpush
</div>
