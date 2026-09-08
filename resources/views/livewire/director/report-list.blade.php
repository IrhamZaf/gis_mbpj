<div>
  <div class="card border-0 shadow-sm">
    <div class="card-header border-bottom"><h5 class="mb-0">Kelulusan Pengarah</h5></div>
    <div class="card-body">
      <div class="row g-3 mb-4">
        <div class="col-md-6"><input wire:model.live.debounce.300ms="search" class="form-control" placeholder="Cari no. fail / tajuk..." /></div>
        <div class="col-md-4">
          <select wire:model.live="filterStatus" class="form-select">
            <option value="">Semua</option>
            <option value="pending_director_approval">Menunggu Kelulusan</option>
            <option value="approved">Diluluskan</option>
            <option value="director_rejected">Ditolak</option>
          </select>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr><th>No. Fail</th><th>Tajuk</th><th>Unit</th><th>TA</th><th>Engineer</th><th>Status</th><th></th></tr>
          </thead>
          <tbody>
            @forelse ($reports as $r)
              <tr>
                <td><code>{{ $r->file_number ?? $r->report_number }}</code></td>
                <td>{{ $r->title }}</td>
                <td>{{ $r->unit->name ?? '-' }}</td>
                <td>{{ $r->siteVisit?->ta?->name ?? '-' }}</td>
                <td>{{ $r->latestEngineerVerification?->engineer?->name ?? '-' }}</td>
                <td>{!! $r->status_badge !!}</td>
                <td class="text-end"><a href="{{ route('director.reports.view', $r) }}" class="btn btn-sm btn-primary">Semak & Lulus</a></td>
              </tr>
            @empty
              <tr><td colspan="7" class="text-center py-4 text-muted">Tiada laporan.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-3">{{ $reports->links() }}</div>
    </div>
  </div>
</div>
