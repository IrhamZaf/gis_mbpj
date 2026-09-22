<div>
  @if (session()->has('message'))
    <div class="alert alert-success mb-4">{{ session('message') }}</div>
  @endif

  <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
    <div>
      <h4 class="mb-1">{{ __('app.case_label', ['number' => $report->report_number]) }}</h4>
      <div class="text-muted small">
        {{ $report->unit->name ?? '-' }} · {{ $report->category?->display_name ?? '-' }} · {!! $report->status_badge !!}
      </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
      @can('update', $report)
        <a href="{{ $editUrl }}" class="btn btn-outline-primary">{{ __('app.edit') }}</a>
        <button type="button" wire:click="submitCase" class="btn btn-primary"
          wire:confirm="{{ __('app.confirm_submit_short') }}">{{ __('app.submit') }}</button>
      @endcan
      @can('downloadPdf', $report)
        <a href="{{ route('reports.site-visit-pdf', $report) }}" class="btn btn-outline-danger" target="_blank">
          <i class="ti tabler-printer me-1"></i>{{ __('app.generate_pdf') }}
        </a>
      @endcan
    </div>
  </div>

  @if ($isReadOnly)
    <div class="alert alert-secondary border d-flex align-items-start gap-2 mb-4" role="status">
      <i class="ti tabler-lock mt-1"></i>
      <div>
        <div class="fw-semibold">{{ __('app.read_only_title') }}</div>
        <div class="small mb-0">{{ __('app.read_only_body', ['unit' => $report->unit->name ?? '-']) }}</div>
      </div>
    </div>
  @endif

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
          <div class="col-md-4"><div class="text-muted small">{{ __('app.category') }}</div><div class="fw-semibold">{{ $report->category?->display_name ?? '—' }}</div></div>
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
        @if ($report->latitude && $report->longitude)
          <div id="case-gis-map" wire:ignore style="height:420px;width:100%;" class="rounded border bg-light"></div>
        @else
          <div class="alert alert-warning mb-0">{{ __('app.no_geo_reports') }}</div>
        @endif
      </div>
    </div>
  @endif

  @assets
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  @endassets

  @script
  <script>
    let caseGisMap = null;

    const caseGis = {
      lat: {{ $report->latitude !== null ? (float) $report->latitude : 'null' }},
      lng: {{ $report->longitude !== null ? (float) $report->longitude : 'null' }},
      cat: @json($report->category?->code ?? ''),
      popup: @json('<strong>'.e($report->report_number).'</strong><br>'.e($report->category?->display_name ?? $report->category?->name ?? '').'<br>'.e($report->title)),
    };

    function categoryColor(cat) {
      if (cat === 'CERUN' || cat === 'CERUN_RUNTUH') return '#dc3545';
      if (cat === 'BOREHOLE') return '#6c757d';
      return '#0dcaf0';
    }

    function destroyCaseGisMap() {
      if (caseGisMap) {
        try { caseGisMap.remove(); } catch (e) {}
        caseGisMap = null;
      }
    }

    function renderCaseGisMap() {
      const el = document.getElementById('case-gis-map');
      if (!el || caseGis.lat == null || caseGis.lng == null || !window.L) {
        return false;
      }

      // Fresh DOM node after Livewire tab switch
      if (caseGisMap && !el._leaflet_id) {
        caseGisMap = null;
      }

      if (!caseGisMap) {
        // Clear leftover leaflet id if Livewire reused markup oddly
        if (el._leaflet_id) {
          try { el._leaflet_id = null; el.innerHTML = ''; } catch (e) {}
        }

        caseGisMap = L.map(el, {
          scrollWheelZoom: true,
          maxBounds: [[2.97, 101.51], [3.165, 101.69]],
          maxBoundsViscosity: 1.0,
          minZoom: 12,
        }).setView([caseGis.lat, caseGis.lng], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 19,
          attribution: '&copy; OpenStreetMap',
        }).addTo(caseGisMap);
        L.rectangle([[2.97, 101.51], [3.165, 101.69]], {
          color: '#0d6efd', weight: 2, dashArray: '6 4', fill: false, interactive: false
        }).addTo(caseGisMap);

        const color = categoryColor(caseGis.cat);
        L.circleMarker([caseGis.lat, caseGis.lng], {
          radius: 10,
          color,
          fillColor: color,
          fillOpacity: 0.9,
          weight: 2,
        }).addTo(caseGisMap).bindPopup(caseGis.popup);
      }

      setTimeout(() => caseGisMap && caseGisMap.invalidateSize(), 80);
      setTimeout(() => caseGisMap && caseGisMap.invalidateSize(), 300);
      return true;
    }

    function waitAndRenderCaseGis(attempts = 0) {
      if (renderCaseGisMap()) return;
      if (attempts >= 50) return;
      setTimeout(() => waitAndRenderCaseGis(attempts + 1), 100);
    }

    // Initial (if GIS tab already active)
    waitAndRenderCaseGis();

    Livewire.on('case-gis-tab-shown', () => {
      destroyCaseGisMap();
      setTimeout(() => waitAndRenderCaseGis(), 60);
    });

    $wire.$watch('tab', (value) => {
      if (value === 'gis') {
        destroyCaseGisMap();
        setTimeout(() => waitAndRenderCaseGis(), 60);
      } else {
        destroyCaseGisMap();
      }
    });
  </script>
  @endscript

  @if ($tab === 'documents')
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header border-bottom d-flex justify-content-between">
        <h6 class="mb-0">{{ __('app.documents') }}</h6>
        <span class="badge bg-label-info">{{ $report->attachments->where('is_current', true)->count() }} {{ __('app.uploaded') }}</span>
      </div>
      <div class="card-body">
        @forelse ($report->attachments->where('is_current', true) as $att)
          <div class="border rounded p-3 mb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="small">
              <i class="ti tabler-paperclip me-1"></i>
              <strong>{{ $att->original_filename ?? $att->file_name }}</strong>
              <span class="text-muted">
                · {{ $att->file_size_formatted }}
                · {{ $att->uploader->name ?? '-' }}
                · {{ $att->uploaded_at?->format('d/m/Y H:i') }}
              </span>
            </div>
            <a href="{{ route('attachment.download', $att) }}" class="btn btn-sm btn-outline-primary">{{ __('app.download') }}</a>
          </div>
        @empty
          <p class="text-muted mb-0">{{ __('app.no_attachments') }}</p>
        @endforelse
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0">{{ __('app.reports_by_unit') }}</h6>
        <span class="badge bg-label-secondary">{{ collect($reportsByUnit)->sum('total') }} {{ __('app.total_reports') }}</span>
      </div>
      <div class="card-body">
        <div class="row g-3">
          @foreach ($reportsByUnit as $block)
            @php
              $u = $block['unit'];
              $theme = $block['theme'];
            @endphp
            <div class="col-md-6 col-xl-3">
              <a href="{{ $block['dashboardUrl'] }}" class="text-decoration-none">
                <div class="border rounded p-3 h-100" style="border-top:3px solid {{ $theme['color'] }} !important;">
                  <div class="fw-semibold text-body">{{ $u->name }}</div>
                  <div class="small text-muted mb-2">{{ $u->code }}</div>
                  <div class="fs-4 fw-bold" style="color:{{ $theme['color'] }};">{{ number_format($block['total']) }}</div>
                  <div class="small text-muted">{{ __('app.total_reports') }}</div>
                </div>
              </a>
            </div>
          @endforeach
        </div>
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
