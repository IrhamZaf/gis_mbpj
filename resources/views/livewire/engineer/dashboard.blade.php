<div>
  @include('livewire.partials.dashboard-welcome', [
    'user' => $user,
    'roleLabel' => __('app.role_engineer_unit', ['unit' => $unitName]),
    'subtitle' => __('app.engineer_dashboard_subtitle', ['unit' => $unitName]),
    'heroIcon' => 'tabler-tools',
    'unitTheme' => $unitTheme,
    'actions' => [
      ['label' => __('app.review_unit_reports'), 'url' => route('engineer.reports'), 'icon' => 'tabler-report-search', 'class' => 'btn-primary'],
      ['label' => __('app.interactive_map'), 'url' => route('engineer.map'), 'icon' => 'tabler-map', 'class' => 'btn-outline-primary'],
    ],
  ])

  @include('livewire.partials.units-overview', [
    'unitCards' => $unitCards,
    'grandTotal' => $grandTotal,
  ])

  {{-- Stat Cards --}}
  <div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-success">
                <i class="ti tabler-report-search icon-26px"></i>
              </span>
            </div>
            <span class="badge bg-label-success">{{ __('app.this_week_badge', ['count' => $submittedThisWeek]) }}</span>
          </div>
          <h3 class="mb-1 fw-bold">{{ $totalSubmitted }}</h3>
          <p class="mb-2 text-muted fw-medium">{{ __('app.unit_reports', ['unit' => $unitName]) }}</p>
          <hr class="my-2">
          <div class="small text-muted">
            <i class="ti tabler-calendar-today me-1 text-success"></i>{{ __('app.today_pending_hint', ['today' => $submittedToday, 'pending' => $pendingVerify ?? $underReview]) }}
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="ti tabler-calendar-week icon-26px"></i>
              </span>
            </div>
            <span class="badge bg-label-primary">{{ __('app.days_7') }}</span>
          </div>
          <h3 class="mb-1 fw-bold">{{ $submittedThisWeek }}</h3>
          <p class="mb-2 text-muted fw-medium">{{ __('app.this_week') }}</p>
          <hr class="my-2">
          <div class="small text-muted">
            <i class="ti tabler-trending-up me-1 text-primary"></i>{{ __('app.new_reports_received') }}
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
            <span class="badge bg-label-warning">GIS</span>
          </div>
          <h3 class="mb-1 fw-bold">{{ $mappedReports }}</h3>
          <p class="mb-2 text-muted fw-medium">{{ __('app.has_coordinates') }}</p>
          <hr class="my-2">
          <div class="small text-muted">
            <i class="ti tabler-map-2 me-1 text-warning"></i>{{ __('app.can_show_on_map') }}
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
                <i class="ti tabler-category icon-26px"></i>
              </span>
            </div>
            <span class="badge bg-label-info">{{ __('app.active') }}</span>
          </div>
          <h3 class="mb-1 fw-bold">{{ $totalCategories }}</h3>
          <p class="mb-2 text-muted fw-medium">{{ __('app.report_categories') }}</p>
          <hr class="my-2">
          <div class="small text-muted">
            <i class="ti tabler-folder me-1 text-info"></i>{{ __('app.available_types') }}
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
            <i class="ti tabler-chart-bar me-2 text-primary"></i>{{ __('app.trend_7_days') }}
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
                  style="height:{{ $pct }}%;min-height:6px;background:{{ $day['total'] ? 'var(--bs-success)' : '#e0e0e0' }};transition:height .3s;"
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

    <div class="col-lg-8">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom">
          <h6 class="mb-0 fw-semibold">
            <i class="ti tabler-category me-2 text-success"></i>{{ __('app.reports_by_category') }}
          </h6>
          <a href="{{ route('engineer.map') }}" class="btn btn-sm btn-outline-success">
            <i class="ti tabler-map me-1"></i>{{ __('app.view_on_map') }}
          </a>
        </div>
        <div class="card-body">
          @php
            $colors = ['success','primary','info','warning','danger','secondary'];
            $catTotal = $reportsByCategory->sum('reports_count');
          @endphp
          @forelse ($reportsByCategory as $i => $cat)
            @php
              $pct = $catTotal > 0 ? round(($cat->reports_count / $catTotal) * 100) : 0;
              $color = $colors[$i % count($colors)];
            @endphp
            <div class="mb-3">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="d-flex align-items-center gap-2">
                  <span class="badge bg-label-{{ $color }}" style="width:10px;height:10px;padding:0;border-radius:50%;display:inline-block;"></span>
                  <span class="fw-medium small">{{ $cat->name }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <span class="fw-semibold small">{{ $cat->reports_count }}</span>
                  <span class="text-muted small" style="min-width:36px;text-align:right;">{{ $pct }}%</span>
                </div>
              </div>
              <div class="progress" style="height:7px;border-radius:4px;">
                <div class="progress-bar bg-{{ $color }}" role="progressbar"
                  style="width:{{ $pct }}%;border-radius:4px;"
                  aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div>
          @empty
            <div class="text-center text-muted py-5">
              <i class="ti tabler-folder-off icon-40px d-block mb-2 text-muted"></i>
              <p class="mb-0">{{ __('app.no_reports_yet') }}</p>
            </div>
          @endforelse
        </div>
      </div>
    </div>
  </div>

  {{-- Recent Reports --}}
  <div class="card border-0 shadow-sm">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
      <h6 class="mb-0 fw-semibold">
        <i class="ti tabler-clock me-2 text-success"></i>{{ __('app.recent_reports_received') }}
      </h6>
      <a href="{{ route('engineer.reports') }}" class="btn btn-sm btn-outline-primary">
        <i class="ti tabler-list me-1"></i>{{ __('app.view_all') }}
      </a>
    </div>
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">{{ __('app.report_no') }}</th>
            <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">{{ __('app.title') }}</th>
            <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">{{ __('app.category') }}</th>
            <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">{{ __('app.surveyor') }}</th>
            <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">{{ __('app.submitted_date') }}</th>
            <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">{{ __('app.action') }}</th>
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
                  <div class="small text-muted d-flex align-items-center gap-1" style="font-size:12px;">
                    <i class="ti tabler-map-pin" style="font-size:11px;"></i>{{ Str::limit($r->location_name, 35) }}
                  </div>
                @endif
              </td>
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
                    <span class="avatar-initial rounded-circle bg-label-success small">
                      {{ strtoupper(substr($r->user->name ?? 'U', 0, 1)) }}
                    </span>
                  </div>
                  <span class="small">{{ $r->user->name ?? '-' }}</span>
                </div>
              </td>
              <td class="text-nowrap small text-muted">
                {{ $r->submitted_at?->format('d/m/Y H:i') ?? '-' }}
              </td>
              <td>
                <a href="{{ route('engineer.reports.view', $r) }}" class="btn btn-sm btn-primary">
                  <i class="ti tabler-eye me-1"></i>{{ __('app.view') }}
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center text-muted py-5">
                <i class="ti tabler-inbox icon-32px d-block mb-2 text-muted"></i>
                {{ __('app.no_reports_submitted_yet') }}
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
