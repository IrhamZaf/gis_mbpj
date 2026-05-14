@extends('layouts/layoutMaster')

@section('title', 'Senarai Survey')

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Data survey</h5>
    <a href="{{ route('survey.upload') }}" class="btn btn-sm btn-primary">Muat naik</a>
  </div>
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Tarikh</th>
          <th>Surveyor</th>
          <th>Insiden</th>
          <th>Nota</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($surveys as $s)
        <tr>
          <td>{{ $s->survey_date->format('d/m/Y') }}</td>
          <td>{{ $s->surveyor?->name }}</td>
          <td>{{ $s->incident?->incident_number ?? '—' }}</td>
          <td>{{ \Illuminate\Support\Str::limit($s->notes ?? '', 40) }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
