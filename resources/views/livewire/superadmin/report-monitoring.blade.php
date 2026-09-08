<div>
  <div class="card border-0 shadow-sm">
    <div class="card-header border-bottom"><h5 class="mb-0">Pemantauan Laporan</h5></div>
    <div class="card-body">
      <div class="row mb-4 g-3">
        <div class="col-md-3"><input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="Cari tajuk / no. laporan..." /></div>
        <div class="col-md-2">
          <select wire:model.live="filterUnit" class="form-select">
            <option value="">Semua Unit</option>
            @foreach($units as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
          </select>
        </div>
        <div class="col-md-2">
          <select wire:model.live="filterStatus" class="form-select">
            <option value="">Semua Status</option>
            <option value="draft">Draf</option>
            <option value="submitted">Dihantar (status)</option>
            <option value="pending_site_visit">Menunggu Lawatan</option>
            <option value="site_visit_in_progress">Lawatan Berjalan</option>
            <option value="pending_engineer_verification">Menunggu Engineer</option>
            <option value="engineer_returned">Dikembalikan Engineer</option>
            <option value="pending_director_approval">Menunggu Pengarah</option>
            <option value="approved">Diluluskan</option>
            <option value="director_rejected">Ditolak</option>
            <option value="completed">Selesai</option>
          </select>
        </div>
        <div class="col-md-2">
          <select wire:model.live="filterCategory" class="form-select">
            <option value="">Semua Kategori</option>
            @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
          </select>
        </div>
        <div class="col-md-3">
          <select wire:model.live="filterSurveyor" class="form-select">
            <option value="">Semua Surveyor</option>
            @foreach($surveyors as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
          </select>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light"><tr><th>No. Laporan</th><th>Tajuk</th><th>Unit</th><th>Kategori</th><th>Surveyor</th><th>Status</th><th>Tarikh</th></tr></thead>
          <tbody>
            @forelse ($reports as $r)
            <tr>
              <td><code>{{ $r->report_number }}</code></td>
              <td>{{ $r->title }}</td>
              <td>{{ $r->unit->name ?? '-' }}</td>
              <td>{{ $r->category->name ?? '-' }}</td>
              <td>{{ $r->user->name ?? '-' }}</td>
              <td>{!! $r->status_badge !!}</td>
              <td>{{ $r->created_at->format('d/m/Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-4">Tiada laporan.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-3">{{ $reports->links() }}</div>
    </div>
  </div>
</div>
