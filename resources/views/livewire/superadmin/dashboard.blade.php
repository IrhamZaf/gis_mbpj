<div>
  @include('livewire.partials.dashboard-welcome', [
    'user' => $user,
    'roleLabel' => __('app.role_superadmin'),
    'subtitle' => __('app.superadmin_dashboard_subtitle'),
    'heroIcon' => 'tabler-shield-check',
    'actions' => [
      ['label' => __('app.interactive_map'), 'url' => route('superadmin.map'), 'icon' => 'tabler-map', 'class' => 'btn-primary'],
      ['label' => __('app.report_monitoring'), 'url' => route('superadmin.reports'), 'icon' => 'tabler-report-analytics', 'class' => 'btn-outline-primary'],
      ['label' => __('app.units'), 'url' => route('superadmin.units'), 'icon' => 'tabler-building-community', 'class' => 'btn-outline-secondary'],
    ],
  ])

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <div class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label small text-muted mb-1">{{ __('app.unit') }}</label>
          <select wire:model.live="filterUnit" class="form-select">
            <option value="">{{ __('app.all_units') }}</option>
            @foreach ($units as $unit)
              <option value="{{ $unit->id }}">{{ $unit->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small text-muted mb-1">{{ __('app.status') }}</label>
          <select wire:model.live="filterStatus" class="form-select">
            <option value="">{{ __('app.all_statuses') }}</option>
            <option value="draft">{{ __('app.status_draft') }}</option>
            <option value="submitted">{{ __('app.status_submitted') }}</option>
            <option value="pending_site_visit">{{ __('app.wf_pending_site_visit') }}</option>
            <option value="site_visit_in_progress">{{ __('app.wf_site_visit_in_progress') }}</option>
            <option value="pending_engineer_verification">{{ __('app.wf_pending_engineer_verification') }}</option>
            <option value="engineer_returned">{{ __('app.wf_engineer_returned') }}</option>
            <option value="pending_director_approval">{{ __('app.wf_pending_director_approval') }}</option>
            <option value="approved">{{ __('app.wf_approved') }}</option>
            <option value="director_rejected">{{ __('app.wf_director_rejected') }}</option>
            <option value="completed">{{ __('app.status_completed') }}</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small text-muted mb-1">{{ __('app.category') }}</label>
          <select wire:model.live="filterCategory" class="form-select">
            <option value="">{{ __('app.all_categories') }}</option>
            @foreach ($categories as $cat)
              <option value="{{ $cat->code }}">{{ $cat->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3 text-muted small">
          <i class="ti tabler-filter me-1"></i>{{ __('app.filtered_summary', ['units' => $totalUnits, 'reports' => $totalReports]) }}
        </div>
      </div>
    </div>
  </div>

  {{-- Stat Cards --}}
  <div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="ti tabler-users icon-26px"></i>
              </span>
            </div>
            <div class="text-end">
              <span class="badge bg-label-primary">{{ __('app.system') }}</span>
            </div>
          </div>
          <h3 class="mb-1 fw-bold">{{ $totalUsers }}</h3>
          <p class="mb-2 text-muted fw-medium">{{ __('app.total_users') }}</p>
          <hr class="my-2">
          <div class="d-flex justify-content-between small text-muted">
            <span><i class="ti tabler-user-search me-1 text-primary"></i>{{ $totalConsultants }} consultant</span>
            <span><i class="ti tabler-tools me-1 text-success"></i>{{ $totalEngineers }} engineer</span>
          </div>
          <div class="small text-muted mt-1">
            <i class="ti tabler-building-community me-1"></i>{{ $totalUnits }} {{ __('app.unit') }}
            · {{ $totalTa ?? 0 }} TA
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-success">
                <i class="ti tabler-report icon-26px"></i>
              </span>
            </div>
            <div class="text-end">
              <span class="badge bg-label-success">{{ __('app.this_week_badge', ['count' => $reportsThisWeek]) }}</span>
            </div>
          </div>
          <h3 class="mb-1 fw-bold">{{ $totalReports }}</h3>
          <p class="mb-2 text-muted fw-medium">{{ __('app.total_reports') }}</p>
          <hr class="my-2">
          <div class="d-flex justify-content-between small text-muted">
            <span><i class="ti tabler-send me-1 text-info"></i>{{ $submittedReports }} {{ __('app.submitted_plus') }}</span>
            <span><i class="ti tabler-file me-1 text-warning"></i>{{ $draftReports }} {{ __('app.drafts_label') }}</span>
          </div>
          <div class="small text-muted mt-1"><i class="ti tabler-circle-check me-1 text-success"></i>{{ $completedReports }} {{ __('app.completed_label') }}</div>
          <div class="small text-muted mt-1">
            {{ __('app.pipeline_summary', ['visit' => $pendingSiteVisit ?? 0, 'engineer' => $pendingEngineer ?? 0, 'director' => $pendingDirector ?? 0]) }}
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-info">
                <i class="ti tabler-send icon-26px"></i>
              </span>
            </div>
            <div class="text-end">
              <span class="badge bg-label-info">{{ __('app.this_week_badge', ['count' => $submittedThisWeek]) }}</span>
            </div>
          </div>
          <h3 class="mb-1 fw-bold">{{ $submittedReports }}</h3>
          <p class="mb-2 text-muted fw-medium">{{ __('app.submitted_reports_label') }}</p>
          <hr class="my-2">
          <div class="small text-muted">
            <i class="ti tabler-checks me-1 text-success"></i>{{ __('app.ready_for_engineer') }}
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="ti tabler-map-pin icon-26px"></i>
              </span>
            </div>
            <div class="text-end">
              <span class="badge bg-label-warning">{{ __('app.categories_count', ['count' => $totalCategories]) }}</span>
            </div>
          </div>
          <h3 class="mb-1 fw-bold">{{ $mappedReports }}</h3>
          <p class="mb-2 text-muted fw-medium">{{ __('app.geolocated_gis') }}</p>
          <hr class="my-2">
          <div class="small text-muted">
            <i class="ti tabler-map-2 me-1 text-warning"></i>{{ __('app.can_show_on_map') }}
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Chart + Category --}}
  <div class="row g-4 mb-4">
    <div class="col-lg-4">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
          <h6 class="mb-0 fw-semibold">
            <i class="ti tabler-building-community me-2 text-primary"></i>{{ __('app.reports_by_unit') }}
          </h6>
        </div>
        <div class="card-body">
          @forelse ($reportsByUnit as $unit)
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span class="fw-medium">{{ $unit->name }}</span>
              <span class="badge bg-label-primary">{{ $unit->reports_count }}</span>
            </div>
          @empty
            <div class="text-muted text-center py-4">{{ __('app.no_units') }}</div>
          @endforelse
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
          <h6 class="mb-0 fw-semibold">
            <i class="ti tabler-chart-bar me-2 text-primary"></i>{{ __('app.activity_7_days') }}
          </h6>
          @php $weekTotal = $trendDays->sum('total'); @endphp
          <span class="badge bg-label-primary">{{ __('app.total_count', ['count' => $weekTotal]) }}</span>
        </div>
        <div class="card-body">
          <div class="d-flex align-items-end gap-2" style="height:120px;">
            @foreach ($trendDays as $day)
              @php $pct = max(8, (int) round(($day['total'] / $trendMax) * 100)); @endphp
              <div class="flex-fill text-center d-flex flex-column justify-content-end align-items-center" style="height:100%;">
                <div class="fw-bold text-primary small mb-1" style="font-size:11px;">
                  {{ $day['total'] ?: '' }}
                </div>
                <div class="w-100 mx-1 rounded-top"
                  style="height:{{ $pct }}%;min-height:6px;background:{{ $day['total'] ? 'var(--bs-primary)' : '#e0e0e0' }};transition:height .3s;cursor:default;"
                  title="{{ __('app.reports_on_day', ['count' => $day['total'], 'day' => $day['label']]) }}"></div>
                <div class="small text-muted mt-2" style="font-size:11px;">{{ $day['label'] }}</div>
              </div>
            @endforeach
          </div>
          <div class="mt-3 pt-2 border-top d-flex justify-content-between small text-muted">
            <span>{{ __('app.average') }}: <strong class="text-body">{{ $weekTotal > 0 ? round($weekTotal / 7, 1) : 0 }}{{ __('app.per_day') }}</strong></span>
            <span>{{ __('app.highest') }}: <strong class="text-body">{{ $trendMax }}</strong></span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      @include('livewire.partials.reports-by-category', [
        'reportsByCategory' => $reportsByCategory,
        'title' => __('app.by_category'),
        'headerAction' => '<a href="'.route('superadmin.categories').'" class="btn btn-sm btn-outline-primary"><i class="ti tabler-settings me-1"></i>'.e(__('app.manage')).'</a>',
      ])
    </div>
  </div>

  {{-- Recent Reports --}}
  <div class="card border-0 shadow-sm">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
      <h6 class="mb-0 fw-semibold">
        <i class="ti tabler-clock me-2 text-primary"></i>{{ __('app.recent_reports_title') }}
      </h6>
      <a href="{{ route('superadmin.reports') }}" class="btn btn-sm btn-outline-primary">
        <i class="ti tabler-list me-1"></i>{{ __('app.view_all') }}
      </a>
    </div>
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">{{ __('app.report_no') }}</th>
            <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">{{ __('app.title_location') }}</th>
            <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">{{ __('app.unit') }}</th>
            <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">{{ __('app.category') }}</th>
            <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">{{ __('app.surveyor') }}</th>
            <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">{{ __('app.status') }}</th>
            <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">{{ __('app.date') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($recentReports as $r)
            <tr>
              <td>
                <code class="bg-light px-2 py-1 rounded small">{{ $r->report_number }}</code>
              </td>
              <td>
                <div class="fw-medium">{{ $r->title }}</div>
                @if ($r->location_name)
                  <div class="small text-muted text-truncate d-flex align-items-center gap-1" style="max-width:220px;">
                    <i class="ti tabler-map-pin" style="font-size:12px;"></i>{{ $r->location_name }}
                  </div>
                @endif
              </td>
              <td>{{ $r->unit->name ?? '-' }}</td>
              <td>
                @if ($r->category)
                  <span class="badge bg-label-secondary">{{ $r->category->name }}</span>
                @else
                  <span class="text-muted">-</span>
                @endif
              </td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="avatar avatar-xs">
                    <span class="avatar-initial rounded-circle bg-label-primary small">
                      {{ strtoupper(substr($r->user->name ?? 'U', 0, 1)) }}
                    </span>
                  </div>
                  <span class="small">{{ $r->user->name ?? '-' }}</span>
                </div>
              </td>
              <td>{!! $r->status_badge !!}</td>
              <td class="text-nowrap small text-muted">{{ $r->created_at->format('d/m/Y') }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-5">
                <i class="ti tabler-inbox icon-32px d-block mb-2 text-muted"></i>
                {{ __('app.no_reports_in_system') }}
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
