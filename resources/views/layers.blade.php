@extends('layouts/layoutMaster')

@section('title', 'Lapisan GIS')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/leaflet/leaflet.scss', 'resources/assets/vendor/scss/pages/gis-mbpj.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/leaflet/leaflet.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/gis-layers.js'])
@endsection

@section('content')
<div class="row g-4">
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header"><h5 class="mb-0">Senarai lapisan</h5></div>
      <div class="list-group list-group-flush">
        @forelse ($layers as $layer)
        <button type="button" class="list-group-item list-group-item-action gis-layer-pick d-flex justify-content-between align-items-center"
          data-id="{{ $layer->id }}" data-name="{{ $layer->name }}">
          <span>{{ $layer->name }} <small class="text-muted">({{ $layer->type }})</small></span>
          <span class="badge bg-label-primary">Papar</span>
        </button>
        @empty
        <div class="list-group-item text-muted">Tiada lapisan aktif.</div>
        @endforelse
      </div>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header"><h5 class="mb-0">Pratonton GeoJSON</h5></div>
      <div class="card-body p-0">
        <div id="gisLayersMap" class="gis-leaflet-mini"></div>
      </div>
    </div>
  </div>
</div>
@endsection
