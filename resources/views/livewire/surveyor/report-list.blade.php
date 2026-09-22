<div>
  @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible mb-4"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('message') }}</div>
  @endif

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">{{ __('app.my_report_list') }}</h5>
      <a href="{{ route('consultant.reports.create') }}" class="btn btn-primary btn-sm"><i class="ti tabler-plus me-1"></i>{{ __('app.create_report_btn') }}</a>
    </div>
    <div class="card-body">
      <div class="row mb-4">
        <div class="col-md-6"><input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="{{ __('app.search_reports') }}" /></div>
        <div class="col-md-3">
          <select wire:model.live="filterStatus" class="form-select">
            <option value="">{{ __('app.all_statuses') }}</option>
            <option value="draft">{{ __('app.status_draft') }}</option>
            <option value="submitted">{{ __('app.status_submitted') }}</option>
            <option value="pending_site_visit">{{ __('app.wf_pending_site_visit') }}</option>
            <option value="pending_engineer_verification">{{ __('app.pending_engineer') }}</option>
            <option value="engineer_returned">{{ __('app.returned') }}</option>
            <option value="pending_director_approval">{{ __('app.wf_pending_director_approval') }}</option>
            <option value="approved">{{ __('app.approved') }}</option>
            <option value="completed">{{ __('app.status_completed') }}</option>
          </select>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead><tr><th>{{ __('app.report_no') }}</th><th>{{ __('app.title') }}</th><th>{{ __('app.category') }}</th><th>{{ __('app.status') }}</th><th>{{ __('app.date') }}</th><th>{{ __('app.action') }}</th></tr></thead>
          <tbody>
            @forelse ($reports as $r)
            <tr>
              <td><code>{{ $r->report_number }}</code></td>
              <td>{{ $r->title }}</td>
              <td>{{ $r->category->name ?? '-' }}</td>
              <td>{!! $r->status_badge !!}</td>
              <td>{{ $r->created_at->format('d/m/Y') }}</td>
              <td>
                <div class="d-flex gap-1">
                  <a href="{{ route('consultant.reports.view', $r) }}" class="btn btn-sm btn-icon btn-text-info" title="{{ __('app.view') }}"><i class="ti tabler-eye"></i></a>
                  @can('update', $r)
                    <a href="{{ route('consultant.reports.edit', $r) }}" class="btn btn-sm btn-icon btn-text-secondary" title="{{ __('app.update') }}"><i class="ti tabler-pencil"></i></a>
                  @endcan
                </div>
              </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-4">{{ __('app.no_reports') }}</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-3">{{ $reports->links() }}</div>
    </div>
  </div>
</div>
