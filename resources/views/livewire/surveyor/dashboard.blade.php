<div>
  <div class="row">
    <div class="col-xl-4 col-sm-6 mb-6">
      <div class="card h-100"><div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <div><p class="mb-1 text-body">Jumlah Laporan</p><h4 class="mb-0">{{ $totalReports }}</h4></div>
          <div class="avatar"><span class="avatar-initial rounded bg-label-primary"><i class="icon-base ti tabler-report icon-28px"></i></span></div>
        </div>
      </div></div>
    </div>
    <div class="col-xl-4 col-sm-6 mb-6">
      <div class="card h-100"><div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <div><p class="mb-1 text-body">Draf</p><h4 class="mb-0">{{ $draftReports }}</h4></div>
          <div class="avatar"><span class="avatar-initial rounded bg-label-warning"><i class="icon-base ti tabler-file-text icon-28px"></i></span></div>
        </div>
      </div></div>
    </div>
    <div class="col-xl-4 col-sm-6 mb-6">
      <div class="card h-100"><div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <div><p class="mb-1 text-body">Dihantar</p><h4 class="mb-0">{{ $submittedReports }}</h4></div>
          <div class="avatar"><span class="avatar-initial rounded bg-label-success"><i class="icon-base ti tabler-send icon-28px"></i></span></div>
        </div>
      </div></div>
    </div>
  </div>

  <!-- Recent reports -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Laporan Terkini</h5>
      <a href="{{ route('surveyor.reports.create') }}" class="btn btn-primary btn-sm"><i class="ti tabler-plus me-1"></i>Cipta Laporan</a>
    </div>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead><tr><th>No. Laporan</th><th>Tajuk</th><th>Kategori</th><th>Status</th><th>Tarikh</th></tr></thead>
        <tbody>
          @forelse ($recentReports as $r)
          <tr>
            <td><code>{{ $r->report_number }}</code></td>
            <td>{{ $r->title }}</td>
            <td>{{ $r->category->name ?? '-' }}</td>
            <td>{!! $r->status_badge !!}</td>
            <td>{{ $r->created_at->format('d/m/Y') }}</td>
          </tr>
          @empty
          <tr><td colspan="5" class="text-center py-4">Belum ada laporan. <a href="{{ route('surveyor.reports.create') }}">Cipta sekarang</a></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
