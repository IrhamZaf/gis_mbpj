<div>
  @include('livewire.partials.dashboard-welcome', [
    'user' => $user,
    'roleLabel' => __('app.role_ta_unit', ['unit' => $unitName]),
    'subtitle' => __('app.ta_dashboard_subtitle', ['unit' => $unitName]),
    'heroIcon' => 'tabler-map-pin',
    'unitTheme' => $unitTheme,
    'actions' => [
      ['label' => __('app.visit_list'), 'url' => route('ta.reports'), 'icon' => 'tabler-list', 'class' => 'btn-primary'],
      ['label' => __('app.map'), 'url' => route('ta.map'), 'icon' => 'tabler-map', 'class' => 'btn-outline-primary'],
    ],
  ])

  @include('livewire.partials.units-overview', [
    'unitCards' => $unitCards,
    'grandTotal' => $grandTotal,
  ])

  <div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm h-100" style="border-bottom:3px solid {{ $unitTheme['color'] }} !important;"><div class="card-body">
        <h3 class="mb-1">{{ $pending }}</h3>
        <p class="mb-0 text-muted">{{ __('app.pending_site_visit') }}</p>
      </div></div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm h-100" style="border-bottom:3px solid {{ $unitTheme['color'] }} !important;"><div class="card-body">
        <h3 class="mb-1">{{ $inProgress }}</h3>
        <p class="mb-0 text-muted">{{ __('app.in_progress_returned') }}</p>
      </div></div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm h-100" style="border-bottom:3px solid {{ $unitTheme['color'] }} !important;"><div class="card-body">
        <h3 class="mb-1">{{ $awaitingEngineer }}</h3>
        <p class="mb-0 text-muted">{{ __('app.pending_engineer') }}</p>
      </div></div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm h-100" style="border-bottom:3px solid {{ $unitTheme['color'] }} !important;"><div class="card-body">
        <h3 class="mb-1">{{ $completed }}</h3>
        <p class="mb-0 text-muted">{{ __('app.approved') }}</p>
      </div></div>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-header border-bottom d-flex justify-content-between">
      <h6 class="mb-0">{{ __('app.recent_tasks') }}</h6>
      <a href="{{ route('ta.reports') }}" class="btn btn-sm btn-outline-primary">{{ __('app.view_all') }}</a>
    </div>
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th>{{ __('app.file_number') }}</th>
            <th>{{ __('app.title') }}</th>
            <th>{{ __('app.surveyor') }}</th>
            <th>{{ __('app.status') }}</th>
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
                  <a href="{{ route('site-visits.form', $r) }}" class="btn btn-sm btn-primary">{{ __('app.open') }}</a>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted py-4">{{ __('app.no_tasks') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
