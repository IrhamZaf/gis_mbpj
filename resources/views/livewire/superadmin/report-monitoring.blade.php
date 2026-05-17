<div>
  <div class="card">
    <div class="card-header"><h5 class="mb-0">Pemantauan Laporan</h5></div>
    <div class="card-body">
      <div class="row mb-4">
        <div class="col-md-4"><input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="Cari tajuk / no. laporan..." /></div>
        <div class="col-md-3"><select wire:model.live="filterStatus" class="form-select"><option value="">Semua Status</option><option value="draft">Draf</option><option value="submitted">Dihantar</option></select></div>
        <div class="col-md-3"><select wire:model.live="filterCategory" class="form-select"><option value="">Semua Kategori</option>@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead><tr><th>No. Laporan</th><th>Tajuk</th><th>Kategori</th><th>Surveyor</th><th>Status</th><th>Tarikh</th></tr></thead>
          <tbody>
            @forelse ($reports as $r)
            <tr>
              <td><code>{{ $r->report_number }}</code></td>
              <td>{{ $r->title }}</td>
              <td>{{ $r->category->name ?? '-' }}</td>
              <td>{{ $r->user->name ?? '-' }}</td>
              <td>{!! $r->status_badge !!}</td>
              <td>{{ $r->created_at->format('d/m/Y') }}</td>
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
