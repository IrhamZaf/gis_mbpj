<div>
  <div class="card border-0 shadow-sm">
    <div class="card-header border-bottom"><h5 class="mb-0">{{ __('app.report_monitoring') }}</h5></div>
    <div class="card-body">
      <div class="row mb-4 g-3">
        <div class="col-md-3"><input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="{{ __('app.search_reports') }}" /></div>
        <div class="col-md-2">
          <select wire:model.live="filterUnit" class="form-select">
            <option value="">{{ __('app.all_units') }}</option>
            @foreach($units as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
          </select>
        </div>
        <div class="col-md-2">
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
        <div class="col-md-2">
          <select wire:model.live="filterCategory" class="form-select">
            <option value="">{{ __('app.all_categories') }}</option>
            @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->display_name }}</option>@endforeach
          </select>
        </div>
        <div class="col-md-3">
          <select wire:model.live="filterConsultant" class="form-select">
            <option value="">{{ __('app.all_surveyors') }}</option>
            @foreach($consultants as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
          </select>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light"><tr>
            <th>{{ __('app.report_no') }}</th>
            <th>{{ __('app.title') }}</th>
            <th>{{ __('app.unit') }}</th>
            <th>{{ __('app.category') }}</th>
            <th>{{ __('app.surveyor') }}</th>
            <th>{{ __('app.status') }}</th>
            <th>{{ __('app.date') }}</th>
            <th></th>
          </tr></thead>
          <tbody>
            @forelse ($reports as $r)
            <tr>
              <td><code>{{ $r->report_number }}</code></td>
              <td>{{ $r->title }}</td>
              <td>{{ $r->unit->name ?? '-' }}</td>
              <td>{{ $r->category?->display_name ?? '-' }}</td>
              <td>{{ $r->user->name ?? '-' }}</td>
              <td>{!! $r->status_badge !!}</td>
              <td>{{ $r->created_at->format('d/m/Y') }}</td>
              <td class="text-end text-nowrap">
                @php
                  $unitCode = $r->unit?->code;
                  $showUrl = ($unitCode && \App\Support\UnitModule::unitSlugFromCode($unitCode))
                    ? \App\Support\UnitModule::caseShowRoute($unitCode, $r)
                    : route('superadmin.reports.show', $r);
                @endphp
                <a href="{{ $showUrl }}" class="btn btn-sm btn-outline-primary">{{ __('app.view') }}</a>
                @if ($r->siteVisit)
                  <a href="{{ route('site-visits.form', $r) }}" class="btn btn-sm btn-outline-secondary">{{ __('app.site_visit') }}</a>
                @endif
              </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center py-4">{{ __('app.no_reports') }}</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
        <div class="small text-muted">
          {{ __('app.showing', [
            'from' => $reports->firstItem() ?? 0,
            'to' => $reports->lastItem() ?? 0,
            'total' => $reports->total(),
          ]) }}
        </div>
        <div>
          {{ $reports->links() }}
        </div>
      </div>
    </div>
  </div>
</div>
