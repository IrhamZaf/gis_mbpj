<div>
  @if (session()->has('message'))
    <div class="alert alert-success mb-4">{{ session('message') }}</div>
  @endif

  <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
    <div>
      <h4 class="mb-1">{{ __('app.case_label', ['number' => $report->report_number]) }}</h4>
      <div class="text-muted small">
        {{ $report->unit->name ?? '-' }} · {{ $report->category->name ?? '-' }} · {!! $report->status_badge !!}
      </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
      @if ($report->status === 'draft' && (auth()->id() === $report->user_id || auth()->user()->isSuperadmin()))
        <a href="{{ route('saliran-cerun.cases.edit', $report) }}" class="btn btn-outline-primary">{{ __('app.edit') }}</a>
        <button type="button" wire:click="submitCase" class="btn btn-primary"
          wire:confirm="{{ __('app.confirm_submit_short') }}">{{ __('app.submit') }}</button>
      @endif
      @can('downloadPdf', $report)
        <a href="{{ route('reports.site-visit-pdf', $report) }}" class="btn btn-outline-danger" target="_blank">
          <i class="ti tabler-printer me-1"></i>{{ __('app.generate_pdf') }}
        </a>
      @endcan
    </div>
  </div>

  <ul class="nav nav-tabs mb-4">
    @foreach ([
      'overview' => __('app.overview'),
      'gis' => __('app.gis_location'),
      'documents' => __('app.documents'),
      'site_visit' => __('app.site_visit'),
      'audit' => __('app.audit_trail'),
    ] as $key => $label)
      <li class="nav-item">
        <button type="button" class="nav-link {{ $tab === $key ? 'active' : '' }}" wire:click="setTab('{{ $key }}')">{{ $label }}</button>
      </li>
    @endforeach
  </ul>

  @if ($tab === 'overview')
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4"><div class="text-muted small">{{ __('app.case_id') }}</div><div class="fw-semibold">{{ $report->report_number }}</div></div>
          <div class="col-md-4"><div class="text-muted small">{{ __('app.file_number') }}</div><div class="fw-semibold">{{ $report->file_number ?? '—' }}</div></div>
          <div class="col-md-4"><div class="text-muted small">{{ __('app.unit') }}</div><div class="fw-semibold">{{ $report->unit->name ?? '—' }}</div></div>
          <div class="col-md-4"><div class="text-muted small">{{ __('app.category') }}</div><div class="fw-semibold">{{ $report->category->name ?? '—' }}</div></div>
          <div class="col-md-8"><div class="text-muted small">{{ __('app.title') }}</div><div class="fw-semibold">{{ $report->title }}</div></div>
          <div class="col-md-4"><div class="text-muted small">{{ __('app.date') }}</div><div>{{ $report->created_at?->format('d/m/Y H:i') }}</div></div>
          <div class="col-md-4"><div class="text-muted small">{{ __('app.created_by') }}</div><div>{{ $report->user->name ?? '—' }}</div></div>
          <div class="col-md-4"><div class="text-muted small">{{ __('app.site_visit') }}</div><div class="fw-semibold">{{ $siteStatus }}</div></div>
          <div class="col-12"><div class="text-muted small">{{ __('app.description') }}</div><div>{{ $report->description }}</div></div>
        </div>
      </div>
    </div>
  @endif

  @if ($tab === 'gis')
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="mb-3">
          <div><strong>{{ $report->location_name ?? __('app.case_location') }}</strong></div>
          <div class="small text-muted">{{ $report->address }}</div>
          <code>{{ $report->latitude }}, {{ $report->longitude }}</code>
          @if ($report->gps_accuracy)<span class="small text-muted ms-2">±{{ $report->gps_accuracy }} m</span>@endif
        </div>
        <div id="case-gis-map" style="height:420px;" class="rounded border"></div>
      </div>
    </div>
    @assets
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @endassets
    @script
    <script>
      const lat = {{ $report->latitude ?? 'null' }};
      const lng = {{ $report->longitude ?? 'null' }};
      const cat = @json($report->category->code ?? '');
      if (lat && lng && window.L) {
        const map = L.map('case-gis-map').setView([lat, lng], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
        const color = cat === 'CERUN_RUNTUH' ? '#dc3545' : '#0dcaf0';
        L.circleMarker([lat, lng], { radius: 10, color, fillColor: color, fillOpacity: 0.9 })
          .addTo(map)
          .bindPopup(`<strong>{{ $report->report_number }}</strong><br>{{ $report->category->name }}<br>{{ addslashes($report->title) }}`);
      }
    </script>
    @endscript
  @endif

  @if ($tab === 'documents')
    <div class="card border-0 shadow-sm">
      <div class="card-header border-bottom d-flex justify-content-between">
        <h6 class="mb-0">{{ __('app.technical_documents') }}</h6>
        <span class="badge bg-label-{{ $progress['complete'] ? 'success' : 'warning' }}">{{ $progress['uploaded'] }} / {{ $progress['total'] }}</span>
      </div>
      <div class="card-body">
        @foreach ($progress['items'] as $i => $item)
          <div class="border rounded p-3 mb-3">
            <div class="d-flex justify-content-between">
              <strong>{{ $i+1 }}. {{ $item['display_name'] }}</strong>
              @if ($item['uploaded']) <span class="text-success">✓ {{ __('app.uploaded') }}</span>
              @else <span class="text-danger">✕ {{ __('app.missing') }}</span> @endif
            </div>
            @if ($item['attachment'])
              <div class="small mt-2">
                {{ $item['attachment']->original_filename ?? $item['attachment']->file_name }}
                · {{ __('app.version', ['n' => $item['attachment']->version]) }} <span class="badge bg-label-success">{{ __('app.current_version') }}</span>
                · {{ $item['attachment']->file_size_formatted }}
                · {{ $item['attachment']->uploader->name ?? '-' }}
                · {{ $item['attachment']->uploaded_at?->format('d/m/Y H:i') }}
                <a href="{{ route('attachment.download', $item['attachment']) }}" class="ms-2">{{ __('app.download') }}</a>
              </div>
            @endif
          </div>
        @endforeach
      </div>
    </div>
  @endif

  @if ($tab === 'site_visit')
    <div class="card border-0 shadow-sm">
      <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0">{{ __('app.site_visit') }} — {{ $siteStatus }}</h6>
        <div class="d-flex gap-2">
          @if ($report->status !== 'draft')
            @can('startSiteVisit', $report)
              <a href="{{ route('site-visits.form', $report) }}" class="btn btn-sm btn-primary">{{ __('app.create_open_site_visit') }}</a>
            @endcan
            @if ($report->siteVisit)
              <a href="{{ route('site-visits.form', $report) }}" class="btn btn-sm {{ auth()->user()->can('manageSiteVisit', $report) ? 'btn-warning' : 'btn-outline-primary' }}">
                {{ auth()->user()->can('manageSiteVisit', $report) ? __('app.edit_site_visit') : __('app.view') }}
              </a>
            @endif
            @can('downloadPdf', $report)
              <a href="{{ route('reports.site-visit-pdf', $report) }}" class="btn btn-sm btn-outline-danger" target="_blank">{{ __('app.generate_pdf') }}</a>
            @endcan
          @else
            <span class="small text-muted">{{ __('app.submit_site_visit_first') }}</span>
          @endif
        </div>
      </div>
      <div class="card-body">
        @if ($report->siteVisit)
          <div class="row g-3 mb-3">
            <div class="col-md-3"><div class="text-muted small">{{ __('app.date') }}</div>{{ $report->siteVisit->visit_date?->format('d/m/Y') ?? '-' }}</div>
            <div class="col-md-3"><div class="text-muted small">{{ __('app.ta') }}</div>{{ $report->siteVisit->ta->name ?? '-' }}</div>
            <div class="col-md-3"><div class="text-muted small">{{ __('app.gps') }}</div><code class="small">{{ $report->siteVisit->latitude }}, {{ $report->siteVisit->longitude }}</code></div>
            <div class="col-md-3"><div class="text-muted small">{{ __('app.status') }}</div>{{ $report->siteVisit->status }}</div></div>
          </div>
          <div class="mb-3">
            <div class="text-muted small mb-1">{{ __('app.pj_pjk_report') }}</div>
            <div class="border rounded p-3" style="white-space:pre-wrap;">{{ $report->siteVisit->laporan_pj_pjk ?: '—' }}</div>
          </div>
          <h6>{{ __('app.site_visit_photos') }}</h6>
          <div class="row g-2">
            @forelse ($report->siteVisit->photos as $photo)
              <div class="col-6 col-md-3">
                <img src="{{ $photo->url }}" class="img-fluid rounded border" style="height:120px;width:100%;object-fit:cover;" alt="">
                <div class="small">{{ $photo->caption }}</div>
              </div>
            @empty
              <div class="text-muted">{{ __('app.no_photos') }}</div>
            @endforelse
          </div>
        @else
          <p class="text-muted mb-0">{{ __('app.no_site_visit') }}</p>
        @endif
      </div>
    </div>
  @endif

  @if ($tab === 'audit')
    @include('livewire.partials.workflow-audit', ['report' => $report])
  @endif
</div>
