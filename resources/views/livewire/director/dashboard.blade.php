<div>
  @include('livewire.partials.dashboard-welcome', [
    'user' => $user,
    'roleLabel' => __('app.role_director'),
    'subtitle' => __('app.director_dashboard_subtitle'),
    'heroIcon' => 'tabler-stamp',
    'actions' => [
      ['label' => __('app.pending_approval'), 'url' => route('director.reports'), 'icon' => 'tabler-list-check', 'class' => 'btn-primary'],
      ['label' => __('app.map'), 'url' => route('director.map'), 'icon' => 'tabler-map', 'class' => 'btn-outline-primary'],
    ],
  ])

  @include('livewire.partials.units-overview', [
    'unitCards' => $unitCards,
    'grandTotal' => $grandTotal,
  ])

  <div class="row g-4 mb-4">
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><h3>{{ $pending }}</h3><p class="mb-0 text-muted">{{ __('app.pending_approval') }}</p></div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><h3>{{ $approved }}</h3><p class="mb-0 text-muted">{{ __('app.approved') }}</p></div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><h3>{{ $rejected }}</h3><p class="mb-0 text-muted">{{ __('app.rejected') }}</p></div></div></div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-header border-bottom"><h6 class="mb-0">{{ __('app.recent_list') }}</h6></div>
    <div class="table-responsive">
      <table class="table mb-0 align-middle">
        <thead class="table-light"><tr><th>{{ __('app.file_number') }}</th><th>{{ __('app.title') }}</th><th>{{ __('app.unit') }}</th><th>Engineer</th><th>{{ __('app.status') }}</th><th></th></tr></thead>
        <tbody>
          @forelse ($recent as $r)
            <tr>
              <td><code>{{ $r->file_number ?? $r->report_number }}</code></td>
              <td>{{ $r->title }}</td>
              <td>{{ $r->unit->name ?? '-' }}</td>
              <td>{{ $r->latestEngineerVerification?->engineer?->name ?? '-' }}</td>
              <td>{!! $r->status_badge !!}</td>
              <td class="text-end"><a href="{{ route('director.reports.view', $r) }}" class="btn btn-sm btn-primary">{{ __('app.review') }}</a></td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted py-4">{{ __('app.no_records') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
