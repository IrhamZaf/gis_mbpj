<div>
  @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible mb-4"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('message') }}</div>
  @endif

  <div class="card border-0 shadow-sm">
    <div class="card-header border-bottom">
      <h5 class="mb-0">Lawatan Tapak — Unit {{ $unitName }}</h5>
    </div>
    <div class="card-body">
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="Cari no. fail / tajuk..." />
        </div>
        <div class="col-md-4">
          <select wire:model.live="filterStatus" class="form-select">
            <option value="">Semua Status</option>
            <option value="pending_site_visit">Menunggu Lawatan</option>
            <option value="site_visit_in_progress">Sedang Dijalankan</option>
            <option value="engineer_returned">Dikembalikan</option>
            <option value="pending_engineer_verification">Menunggu Engineer</option>
            <option value="approved">Diluluskan</option>
          </select>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>No. Fail</th>
              <th>Tajuk Kerja</th>
              <th>Lokasi</th>
              <th>Surveyor / Vendor</th>
              <th>Tarikh</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @forelse ($reports as $r)
              <tr>
                <td><code>{{ $r->file_number ?? $r->report_number }}</code></td>
                <td>{{ $r->title }}</td>
                <td class="small">{{ $r->location_name ?? '-' }}</td>
                <td>
                  <div>{{ $r->user->name ?? '-' }}</div>
                  <div class="small text-muted">{{ $r->vendor_name ?? '' }}</div>
                </td>
                <td class="small">{{ $r->submitted_at?->format('d/m/Y') ?? '-' }}</td>
                <td>{!! $r->status_badge !!}</td>
                <td class="text-end text-nowrap">
                  @if ($r->workflow_status === 'pending_site_visit')
                    <button wire:click="startVisit({{ $r->id }})" class="btn btn-sm btn-primary">Mulakan Lawatan</button>
                  @elseif (in_array($r->workflow_status, ['site_visit_in_progress', 'engineer_returned'], true))
                    <a href="{{ route('site-visits.form', $r) }}" class="btn btn-sm btn-warning">Sambung</a>
                    @can('downloadPdf', $r)
                      <a href="{{ route('reports.site-visit-pdf', $r) }}" class="btn btn-sm btn-outline-danger" target="_blank" title="Cetak PDF">
                        <i class="ti tabler-printer"></i>
                      </a>
                    @endcan
                  @else
                    <a href="{{ route('site-visits.form', $r) }}" class="btn btn-sm btn-outline-secondary">Lihat</a>
                    @can('downloadPdf', $r)
                      <a href="{{ route('reports.site-visit-pdf', $r) }}" class="btn btn-sm btn-outline-danger" target="_blank" title="Cetak PDF">
                        <i class="ti tabler-printer"></i>
                      </a>
                    @endcan
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="7" class="text-center py-4 text-muted">Tiada laporan.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
        <div class="small text-muted">
          Menunjukkan {{ $reports->firstItem() ?? 0 }}–{{ $reports->lastItem() ?? 0 }}
          daripada {{ $reports->total() }} rekod
        </div>
        <div>
          {{ $reports->links() }}
        </div>
      </div>
    </div>
  </div>
</div>
