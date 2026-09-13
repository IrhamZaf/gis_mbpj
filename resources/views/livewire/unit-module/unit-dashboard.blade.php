<div>
  @include('livewire.partials.dashboard-welcome', [
    'user' => $user,
    'roleLabel' => $unit->name,
    'subtitle' => __('app.dashboard_subtitle'),
    'heroIcon' => 'tabler-building-tunnel',
    'unitTheme' => $theme,
    'actions' => [
      ['label' => __('app.sinkhole'), 'url' => route('saliran-cerun.sinkhole'), 'icon' => 'tabler-circle-dotted', 'class' => 'btn-primary'],
      ['label' => __('app.cerun_runtuh'), 'url' => route('saliran-cerun.cerun'), 'icon' => 'tabler-mountain', 'class' => 'btn-outline-primary'],
    ],
  ])

  <div class="row g-3 mb-4">
    @foreach ([
      [__('app.total_cases'), $totalCases],
      [__('app.sinkhole'), $sinkholeCount],
      [__('app.cerun_runtuh'), $cerunCount],
      [__('app.pending_site_visit'), $pendingVisit],
      [__('app.completed_site_visit'), $completedVisit],
    ] as [$label, $val])
      <div class="col-6 col-xl">
        <div class="card border-0 shadow-sm h-100" style="border-bottom:3px solid {{ $theme['color'] }} !important;">
          <div class="card-body py-3">
            <div class="fw-bold fs-4">{{ $val }}</div>
            <div class="small text-muted">{{ $label }}</div>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="row g-4 mb-4">
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header border-bottom"><h6 class="mb-0">{{ __('app.cases_by_category') }}</h6></div>
        <div class="card-body">
          @foreach ($byCategory as $row)
            <div class="d-flex justify-content-between mb-2">
              <span>{{ $row['name'] }}</span>
              <strong>{{ $row['total'] }}</strong>
            </div>
          @endforeach
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header border-bottom"><h6 class="mb-0">{{ __('app.cases_by_month') }}</h6></div>
        <div class="card-body">
          <div class="d-flex align-items-end gap-2" style="height:120px;">
            @foreach ($months as $day)
              @php $pct = max(8, (int) round(($day['total'] / $trendMax) * 100)); @endphp
              <div class="flex-fill text-center d-flex flex-column justify-content-end align-items-center" style="height:100%;">
                <div class="w-100 rounded-top" style="height:{{ $pct }}%;min-height:6px;background:{{ $day['total'] ? $theme['color'] : '#e0e0e0' }};"></div>
                <div class="small text-muted mt-1">{{ $day['label'] }}</div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header border-bottom"><h6 class="mb-0">{{ __('app.map_filter') }}</h6></div>
        <div class="card-body">
          <select wire:model.live="filterCategory" class="form-select mb-2">
            <option value="">{{ __('app.all_categories') }}</option>
            @foreach ($categories as $c)
              <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
          </select>
          <select wire:model.live="filterStatus" class="form-select">
            <option value="">{{ __('app.all_statuses') }}</option>
            <option value="pending">{{ __('app.pending') }}</option>
            <option value="site_visit">{{ __('app.site_visit') }}</option>
            <option value="completed">{{ __('app.completed') }}</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header border-bottom"><h6 class="mb-0">{{ __('app.gis_map', ['unit' => $unit->name]) }}</h6></div>
    <div class="card-body p-0">
      <div id="sc-dash-map" style="height:420px;"></div>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-header border-bottom"><h6 class="mb-0">{{ __('app.recent_reports') }}</h6></div>
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th>{{ __('app.case_id') }}</th>
            <th>{{ __('app.category') }}</th>
            <th>{{ __('app.title') }}</th>
            <th>{{ __('app.status') }}</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($recent as $r)
            <tr>
              <td><code>{{ $r->report_number }}</code></td>
              <td>{{ $r->category->name ?? '-' }}</td>
              <td>{{ $r->title }}</td>
              <td>{!! $r->status_badge !!}</td>
              <td class="text-end">
                <a href="{{ route('saliran-cerun.cases.show', $r) }}" class="btn btn-sm btn-outline-primary">{{ __('app.open') }}</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted py-4">{{ __('app.no_cases') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@assets
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endassets

@script
<script>
  const points = @json($mapPoints);
  const openCaseLabel = @json(__('app.open_case'));

  const el = document.getElementById('sc-dash-map');
  if (el && window.L) {
    const map = L.map(el).setView([3.0738, 101.5183], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
    const group = [];
    points.forEach(p => {
      const color = p.cat === 'CERUN_RUNTUH' ? '#dc3545' : '#0dcaf0';
      const m = L.circleMarker([p.lat, p.lng], { radius: 8, color, fillColor: color, fillOpacity: 0.85 }).addTo(map);
      m.bindPopup(`<strong>${p.case}</strong><br>${p.catName}<br>${p.title}<br>${p.status}<br><a href="${p.url}">${openCaseLabel}</a>`);
      group.push(m);
    });
    if (group.length) {
      map.fitBounds(L.featureGroup(group).getBounds().pad(0.2));
    }
  }
</script>
@endscript
