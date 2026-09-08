<div>
  <div class="card border-0 shadow-sm">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
      <div>
        <h5 class="mb-0">Senarai Laporan</h5>
        <small class="text-muted">Unit {{ $unitName }}</small>
      </div>
    </div>
    <div class="card-body">
      <div class="row mb-4 g-3">
        <div class="col-md-5"><input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="Cari tajuk / no. laporan / lokasi..." /></div>
        <div class="col-md-3">
          <select wire:model.live="filterStatus" class="form-select">
            <option value="">Semua Status</option>
            <option value="pending_engineer_verification">Menunggu Pengesahan</option>
            <option value="engineer_returned">Dikembalikan</option>
            <option value="pending_director_approval">Menunggu Pengarah</option>
            <option value="approved">Diluluskan</option>
            <option value="director_rejected">Ditolak Pengarah</option>
            <option value="pending_site_visit">Menunggu Lawatan</option>
            <option value="site_visit_in_progress">Lawatan Berjalan</option>
          </select>
        </div>
        <div class="col-md-4">
          <select wire:model.live="filterCategory" class="form-select">
            <option value="">Semua Kategori</option>
            @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
          </select>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light"><tr><th>No. Laporan</th><th>Tajuk</th><th>Kategori</th><th>Status</th><th>Surveyor</th><th>Tarikh</th><th></th></tr></thead>
          <tbody>
            @forelse ($reports as $r)
            <tr>
              <td><code>{{ $r->report_number }}</code></td>
              <td>
                <div>{{ $r->title }}</div>
                <div class="small text-muted">{{ $r->location_name ?? '' }}</div>
              </td>
              <td>{{ $r->category->name ?? '-' }}</td>
              <td>{!! $r->status_badge !!}</td>
              <td>{{ $r->user->name ?? '-' }}</td>
              <td>{{ $r->submitted_at?->format('d/m/Y') ?? '-' }}</td>
              <td><a href="{{ route('engineer.reports.view', $r) }}" class="btn btn-sm btn-primary"><i class="ti tabler-eye me-1"></i>Lihat</a></td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-4">Tiada laporan untuk unit anda.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-3">{{ $reports->links() }}</div>
    </div>
  </div>
</div>
