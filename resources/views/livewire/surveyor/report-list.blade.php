<div>
  @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible mb-4"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('message') }}</div>
  @endif

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Senarai Laporan Saya</h5>
      <a href="{{ route('surveyor.reports.create') }}" class="btn btn-primary btn-sm"><i class="ti tabler-plus me-1"></i>Cipta Laporan</a>
    </div>
    <div class="card-body">
      <div class="row mb-4">
        <div class="col-md-6"><input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="Cari tajuk / no. laporan..." /></div>
        <div class="col-md-3"><select wire:model.live="filterStatus" class="form-select"><option value="">Semua Status</option><option value="draft">Draf</option><option value="submitted">Dihantar</option></select></div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead><tr><th>No. Laporan</th><th>Tajuk</th><th>Kategori</th><th>Status</th><th>Tarikh</th><th>Tindakan</th></tr></thead>
          <tbody>
            @forelse ($reports as $r)
            <tr>
              <td><code>{{ $r->report_number }}</code></td>
              <td>{{ $r->title }}</td>
              <td>{{ $r->category->name ?? '-' }}</td>
              <td>{!! $r->status_badge !!}</td>
              <td>{{ $r->created_at->format('d/m/Y') }}</td>
              <td>
                <div class="d-flex gap-1">
                  <a href="{{ route('surveyor.reports.view', $r) }}" class="btn btn-sm btn-icon btn-text-info" title="Lihat"><i class="ti tabler-eye"></i></a>
                  <a href="{{ route('surveyor.reports.edit', $r) }}" class="btn btn-sm btn-icon btn-text-secondary" title="Kemaskini"><i class="ti tabler-pencil"></i></a>
                </div>
              </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-4">Tiada laporan.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-3">{{ $reports->links() }}</div>
    </div>
  </div>
</div>
