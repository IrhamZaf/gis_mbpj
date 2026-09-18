<div>
  @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible mb-4">
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('message') }}
    </div>
  @endif

  <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
      <div class="text-uppercase text-muted small fw-semibold mb-1 letter-spacing">{{ $unit->name }}</div>
      <div class="d-flex align-items-center gap-2 mb-1">
        <span class="rounded-circle d-inline-flex align-items-center justify-content-center"
          style="width:36px;height:36px;background:{{ $theme['soft'] }};color:{{ $theme['color'] }};">
          <i class="ti {{ $theme['icon'] }}"></i>
        </span>
        <h4 class="mb-0">{{ $category->display_name }}</h4>
        @if ($isReadOnly ?? false)
          <span class="badge bg-label-secondary"><i class="ti tabler-lock me-1"></i>{{ __('app.read_only_badge') }}</span>
        @endif
      </div>
      <div class="text-muted small d-flex flex-wrap align-items-center gap-1">
        <a href="{{ $dashboardUrl }}" class="text-decoration-none">{{ __('app.dashboard') }}</a>
        @foreach ($siblingCategories as $sib)
          <span class="text-muted">·</span>
          @if ($sib->id === $category->id)
            <span class="fw-semibold text-body">{{ $sib->display_name }}</span>
          @else
            <a href="{{ \App\Support\UnitModule::categoryRoute($unit->code, $sib->code) }}" class="text-decoration-none">{{ $sib->display_name }}</a>
          @endif
        @endforeach
      </div>
    </div>
    @if ($canCreate)
      <a href="{{ $createUrl }}"
        class="btn btn-primary" style="background:{{ $theme['color'] }};border-color:{{ $theme['color'] }};">
        <i class="ti tabler-plus me-1"></i>{{ __('app.add_report') }}
      </a>
    @endif
  </div>

  <div class="row g-3 mb-4">
    @foreach ([
      [__('app.total_cases'), $stats['total'], $theme['color']],
      [__('app.draft'), $stats['draft'], '#6c757d'],
      [__('app.pending_site_visit'), $stats['pending'], '#ffc107'],
      [__('app.in_progress'), $stats['in_progress'], '#0d6efd'],
      [__('app.completed'), $stats['completed'], '#198754'],
    ] as [$label, $val, $color])
      <div class="col-6 col-md">
        <div class="card border-0 shadow-sm h-100" style="border-bottom:3px solid {{ $color }} !important;">
          <div class="card-body py-3">
            <div class="fw-bold fs-4">{{ $val }}</div>
            <div class="small text-muted">{{ $label }}</div>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-header border-bottom" style="border-top:3px solid {{ $theme['color'] }};">
      <div class="row g-3 align-items-center">
        <div class="col-md-6">
          <input wire:model.live.debounce.300ms="search" type="text" class="form-control"
            placeholder="{{ __('app.search_cases') }}" />
        </div>
        <div class="col-md-4">
          <select wire:model.live="filterStatus" class="form-select">
            <option value="">{{ __('app.all_statuses') }}</option>
            <option value="draft">{{ __('app.draft') }}</option>
            <option value="pending">{{ __('app.pending_site_visit') }}</option>
            <option value="in_progress">{{ __('app.in_progress') }}</option>
            <option value="completed">{{ __('app.completed') }}</option>
          </select>
        </div>
        <div class="col-md-2 text-md-end">
          <span class="badge bg-label-{{ $theme['label'] }}">{{ $reports->total() }} {{ __('app.reports') }}</span>
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>{{ __('app.file_case_id') }}</th>
              <th>{{ __('app.title') }}</th>
              <th>{{ __('app.location') }}</th>
              <th>{{ __('app.date') }}</th>
              <th>{{ __('app.surveyor') }}</th>
              <th>{{ __('app.documents') }}</th>
              <th>{{ __('app.status') }}</th>
              <th>{{ __('app.site_visit') }}</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @forelse ($reports as $r)
              @php
                $docsUploaded = $r->attachments->count();
                $siteLabel = match (true) {
                  ! $r->workflow_status || $r->status === 'draft' => __('app.pending'),
                  in_array($r->workflow_status, ['site_visit_in_progress', 'engineer_returned'], true) => __('app.in_progress'),
                  $r->workflow_status === 'pending_site_visit' => __('app.pending'),
                  default => __('app.completed'),
                };
              @endphp
              <tr>
                <td>
                  <div><code>{{ $r->report_number }}</code></div>
                  @if ($r->file_number)<div class="small text-muted">{{ $r->file_number }}</div>@endif
                </td>
                <td>
                  <div class="fw-semibold">{{ $r->title }}</div>
                </td>
                <td class="small">{{ $r->location_name ?? '-' }}</td>
                <td class="small">{{ $r->created_at?->format('d/m/Y') }}</td>
                <td>{{ $r->user->name ?? '-' }}</td>
                <td>
                  <span class="badge {{ $docsTotal > 0 && $docsUploaded >= $docsTotal ? 'bg-label-success' : 'bg-label-warning' }}">
                    {{ $docsUploaded }}/{{ $docsTotal }}
                  </span>
                </td>
                <td>{!! $r->status_badge !!}</td>
                <td class="small">{{ $siteLabel }}</td>
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
                  <div class="mb-2"><i class="ti {{ $theme['icon'] }} ti-lg" style="color:{{ $theme['color'] }};"></i></div>
                  <div class="mb-3">{{ __('app.no_records') }}</div>
                  @if ($canCreate)
                    <a href="{{ $createUrl }}" class="btn btn-sm btn-primary"
                      style="background:{{ $theme['color'] }};border-color:{{ $theme['color'] }};">
                      <i class="ti tabler-plus me-1"></i>{{ __('app.add_report') }}
                    </a>
                  @endif
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    @if ($reports->total() > 0)
      <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="small text-muted">
          {{ __('app.showing', ['from' => $reports->firstItem() ?? 0, 'to' => $reports->lastItem() ?? 0, 'total' => $reports->total()]) }}
        </div>
        <div>{{ $reports->links() }}</div>
      </div>
    @endif
  </div>
</div>
