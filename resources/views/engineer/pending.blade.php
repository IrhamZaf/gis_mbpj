@extends('layouts/layoutMaster')

@section('title', 'Pending Review')

@section('content')
<div class="alert alert-primary d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4" role="region" aria-label="Fail laporan surveyor">
  <div>
    <strong>Fail laporan surveyor</strong>
    <span class="d-block small mb-0">Senarai semua dokumen yang dimuat naik oleh surveyor untuk insiden.</span>
  </div>
  <a href="{{ route('engineer.survey.files') }}" class="btn btn-sm btn-light">Lihat senarai fail</a>
</div>
<div class="card">
  <div class="card-header"><h5 class="mb-0">Insiden menunggu semakan</h5></div>
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>No.</th>
          <th>Kategori</th>
          <th>Risiko</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($incidents as $i)
        <tr>
          <td>{{ $i->incident_number }}</td>
          <td>{{ $i->category }}</td>
          <td>{{ $i->risk_level }}</td>
          <td>{{ $i->status }}</td>
          <td><a class="btn btn-sm btn-primary" href="{{ route('engineer.review', $i) }}">Semak</a></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
