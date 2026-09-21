<div>
  <div class="card border-0 shadow-sm">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
      <div>
        <h5 class="mb-0">{{ __('app.report_list') }}</h5>
        <small class="text-muted">{{ __('app.unit_label_short', ['unit' => $unitName]) }}</small>
      </div>
    </div>
    <div class="card-body">
      <div class="row mb-4 g-3">
        <div class="col-md-5"><input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="{{ __('app.search_title_report_location') }}" /></div>
        <div class="col-md-3">
          <select wire:model.live="filterStatus" class="form-select">
            <option value="">{{ __('app.all_statuses') }}</option>
            <option value="pending_engineer_verification">{{ __('app.awaiting_verification_short') }}</option>
            <option value="engineer_returned">{{ __('app.returned') }}</option>
            <option value="pending_director_approval">{{ __('app.wf_pending_director_approval') }}</option>
            <option value="approved">{{ __('app.approved') }}</option>
            <option value="director_rejected">{{ __('app.wf_director_rejected') }}</option>
            <option value="pending_site_visit">{{ __('app.wf_pending_site_visit') }}</option>
            <option value="site_visit_in_progress">{{ __('app.wf_site_visit_in_progress') }}</option>
          </select>
        </div>
        <div class="col-md-4">
          <select wire:model.live="filterCategory" class="form-select">
            <option value="">{{ __('app.all_categories') }}</option>
            @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
          </select>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light"><tr><th>{{ __('app.report_no') }}</th><th>{{ __('app.title') }}</th><th>{{ __('app.category') }}</th><th>{{ __('app.status') }}</th><th>{{ __('app.surveyor') }}</th><th>{{ __('app.date') }}</th><th></th></tr></thead>
          <tbody>
            @forelse ($reports as $r)
            <tr>
              <td><code>{{ $r->report_number }}</code></td>
              <td>
                <div>{{ $r->title }}</div>
                <div class="small text-muted">{{ $r->location_name ?? '' }}</div>
              </td>
              <td>{{ $r->category->name ?? '-' }}</td>
              <td>{!! $r->status_badge !!}</td>
              <td>{{ $r->user->name ?? '-' }}</td>
              <td>{{ $r->submitted_at?->format('d/m/Y') ?? '-' }}</td>
              <td><a href="{{ route('engineer.reports.view', $r) }}" class="btn btn-sm btn-primary"><i class="ti tabler-eye me-1"></i>{{ __('app.view') }}</a></td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-4">{{ __('app.no_reports_for_your_unit') }}</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-3">{{ $reports->links() }}</div>
    </div>
  </div>
</div>
