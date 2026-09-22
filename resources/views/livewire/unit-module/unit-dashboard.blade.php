<div>
  {{-- Header + Unit switcher + filters --}}
  <div class="card border-0 shadow-sm mb-4" style="border-top:4px solid {{ $theme['color'] }} !important;">
    <div class="card-body">
      <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
          <div class="text-muted small text-uppercase fw-semibold">{{ config('variables.templateName') }}</div>
          <h4 class="mb-1 d-flex align-items-center gap-2">
            <span class="rounded-circle d-inline-flex align-items-center justify-content-center"
              style="width:36px;height:36px;background:{{ $theme['soft'] }};color:{{ $theme['color'] }};">
              <i class="ti {{ $unitIcon }}"></i>
            </span>
            {{ __('app.dashboard') }} — {{ $unit->name }}
          </h4>
          <div class="text-muted small">
            @if ($isReadOnly)
              <span class="badge bg-label-secondary me-1"><i class="ti tabler-lock me-1"></i>{{ __('app.read_only_badge') }}</span>
              {{ __('app.read_only_body', ['unit' => $unit->name]) }}
            @else
              {{ __('app.dashboard_subtitle_unit', ['unit' => $unit->name]) }}
            @endif
          </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
          @foreach ($quickActions as $action)
            <a href="{{ $action['url'] }}" class="btn btn-sm {{ $action['class'] }}">
              <i class="ti {{ $action['icon'] }} me-1"></i>{{ $action['label'] }}
            </a>
          @endforeach
        </div>
      </div>

      <div class="row g-3 align-items-end">
        <div class="col-md-3 col-lg-2">
          <label class="form-label small text-muted mb-1">{{ __('app.unit') }}</label>
          <select wire:model.live="unitCode" class="form-select">
            @foreach ($navUnits as $u)
              <option value="{{ $u->code }}">{{ $u->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small text-muted mb-1">{{ __('app.year') }}</label>
          <select wire:model.live="filterYear" class="form-select">
            @foreach ($availableYears as $y)
              <option value="{{ $y }}">{{ $y }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3 col-lg-2">
          <label class="form-label small text-muted mb-1">{{ __('app.category') }}</label>
          <select wire:model.live="filterCategory" class="form-select">
            <option value="">{{ __('app.all_categories') }}</option>
            @foreach ($categories as $c)
              <option value="{{ $c->id }}">{{ $c->display_name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3 col-lg-3">
          <label class="form-label small text-muted mb-1">{{ __('app.status') }}</label>
          <select wire:model.live="filterStatus" class="form-select">
            <option value="">{{ __('app.all_statuses') }}</option>
            <option value="draft">{{ __('app.status_draft') }}</option>
            <option value="pending_site_visit">{{ __('app.wf_pending_site_visit') }}</option>
            <option value="site_visit_in_progress">{{ __('app.wf_site_visit_in_progress') }}</option>
            <option value="pending_engineer_verification">{{ __('app.wf_pending_engineer_verification') }}</option>
            <option value="pending_director_approval">{{ __('app.wf_pending_director_approval') }}</option>
            <option value="approved">{{ __('app.wf_approved') }}</option>
            <option value="director_rejected">{{ __('app.wf_director_rejected') }}</option>
            <option value="completed">{{ __('app.status_completed') }}</option>
          </select>
        </div>
        <div class="col-md-2 col-lg-3">
          <button type="button" wire:click="resetFilters" class="btn btn-outline-secondary w-100">
            <i class="ti tabler-refresh me-1"></i>{{ __('app.reset_filters') }}
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- KPI cards --}}
  <div class="row g-3 mb-4">
    @foreach ($kpi as $card)
      <div class="col-6 col-md-4 col-xl-2">
        <a href="{{ $card['url'] }}" class="text-decoration-none text-body">
          <div class="card border-0 shadow-sm h-100" style="border-bottom:3px solid {{ $card['color'] }} !important;">
            <div class="card-body py-3">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="rounded-circle d-inline-flex align-items-center justify-content-center"
                  style="width:32px;height:32px;background:{{ $card['color'] }}22;color:{{ $card['color'] }};">
                  <i class="ti {{ $card['icon'] }}"></i>
                </span>
              </div>
              <div class="fw-bold fs-4 lh-1">{{ number_format($card['value']) }}</div>
              <div class="small fw-semibold mt-1">{{ $card['label'] }}</div>
              <div class="small text-muted">{{ $card['hint'] }}</div>
            </div>
          </div>
        </a>
      </div>
    @endforeach
  </div>

  @if ($totalReports === 0)
    <div class="alert alert-light border text-center py-5 mb-4">
      <i class="ti tabler-folder-off icon-32px text-muted d-block mb-2"></i>
      <div class="fw-semibold">{{ __('app.no_reports_found') }}</div>
      <div class="text-muted small">{{ __('app.no_reports_for_unit') }}</div>
    </div>
  @endif

  <div class="row g-4 mb-4">
    {{-- Report by Category --}}
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header border-bottom"><h6 class="mb-0">{{ __('app.report_by_category') }}</h6></div>
        <div class="card-body">
          @forelse ($byCategory as $row)
            <a href="{{ $row['url'] }}" class="text-decoration-none text-body d-block mb-3">
              <div class="d-flex justify-content-between small mb-1">
                <span>{{ $row['name'] }}</span>
                <strong>{{ $row['total'] }}</strong>
              </div>
              <div class="progress" style="height:8px;">
                <div class="progress-bar" role="progressbar"
                  style="width:{{ max($row['total'] ? 6 : 0, (int) round(($row['total'] / $categoryMax) * 100)) }}%;background:{{ $row['color'] }};">
                </div>
              </div>
            </a>
          @empty
            <div class="text-muted small text-center py-4">{{ __('app.no_chart_data') }}</div>
          @endforelse
        </div>
      </div>
    </div>

    {{-- Report Status --}}
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header border-bottom"><h6 class="mb-0">{{ __('app.report_status') }}</h6></div>
        <div class="card-body">
          @php $hasStatus = collect($statusBreakdown)->sum('total') > 0; @endphp
          @if ($hasStatus)
            @foreach ($statusBreakdown as $row)
              <div class="mb-2">
                <div class="d-flex justify-content-between small mb-1">
                  <span class="d-inline-flex align-items-center gap-1">
                    <span style="width:8px;height:8px;border-radius:50%;background:{{ $row['color'] }};display:inline-block;"></span>
                    {{ $row['label'] }}
                  </span>
                  <strong>{{ $row['total'] }}</strong>
                </div>
                <div class="progress" style="height:6px;">
                  <div class="progress-bar" style="width:{{ max($row['total'] ? 4 : 0, (int) round(($row['total'] / $statusMax) * 100)) }}%;background:{{ $row['color'] }};"></div>
                </div>
              </div>
            @endforeach
          @else
            <div class="text-muted small text-center py-4">{{ __('app.no_chart_data') }}</div>
          @endif
        </div>
      </div>
    </div>

    {{-- Site Visit --}}
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header border-bottom"><h6 class="mb-0">{{ __('app.site_visit') }}</h6></div>
        <div class="card-body">
          @foreach ([
            ['label' => __('app.pending'), 'total' => $siteVisitSummary['pending'], 'color' => '#ffc107', 'url' => \App\Support\UnitModule::categoryRoute($unit->code, 'SINKHOLE').'?filterStatus=pending'],
            ['label' => __('app.in_progress'), 'total' => $siteVisitSummary['in_progress'], 'color' => '#0d6efd', 'url' => \App\Support\UnitModule::categoryRoute($unit->code, 'SINKHOLE').'?filterStatus=in_progress'],
            ['label' => __('app.completed'), 'total' => $siteVisitSummary['completed'], 'color' => '#198754', 'url' => \App\Support\UnitModule::categoryRoute($unit->code, 'SINKHOLE').'?filterStatus=completed'],
          ] as $sv)
            <a href="{{ $sv['url'] }}" class="text-decoration-none text-body d-block mb-3">
              <div class="d-flex justify-content-between small mb-1">
                <span>{{ $sv['label'] }}</span>
                <strong>{{ $sv['total'] }}</strong>
              </div>
              <div class="progress" style="height:8px;">
                <div class="progress-bar" style="width:{{ max($sv['total'] ? 6 : 0, (int) round(($sv['total'] / $siteVisitMax) * 100)) }}%;background:{{ $sv['color'] }};"></div>
              </div>
            </a>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  {{-- Monthly trend --}}
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
      <h6 class="mb-0">{{ __('app.monthly_report_trend') }}</h6>
      <span class="badge bg-label-{{ $theme['label'] }}">{{ $filterYear }}</span>
    </div>
    <div class="card-body">
      @if ($months->sum('total') === 0)
        <div class="text-muted small text-center py-4">{{ __('app.no_chart_data') }}</div>
      @else
        <div class="d-flex align-items-end gap-1 gap-md-2" style="height:160px;">
          @foreach ($months as $day)
            @php $pct = $day['total'] ? max(8, (int) round(($day['total'] / $trendMax) * 100)) : 0; @endphp
            <div class="flex-fill text-center d-flex flex-column justify-content-end align-items-center" style="height:100%;" title="{{ $day['label'] }}: {{ $day['total'] }}">
              <div class="small text-muted mb-1">{{ $day['total'] ?: '' }}</div>
              <div class="w-100 rounded-top" style="height:{{ $pct }}%;min-height:{{ $day['total'] ? '6px' : '2px' }};background:{{ $day['total'] ? $theme['color'] : '#e9ecef' }};"></div>
              <div class="small text-muted mt-1">{{ $day['label'] }}</div>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>

  {{-- GIS Map --}}
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
      <h6 class="mb-0">{{ __('app.report_location') }}</h6>
      <span class="badge bg-label-primary">{{ count($mapPoints) }} {{ __('app.markers') }}</span>
    </div>
    <div class="card-body p-0 position-relative">
      @if (count($mapPoints) === 0)
        <div class="text-center text-muted py-5">
          <i class="ti tabler-map-off icon-32px d-block mb-2"></i>
          {{ __('app.no_geo_reports') }}
        </div>
      @endif
      <div id="unit-dash-map" wire:ignore style="height:420px;{{ count($mapPoints) ? '' : 'display:none;' }}"></div>
    </div>
  </div>

  {{-- Recent Reports --}}
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
      <h6 class="mb-0">{{ __('app.recent_reports') }}</h6>
      <a href="{{ \App\Support\UnitModule::categoryRoute($unit->code, 'SINKHOLE') }}" class="small">{{ __('app.view_all_reports') }}</a>
    </div>
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th style="width:48px;">{{ __('app.no') }}</th>
            <th>{{ __('app.report_no') }}</th>
            <th>{{ __('app.category') }}</th>
            <th>{{ __('app.location') }}</th>
            <th>{{ __('app.surveyor') }}</th>
            <th>{{ __('app.status') }}</th>
            <th>{{ __('app.site_visit') }}</th>
            <th>{{ __('app.date') }}</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($recent as $i => $r)
            @php
              $siteLabel = match (true) {
                ! $r->workflow_status || $r->status === 'draft' => __('app.pending'),
                in_array($r->workflow_status, ['site_visit_in_progress', 'engineer_returned'], true) => __('app.in_progress'),
                $r->workflow_status === 'pending_site_visit' => __('app.pending'),
                default => __('app.completed'),
              };
            @endphp
            <tr>
              <td class="text-muted">{{ $i + 1 }}</td>
              <td><code>{{ $r->report_number }}</code></td>
              <td>{{ $r->category?->display_name ?? '-' }}</td>
              <td class="small">{{ $r->location_name ?? '-' }}</td>
              <td>{{ $r->user->name ?? '-' }}</td>
              <td>{!! $r->status_badge !!}</td>
              <td class="small">{{ $siteLabel }}</td>
              <td class="small">{{ $r->created_at?->format('d/m/Y') }}</td>
              <td class="text-end text-nowrap">
                <a href="{{ \App\Support\UnitModule::caseShowRoute($unit->code, $r) }}" class="btn btn-sm btn-outline-primary">{{ __('app.view') }}</a>
                @can('update', $r)
                  <a href="{{ \App\Support\UnitModule::caseEditRoute($unit->code, $r) }}" class="btn btn-sm btn-outline-secondary">{{ __('app.edit') }}</a>
                @endcan
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="text-center text-muted py-5">
                <div class="fw-semibold mb-1">{{ __('app.no_reports_found') }}</div>
                <div class="small">{{ __('app.no_reports_for_unit') }}</div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Quick Actions footer --}}
  <div class="card border-0 shadow-sm">
    <div class="card-header border-bottom"><h6 class="mb-0">{{ __('app.quick_actions') }}</h6></div>
    <div class="card-body d-flex flex-wrap gap-2">
      @foreach ($quickActions as $action)
        <a href="{{ $action['url'] }}" class="btn {{ $action['class'] }}">
          <i class="ti {{ $action['icon'] }} me-1"></i>{{ $action['label'] }}
        </a>
      @endforeach
      @foreach ($categories as $c)
        <a href="{{ \App\Support\UnitModule::categoryRoute($unit->code, $c->code) }}" class="btn btn-outline-secondary">
          <i class="ti {{ \App\Support\UnitTheme::forCategory($c->code)['icon'] }} me-1"></i>{{ $c->display_name }}
        </a>
      @endforeach
    </div>
  </div>
</div>

@assets
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endassets

@script
<script>
  const openCaseLabel = @json(__('app.open_case'));
  const viewReportLabel = @json(__('app.view'));
  let dashMap = null;
  let dashLayer = null;

  function categoryColor(cat) {
    if (cat === 'CERUN' || cat === 'CERUN_RUNTUH') return '#dc3545';
    if (cat === 'BOREHOLE') return '#6c757d';
    return '#0dcaf0';
  }

  function renderDashMap(points) {
    const el = document.getElementById('unit-dash-map');
    if (!el || !window.L) return;

    if (!points || !points.length) {
      el.style.display = 'none';
      return;
    }
    el.style.display = '';

    if (!dashMap) {
      dashMap = L.map(el, {
        center: [3.0565, 101.5851],
        zoom: 13,
        maxBounds: [[2.97, 101.51], [3.165, 101.69]],
        maxBoundsViscosity: 1.0,
        minZoom: 12,
      });
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(dashMap);
      L.rectangle([[2.97, 101.51], [3.165, 101.69]], {
        color: '#0d6efd', weight: 2, dashArray: '6 4', fill: false, interactive: false
      }).addTo(dashMap);
      dashLayer = L.layerGroup().addTo(dashMap);
    }

    dashLayer.clearLayers();
    const bounds = [];
    points.forEach(p => {
      if (p.lat < 2.97 || p.lat > 3.165 || p.lng < 101.51 || p.lng > 101.69) return;
      const color = categoryColor(p.cat);
      const m = L.circleMarker([p.lat, p.lng], {
        radius: 8, color, fillColor: color, fillOpacity: 0.85
      }).addTo(dashLayer);
      m.bindPopup(
        `<strong>${p.case}</strong><br>${p.catName}<br>${p.location || '-'}<br>${p.status}` +
        `<br><a href="${p.url}">${viewReportLabel}</a>`
      );
      bounds.push([p.lat, p.lng]);
    });

    if (bounds.length) {
      dashMap.fitBounds(bounds, { padding: [30, 30], maxZoom: 16 });
    } else {
      dashMap.setView([3.0565, 101.5851], 13);
    }
    setTimeout(() => dashMap.invalidateSize(), 120);
  }

  renderDashMap(@json($mapPoints));

  Livewire.on('dash-map-updated', (payload) => {
    const points = Array.isArray(payload) ? payload[0]?.points ?? payload.points : payload?.points;
    renderDashMap(points || []);
  });
</script>
@endscript
