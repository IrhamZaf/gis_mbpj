<div>
  <div class="row">
    <div class="col-xl-6 col-sm-6 mb-6">
      <div class="card h-100"><div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <div><p class="mb-1 text-body">Laporan Dihantar</p><h4 class="mb-0">{{ $totalSubmitted }}</h4></div>
          <div class="avatar"><span class="avatar-initial rounded bg-label-success"><i class="icon-base ti tabler-report-search icon-28px"></i></span></div>
        </div>
      </div></div>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h5 class="mb-0">Laporan Terkini</h5></div>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead><tr><th>No. Laporan</th><th>Tajuk</th><th>Kategori</th><th>Surveyor</th><th>Tarikh Hantar</th><th>Tindakan</th></tr></thead>
        <tbody>
          @forelse ($recentReports as $r)
          <tr>
            <td><code>{{ $r->report_number }}</code></td>
            <td>{{ $r->title }}</td>
            <td>{{ $r->category->name ?? '-' }}</td>
            <td>{{ $r->user->name ?? '-' }}</td>
            <td>{{ $r->submitted_at?->format('d/m/Y H:i') ?? '-' }}</td>
            <td><a href="{{ route('engineer.reports.view', $r) }}" class="btn btn-sm btn-primary"><i class="ti tabler-eye me-1"></i>Lihat</a></td>
          </tr>
          @empty
          <tr><td colspan="6" class="text-center py-4">Tiada laporan dihantar.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
