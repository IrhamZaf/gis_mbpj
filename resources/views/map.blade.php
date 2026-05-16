@php
$configData = Helper::appClasses();
$heatmapMode = $heatmapMode ?? false;
@endphp

@extends('layouts/layoutMaster')

@section('title', $heatmapMode ? 'Heatmap Risiko' : 'Peta GIS Interaktif')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/leaflet/leaflet.scss',
'resources/assets/vendor/scss/pages/gis-mbpj.scss'
])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/leaflet/leaflet.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/gis-map.js'])
@endsection

@section('content')
<div class="card gis-map-fullscreen mb-4" style="overflow:hidden;border-radius:12px">
  <div class="card-header flex-wrap gap-2 d-flex justify-content-between align-items-center" style="background:rgba(15,23,42,0.95);border-bottom:1px solid rgba(255,255,255,0.08)">
    <div class="d-flex align-items-center gap-2">
      <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:rgba(255,255,255,0.08)">
        <i class="icon-base ti {{ $heatmapMode ? 'tabler-flame' : 'tabler-map-2' }}" style="color:#60a5fa;font-size:18px"></i>
      </div>
      <div>
        <h5 class="mb-0" style="color:#f1f5f9">{{ $heatmapMode ? 'Heatmap risiko insiden' : 'Peta interaktif GIS' }}</h5>
        <small style="color:#64748b">Petaling Jaya, Selangor</small>
      </div>
    </div>
    <div class="d-flex flex-wrap gap-2 align-items-center">
      <input type="search" class="form-control form-control-sm" id="gisMapSearch" placeholder="Cari no. rujukan / alamat"
        style="min-width:12rem;background:rgba(255,255,255,0.06);border-color:rgba(255,255,255,0.12);color:#e2e8f0" />
      <div class="btn-group">
        <button type="button" class="btn btn-sm btn-primary gis-mode-btn" data-mode="normal" id="btnModeNormal">
          <i class="ti tabler-map-2 me-1"></i>Peta
        </button>
        <button type="button" class="btn btn-sm btn-outline-light gis-mode-btn" data-mode="heatmap" id="btnModeHeatmap" style="color:#94a3b8;border-color:rgba(255,255,255,0.15)">
          <i class="ti tabler-flame me-1"></i>Heatmap
        </button>
      </div>
      {{-- Layers toggle button --}}
      <button type="button" class="btn btn-sm btn-outline-light" id="btnToggleLayers" style="color:#94a3b8;border-color:rgba(255,255,255,0.15)" title="Lapisan GIS">
        <i class="ti tabler-stack-2 me-1"></i>Lapisan
      </button>
    </div>
  </div>
  <div class="card-body p-0 position-relative">
    <div id="gisMainMap" class="leaflet-map" style="min-height: 420px"></div>

    {{-- Layers sidebar panel (overlaid on map) --}}
    <div id="gisLayersPanel" class="gis-layers-panel" style="display:none">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0" style="color:#f1f5f9"><i class="ti tabler-stack-2 me-1"></i>Lapisan GIS</h6>
        <button type="button" class="btn-close btn-close-white btn-sm" id="btnCloseLayers" aria-label="Tutup"></button>
      </div>
      <div class="list-group list-group-flush">
        @forelse ($layers as $layer)
        <button type="button" class="list-group-item list-group-item-action gis-layer-pick d-flex justify-content-between align-items-center"
          data-id="{{ $layer->id }}" data-name="{{ $layer->name }}"
          style="background:rgba(255,255,255,0.04);border-color:rgba(255,255,255,0.08);color:#cbd5e1">
          <span>{{ $layer->name }} <small class="text-muted">({{ $layer->type }})</small></span>
          <span class="badge bg-label-primary">Papar</span>
        </button>
        @empty
        <div class="list-group-item text-muted" style="background:transparent;border:0">Tiada lapisan aktif.</div>
        @endforelse
      </div>
    </div>
  </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="gisIncidentOffcanvas" aria-labelledby="gisIncidentOffcanvasLabel">
  <div class="offcanvas-header">
    <h5 id="gisIncidentOffcanvasLabel">Butiran insiden</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body" id="gisIncidentOffcanvasBody">
    <p class="text-muted">Pilih penanda pada peta.</p>
  </div>
</div>

<script>
  window.gisMapOptions = @json([
    'heatmap' => $heatmapMode,
    'geoJsonUrl' => url('/api/incidents/geojson'),
  ]);
</script>
@endsection
