<div>
  @include('livewire.partials.dashboard-welcome', [
    'user' => $user,
    'roleLabel' => __('app.role_consultant'),
    'subtitle' => __('app.consultant_dashboard_subtitle'),
    'heroIcon' => 'tabler-clipboard-list',
    'unitTheme' => $unitTheme,
    'actions' => [
      ['label' => __('app.add_report'), 'url' => route('consultant.reports.create'), 'icon' => 'tabler-plus', 'class' => 'btn-primary'],
      ['label' => __('app.my_reports'), 'url' => route('consultant.reports'), 'icon' => 'tabler-list', 'class' => 'btn-outline-primary'],
      ['label' => __('app.interactive_map'), 'url' => route('consultant.map'), 'icon' => 'tabler-map', 'class' => 'btn-outline-secondary'],
    ],
  ])

  @if ($returnedReports > 0)
    <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4" role="alert">
      <div class="d-flex align-items-center gap-2">
        <i class="ti tabler-arrow-back-up fs-5"></i>
        <div>
          <strong>{{ __('app.returned_reports_alert', ['count' => $returnedReports]) }}</strong>
          {{ __('app.please_update_resubmit') }}
        </div>
      </div>
      <a href="{{ route('consultant.reports') }}" class="btn btn-sm btn-danger">{{ __('app.review_reports') }}</a>
    </div>
  @elseif ($draftReports > 0)
    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4" role="alert">
      <div class="d-flex align-items-center gap-2">
        <i class="ti tabler-alert-triangle fs-5 text-warning"></i>
        <div>
          <strong>{{ __('app.draft_reports_alert', ['count' => $draftReports]) }}</strong>
          {{ __('app.complete_and_submit') }}
        </div>
      </div>
      <a href="{{ route('consultant.reports') }}" class="btn btn-sm btn-warning">
        <i class="ti tabler-pencil me-1"></i>{{ __('app.review_drafts') }}
      </a>
    </div>
  @endif

  {{-- My personal KPIs --}}
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h6 class="mb-0 fw-semibold">{{ __('app.my_reports_summary') }}</h6>
    <span class="badge bg-label-primary">{{ $unitName }}</span>
  </div>
  <div class="row g-3 mb-4">
    @foreach ([
      ['value' => $totalReports, 'label' => __('app.total_reports'), 'hint' => '+'.$reportsThisWeek.' '.__('app.this_week'), 'icon' => 'tabler-report', 'color' => '#0d6efd'],
      ['value' => $draftReports, 'label' => __('app.draft'), 'hint' => __('app.awaiting_submit'), 'icon' => 'tabler-file-text', 'color' => '#ffc107'],
      ['value' => $submittedReports, 'label' => __('app.status_submitted'), 'hint' => __('app.in_review'), 'icon' => 'tabler-send', 'color' => '#198754'],
      ['value' => $returnedReports, 'label' => __('app.returned'), 'hint' => $completedReports.' '.__('app.completed'), 'icon' => 'tabler-arrow-back-up', 'color' => '#dc3545'],
    ] as $card)
      <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100" style="border-bottom:3px solid {{ $card['color'] }} !important;">
          <div class="card-body py-3">
            <div class="d-flex align-items-center gap-2 mb-2">
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
      </div>
    @endforeach
  </div>

  {{-- All Units overview --}}
  @include('livewire.partials.units-overview', [
    'unitCards' => $unitCards,
    'grandTotal' => $grandTotal,
  ])

  {{-- Recent + Quick actions --}}
  <div class="row g-4">
    <div class="col-xl-8">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
          <h6 class="mb-0 fw-semibold">
            <i class="ti tabler-clock me-2 text-primary"></i>{{ __('app.my_recent_reports') }}
          </h6>
          <a href="{{ route('consultant.reports.create') }}" class="btn btn-primary btn-sm">
            <i class="ti tabler-plus me-1"></i>{{ __('app.add_report') }}
          </a>
        </div>
        <div class="table-responsive">
          <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
              <tr>
                <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">{{ __('app.report_no') }}</th>
                <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">{{ __('app.title') }}</th>
                <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">{{ __('app.category') }}</th>
                <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">{{ __('app.status') }}</th>
                <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">{{ __('app.date') }}</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              @forelse ($recentReports as $r)
                <tr>
                  <td><code class="bg-light px-2 py-1 rounded small">{{ $r->report_number }}</code></td>
                  <td>
                    <div class="fw-medium">{{ $r->title }}</div>
                    @if ($r->location_name)
                      <div class="small text-muted"><i class="ti tabler-map-pin me-1"></i>{{ Str::limit($r->location_name, 30) }}</div>
                    @endif
                  </td>
                  <td>
                    @if ($r->category)
                      <span class="badge bg-label-secondary">{{ $r->category->display_name }}</span>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                  <td>{!! $r->status_badge !!}</td>
                  <td class="text-nowrap small text-muted">{{ $r->created_at->format('d/m/Y') }}</td>
                  <td class="text-end text-nowrap">
                    <a href="{{ route('consultant.reports.view', $r) }}" class="btn btn-sm btn-outline-primary">{{ __('app.view') }}</a>
                    @can('update', $r)
                      <a href="{{ route('consultant.reports.edit', $r) }}" class="btn btn-sm btn-outline-secondary">{{ __('app.edit') }}</a>
                    @endcan
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center py-5 text-muted">
                    <i class="ti tabler-inbox icon-32px d-block mb-2"></i>
                    {{ __('app.no_reports_found') }}
                    <a href="{{ route('consultant.reports.create') }}">{{ __('app.add_report') }}</a>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-xl-4">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-header border-bottom">
          <h6 class="mb-0 fw-semibold">
            <i class="ti tabler-bolt me-2 text-warning"></i>{{ __('app.quick_actions') }}
          </h6>
        </div>
        <div class="card-body d-flex flex-column gap-3">
          <a href="{{ route('consultant.reports.create') }}"
            class="d-flex align-items-center gap-3 p-3 rounded border text-decoration-none text-body"
            style="background:rgba(13,110,253,.04);">
            <span class="avatar flex-shrink-0"><span class="avatar-initial rounded bg-label-primary"><i class="ti tabler-plus"></i></span></span>
            <div>
              <div class="fw-semibold">{{ __('app.add_report') }}</div>
              <div class="small text-muted">{{ $unitName }}</div>
            </div>
            <i class="ti tabler-chevron-right ms-auto text-muted"></i>
          </a>

          <a href="{{ route('consultant.map') }}"
            class="d-flex align-items-center gap-3 p-3 rounded border text-decoration-none text-body"
            style="background:rgba(25,135,84,.04);">
            <span class="avatar flex-shrink-0"><span class="avatar-initial rounded bg-label-success"><i class="ti tabler-map"></i></span></span>
            <div>
              <div class="fw-semibold">{{ __('app.interactive_map') }}</div>
              <div class="small text-muted">{{ __('app.all_units') }}</div>
            </div>
            <i class="ti tabler-chevron-right ms-auto text-muted"></i>
          </a>

          @foreach ($unitCards as $card)
            <a href="{{ $card['dashboardUrl'] }}"
              class="d-flex align-items-center gap-3 p-3 rounded border text-decoration-none text-body">
              <span class="avatar flex-shrink-0">
                <span class="avatar-initial rounded" style="background:{{ $card['theme']['soft'] }};color:{{ $card['theme']['color'] }};">
                  <i class="ti {{ $card['icon'] }}"></i>
                </span>
              </span>
              <div>
                <div class="fw-semibold">{{ $card['unit']->name }}</div>
                <div class="small text-muted">{{ number_format($card['total']) }} {{ __('app.reports') }}</div>
              </div>
              <i class="ti tabler-chevron-right ms-auto text-muted"></i>
            </a>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>
