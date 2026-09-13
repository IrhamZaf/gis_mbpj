<div>
  @include('livewire.partials.dashboard-welcome', [
    'user' => $user,
    'roleLabel' => 'TA · Unit ' . $unitName,
    'subtitle' => 'Dashboard Unit ' . $unitName . ' — urus lawatan tapak dan laporan PJ/PJK.',
    'heroIcon' => 'tabler-map-pin',
    'unitTheme' => $unitTheme,
    'actions' => [
      ['label' => 'Senarai Lawatan', 'url' => route('ta.reports'), 'icon' => 'tabler-list', 'class' => 'btn-primary'],
      ['label' => 'Peta', 'url' => route('ta.map'), 'icon' => 'tabler-map', 'class' => 'btn-outline-primary'],
    ],
  ])

  <div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm h-100" style="border-bottom:3px solid {{ $unitTheme['color'] }} !important;"><div class="card-body">
        <h3 class="mb-1">{{ $pending }}</h3>
        <p class="mb-0 text-muted">Menunggu Lawatan</p>
      </div></div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm h-100" style="border-bottom:3px solid {{ $unitTheme['color'] }} !important;"><div class="card-body">
        <h3 class="mb-1">{{ $inProgress }}</h3>
        <p class="mb-0 text-muted">Sedang / Dikembalikan</p>
      </div></div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm h-100" style="border-bottom:3px solid {{ $unitTheme['color'] }} !important;"><div class="card-body">
        <h3 class="mb-1">{{ $awaitingEngineer }}</h3>
        <p class="mb-0 text-muted">Menunggu Engineer</p>
      </div></div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm h-100" style="border-bottom:3px solid {{ $unitTheme['color'] }} !important;"><div class="card-body">
        <h3 class="mb-1">{{ $completed }}</h3>
        <p class="mb-0 text-muted">Diluluskan</p>
      </div></div>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-header border-bottom d-flex justify-content-between">
      <h6 class="mb-0">Tugasan Terkini</h6>
      <a href="{{ route('ta.reports') }}" class="btn btn-sm btn-outline-primary">Lihat semua</a>
    </div>
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th>No. Fail</th>
            <th>Tajuk</th>
            <th>Surveyor</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($recent as $r)
            <tr>
              <td><code>{{ $r->file_number ?? $r->report_number }}</code></td>
              <td>{{ $r->title }}</td>
              <td>{{ $r->user->name ?? '-' }}</td>
              <td>{!! $r->status_badge !!}</td>
              <td class="text-end">
                @if (in_array($r->workflow_status, ['pending_site_visit', 'site_visit_in_progress', 'engineer_returned'], true))
                  <a href="{{ route('site-visits.form', $r) }}" class="btn btn-sm btn-primary">Buka</a>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted py-4">Tiada tugasan.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
