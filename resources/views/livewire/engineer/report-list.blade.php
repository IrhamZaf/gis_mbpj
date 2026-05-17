<div>
  <div class="card">
    <div class="card-header"><h5 class="mb-0">Senarai Laporan (Dihantar)</h5></div>
    <div class="card-body">
      <div class="row mb-4">
        <div class="col-md-5"><input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="Cari tajuk / no. laporan / lokasi..." /></div>
        <div class="col-md-3"><select wire:model.live="filterCategory" class="form-select"><option value="">Semua Kategori</option>@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead><tr><th>No. Laporan</th><th>Tajuk</th><th>Kategori</th><th>Lokasi</th><th>Surveyor</th><th>Tarikh</th><th></th></tr></thead>
          <tbody>
            @forelse ($reports as $r)
            <tr>
              <td><code>{{ $r->report_number }}</code></td>
              <td>{{ $r->title }}</td>
              <td>{{ $r->category->name ?? '-' }}</td>
              <td>{{ $r->location_name ?? '-' }}</td>
              <td>{{ $r->user->name ?? '-' }}</td>
              <td>{{ $r->submitted_at?->format('d/m/Y') ?? '-' }}</td>
              <td><a href="{{ route('engineer.reports.view', $r) }}" class="btn btn-sm btn-primary"><i class="ti tabler-eye me-1"></i>Lihat</a></td>
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
