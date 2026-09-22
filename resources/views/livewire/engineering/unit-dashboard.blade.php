<div>
  @include('livewire.partials.dashboard-welcome', [
    'user' => $user,
    'roleLabel' => __('app.role_unit', ['unit' => $unit->name]),
    'subtitle' => __('app.unit_dashboard_subtitle', ['unit' => $unit->name, 'code' => $unit->code]),
    'heroIcon' => 'tabler-building',
    'unitTheme' => $theme,
    'actions' => [
      ['label' => __('app.all_units'), 'url' => route('engineering.hub'), 'icon' => 'tabler-arrow-left', 'class' => 'btn-outline-secondary'],
      ['label' => __('app.map'), 'url' => auth()->user()->isSuperadmin() ? route('superadmin.map') : route('director.map'), 'icon' => 'tabler-map', 'class' => 'btn-outline-primary'],
    ],
  ])

  <div class="row g-3 mb-4">
    @foreach ([
      ['draft', __('app.status_draft'), 'secondary'],
      ['pending_visit', __('app.pending_ta'), 'warning'],
      ['visit_progress', __('app.visit_in_progress'), 'info'],
      ['pending_engineer', 'Engineer', 'primary'],
      ['pending_director', __('app.role_director'), 'dark'],
      ['approved', __('app.approved'), 'success'],
      ['rejected', __('app.rejected'), 'danger'],
    ] as [$key, $label, $badge])
      <div class="col-6 col-md-4 col-xl">
        <div class="card border-0 shadow-sm h-100" style="border-bottom:3px solid {{ $theme['color'] }} !important;">
          <div class="card-body py-3">
            <div class="fw-bold fs-4">{{ $kpis[$key] }}</div>
            <div class="small text-muted">{{ $label }}</div>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="row g-4">
    <div class="col-lg-4">
      @include('livewire.partials.reports-by-category', [
        'reportsByCategory' => $reportsByCategory,
        'title' => __('app.report_by_category'),
      ])
    </div>

    <div class="col-lg-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header border-bottom">
          <h6 class="mb-0">{{ __('app.staff_unit', ['unit' => $unit->name]) }}</h6>
        </div>
        <div class="card-body p-0">
          <ul class="list-group list-group-flush">
            @forelse ($staff as $s)
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <div class="fw-medium">{{ $s->name }}</div>
                  <div class="small text-muted">{{ $s->email }}</div>
                </div>
                <span class="badge bg-label-{{ $theme['label'] }} text-uppercase">{{ $s->role }}</span>
              </li>
            @empty
              <li class="list-group-item text-muted text-center py-4">{{ __('app.no_staff') }}</li>
            @endforelse
          </ul>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header border-bottom d-flex justify-content-between">
          <h6 class="mb-0">{{ __('app.recent_reports_title') }}</h6>
          <span class="badge bg-label-{{ $theme['label'] }}">{{ __('app.total_count', ['count' => $kpis['total']]) }}</span>
        </div>
        <div class="table-responsive">
          <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
              <tr>
                <th>{{ __('app.no') }}</th>
                <th>{{ __('app.title') }}</th>
                <th>{{ __('app.status') }}</th>
                <th>{{ __('app.surveyor') }}</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($recentReports as $r)
                <tr>
                  <td><code class="small">{{ $r->file_number ?? $r->report_number }}</code></td>
                  <td>{{ Str::limit($r->title, 48) }}</td>
                  <td>{!! $r->status_badge !!}</td>
                  <td class="small">{{ $r->user->name ?? '—' }}</td>
                </tr>
              @empty
                <tr><td colspan="4" class="text-center text-muted py-4">{{ __('app.no_reports_for_this_unit') }}</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
