@extends('layouts/layoutMaster')

@section('title', 'Insiden Diluluskan')

@section('content')
<div class="card">
  <div class="card-header"><h5 class="mb-0">Telah diluluskan</h5></div>
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>No.</th>
          <th>Kategori</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($incidents as $i)
        <tr>
          <td>{{ $i->incident_number }}</td>
          <td>{{ $i->category }}</td>
          <td><a class="btn btn-sm btn-label-primary" href="{{ route('incidents.show', $i) }}">Lihat</a></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
