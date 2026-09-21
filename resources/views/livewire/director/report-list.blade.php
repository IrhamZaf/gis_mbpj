<div>
  <div class="card border-0 shadow-sm">
    <div class="card-header border-bottom"><h5 class="mb-0">{{ __('app.director_approvals') }}</h5></div>
    <div class="card-body">
      <div class="row g-3 mb-4">
        <div class="col-md-6"><input wire:model.live.debounce.300ms="search" class="form-control" placeholder="{{ __('app.search_file_title') }}" /></div>
        <div class="col-md-4">
          <select wire:model.live="filterStatus" class="form-select">
            <option value="">{{ __('app.all') }}</option>
            <option value="pending_director_approval">{{ __('app.pending_approval') }}</option>
            <option value="approved">{{ __('app.approved') }}</option>
            <option value="director_rejected">{{ __('app.rejected') }}</option>
          </select>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr><th>{{ __('app.file_number') }}</th><th>{{ __('app.title') }}</th><th>{{ __('app.unit') }}</th><th>{{ __('app.ta') }}</th><th>Engineer</th><th>{{ __('app.status') }}</th><th></th></tr>
          </thead>
          <tbody>
            @forelse ($reports as $r)
              <tr>
                <td><code>{{ $r->file_number ?? $r->report_number }}</code></td>
                <td>{{ $r->title }}</td>
                <td>{{ $r->unit->name ?? '-' }}</td>
                <td>{{ $r->siteVisit?->ta?->name ?? '-' }}</td>
                <td>{{ $r->latestEngineerVerification?->engineer?->name ?? '-' }}</td>
                <td>{!! $r->status_badge !!}</td>
                <td class="text-end"><a href="{{ route('director.reports.view', $r) }}" class="btn btn-sm btn-primary">{{ __('app.review_and_approve') }}</a></td>
              </tr>
            @empty
              <tr><td colspan="7" class="text-center py-4 text-muted">{{ __('app.no_reports') }}</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-3">{{ $reports->links() }}</div>
    </div>
  </div>
</div>
