@extends('layouts/layoutMaster')

@section('title', 'Fail laporan surveyor')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div>
    <h4 class="mb-1">Fail laporan surveyor</h4>
    <p class="text-muted mb-0">Senarai semua dokumen GIS &amp; laporan yang dimuat naik untuk semakan jurutera.</p>
  </div>
</div>

<div class="card mb-4">
  <div class="card-body">
    <form method="get" action="{{ route('engineer.survey.files') }}" class="row g-2 align-items-end">
      <div class="col-md-8">
        <label class="form-label mb-1" for="q">Cari (no. insiden, nama fail, pengguna)</label>
        <input type="search" id="q" name="q" value="{{ $qSearch }}" class="form-control" placeholder="Contoh: SH3, laporan.pdf" />
      </div>
      <div class="col-md-4">
        <button type="submit" class="btn btn-primary me-2">Cari</button>
        <a href="{{ route('engineer.survey.files') }}" class="btn btn-label-secondary">Set semula</a>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Tarikh</th>
          <th>No. insiden</th>
          <th>Versi</th>
          <th>Jenis</th>
          <th>Nama fail</th>
          <th>Dihantar oleh</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse ($uploads as $up)
        @php $incident = $up->survey?->incident; @endphp
        <tr>
          <td class="text-nowrap small">{{ $up->created_at->format('d/m/Y H:i') }}</td>
          <td>
            @if($incident)
            <a href="{{ route('incidents.show', $incident) }}">{{ $incident->incident_number }}</a>
            @else
            <span class="text-muted">—</span>
            @endif
          </td>
          <td>{{ $up->survey ? 'v'.$up->survey->version : '—' }}</td>
          <td><span class="small">{{ $up->labelMs() }}</span></td>
          <td class="small">{{ \Illuminate\Support\Str::limit($up->original_name ?? basename($up->file_path), 48) }}</td>
          <td class="small">
            {{ $up->survey?->vendor_name ?: '—' }}<br>
            <span class="text-muted">{{ $up->survey?->surveyor_name ?: $up->uploader?->name ?: $up->survey?->surveyor?->name }}</span>
          </td>
          <td class="text-nowrap">
            <div class="d-flex flex-wrap gap-1">
              @if($up->isPreviewableInBrowser())
              <a class="btn btn-sm btn-primary" href="{{ route('engineer.survey.view', $up) }}" target="_blank" rel="noopener noreferrer">Lihat</a>
              @endif
              <a class="btn btn-sm btn-label-primary" href="{{ route('engineer.survey.download', $up) }}">Muat turun</a>
              @if($incident)
              <a class="btn btn-sm btn-outline-secondary" href="{{ route('engineer.review', $incident) }}">Semakan</a>
              @endif
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" class="text-muted">Tiada fail laporan dijumpai (@if($qSearch !== '') Cuba carian lain @else Minta surveyor muat naik melalui modul Survey @endif).</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($uploads->hasPages())
  <div class="card-body pt-0">
    {{ $uploads->links() }}
  </div>
  @endif
</div>

@if($bareSurveys->isNotEmpty())
<div class="card mt-4">
  <div class="card-header">
    <h5 class="mb-0">Hantaran tanpa fail berasingan</h5>
    <p class="small text-muted mb-0">GeoJSON / metadata dalam rekod survey sahaja (tiada baris <code>survey_uploads</code>).</p>
  </div>
  <div class="table-responsive">
    <table class="table mb-0">
      <thead>
        <tr>
          <th>Tarikh</th>
          <th>No. insiden</th>
          <th>Versi</th>
          <th>Status</th>
          <th>Surveyor</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($bareSurveys as $sv)
        @php $inc = $sv->incident; @endphp
        <tr>
          <td class="text-nowrap small">{{ $sv->survey_date->format('d/m/Y') }}</td>
          <td>
            @if($inc)
            <a href="{{ route('incidents.show', $inc) }}">{{ $inc->incident_number }}</a>
            @else
            —
            @endif
          </td>
          <td>v{{ $sv->version }}</td>
          <td><span class="badge bg-label-secondary small">{{ $sv->reviewStatusLabel() }}</span></td>
          <td class="small">{{ $sv->vendor_name ?: '—' }} · {{ $sv->surveyor_name ?: $sv->surveyor?->name }}</td>
          <td class="text-nowrap">
            @if($inc)
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('engineer.review', $inc) }}">Semakan</a>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif
@endsection
