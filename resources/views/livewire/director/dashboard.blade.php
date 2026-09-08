<div>
  @include('livewire.partials.dashboard-welcome', [
    'user' => $user,
    'roleLabel' => 'Pengarah',
    'subtitle' => 'Luluskan atau tolak laporan yang telah disahkan Engineer.',
    'heroIcon' => 'tabler-stamp',
    'actions' => [
      ['label' => 'Menunggu Kelulusan', 'url' => route('director.reports'), 'icon' => 'tabler-list-check', 'class' => 'btn-primary'],
      ['label' => 'Peta', 'url' => route('director.map'), 'icon' => 'tabler-map', 'class' => 'btn-outline-primary'],
    ],
  ])

  <div class="row g-4 mb-4">
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><h3>{{ $pending }}</h3><p class="mb-0 text-muted">Menunggu Kelulusan</p></div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><h3>{{ $approved }}</h3><p class="mb-0 text-muted">Diluluskan</p></div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><h3>{{ $rejected }}</h3><p class="mb-0 text-muted">Ditolak</p></div></div></div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-header border-bottom"><h6 class="mb-0">Senarai Terkini</h6></div>
    <div class="table-responsive">
      <table class="table mb-0 align-middle">
        <thead class="table-light"><tr><th>No. Fail</th><th>Tajuk</th><th>Unit</th><th>Engineer</th><th>Status</th><th></th></tr></thead>
        <tbody>
          @forelse ($recent as $r)
            <tr>
              <td><code>{{ $r->file_number ?? $r->report_number }}</code></td>
              <td>{{ $r->title }}</td>
              <td>{{ $r->unit->name ?? '-' }}</td>
              <td>{{ $r->latestEngineerVerification?->engineer?->name ?? '-' }}</td>
              <td>{!! $r->status_badge !!}</td>
              <td class="text-end"><a href="{{ route('director.reports.view', $r) }}" class="btn btn-sm btn-primary">Semak</a></td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Tiada rekod.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
