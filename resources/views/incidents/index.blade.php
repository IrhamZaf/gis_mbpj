@extends('layouts/layoutMaster')

@section('title', 'Laporan Insiden')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss'
])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/gis-incidents.js'])
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h4 class="mb-1">Senarai insiden</h4>
    <p class="text-muted mb-0">Sinkhole dan cerun di kawasan Petaling Jaya</p>
  </div>
  @if(auth()->user()->isAdmin() || auth()->user()->isSurveyor())
  <a href="{{ route('incidents.create') }}" class="btn btn-primary">Tambah insiden</a>
  @endif
</div>

<div class="card">
  <div class="card-datatable table-responsive">
    <table class="table" id="gisIncidentsTable" data-category="{{ $category }}">
      <thead>
        <tr>
          <th>No. rujukan</th>
          <th>Kategori</th>
          <th>Tarikh</th>
          <th>Risiko</th>
          <th>Status</th>
          <th>Lokasi</th>
          <th></th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>
@endsection
