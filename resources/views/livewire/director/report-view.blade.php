<div>
  @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible mb-4"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('message') }}</div>
  @endif
  @error('workflow')
    <div class="alert alert-danger mb-4">{{ $message }}</div>
  @enderror

  @include('livewire.partials.workflow-progress', ['report' => $report])

  <div class="row g-4 mb-4">
    <div class="col-lg-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header border-bottom"><h5 class="mb-0">{{ __('app.surveyor_report') }}</h5></div>
        <div class="card-body">
          <table class="table table-borderless">
            <tr><td class="fw-semibold">No. Fail</td><td><code>{{ $report->file_number }}</code></td></tr>
            <tr><td class="fw-semibold">Tajuk</td><td>{{ $report->title }}</td></tr>
            <tr><td class="fw-semibold">Unit</td><td>{{ $report->unit->name ?? '-' }}</td></tr>
            <tr><td class="fw-semibold">Surveyor</td><td>{{ $report->user->name ?? '-' }}</td></tr>
            <tr><td class="fw-semibold">Lokasi</td><td>{{ $report->location_name }}</td></tr>
          </table>
          <p>{{ $report->description }}</p>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header border-bottom"><h5 class="mb-0">{{ __('app.ta_visit_engineer_review') }}</h5></div>
        <div class="card-body">
          @if ($report->siteVisit)
            <p><strong>{{ __('app.ta') }}:</strong> {{ $report->siteVisit->ta->name ?? '-' }} · {{ $report->siteVisit->visit_date?->format('d/m/Y') }}</p>
            <h6>{{ __('app.pj_pjk_report') }}</h6>
            <p style="white-space:pre-wrap;">{{ $report->siteVisit->laporan_pj_pjk }}</p>
          @endif
          @if ($report->latestEngineerVerification)
            <hr>
            <h6>ULASAN PP/PPK/TPKJ</h6>
            <p>{{ $report->latestEngineerVerification->remarks ?: '—' }}</p>
            <div class="small text-muted">{{ $report->latestEngineerVerification->engineer->name ?? '' }} · {{ $report->latestEngineerVerification->verified_at?->format('d/m/Y H:i') }}</div>
          @endif
        </div>
      </div>
    </div>
  </div>

  @if ($canApprove)
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header border-bottom"><h5 class="mb-0">{{ __('app.director_decision') }}</h5></div>
      <div class="card-body">
        <textarea wire:model="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="3" placeholder="{{ __('app.remarks_placeholder') }}"></textarea>
        @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="d-flex gap-2 mt-3">
          <button type="button" wire:click="approve" class="btn btn-success" wire:confirm="{{ __('app.confirm_approve') }}">
            <i class="ti tabler-stamp me-1"></i>{{ __('app.approve') }}
          </button>
          <button type="button" wire:click="reject" class="btn btn-outline-danger" wire:confirm="{{ __('app.confirm_reject') }}">
            {{ __('app.reject_return') }}
          </button>
        </div>
      </div>
    </div>
  @endif

  @include('livewire.partials.workflow-audit', ['report' => $report])

  <div class="d-flex gap-2 flex-wrap">
    @php
      $backUrl = match (true) {
        auth()->user()?->isSuperadmin() => route('superadmin.reports'),
        auth()->user()?->isDirector() => route('director.reports'),
        default => url()->previous(),
      };
    @endphp
    <a href="{{ $backUrl }}" class="btn btn-outline-secondary">{{ __('app.back') }}</a>
    @if ($report->siteVisit)
      <a href="{{ route('site-visits.form', $report) }}" class="btn btn-outline-primary">
        <i class="ti tabler-clipboard-list me-1"></i>{{ __('app.site_visit') }}
      </a>
    @endif
    @can('downloadPdf', $report)
      <a href="{{ route('reports.site-visit-pdf', $report) }}" class="btn btn-outline-primary" target="_blank">{{ __('app.official_pdf') }}</a>
    @endcan
  </div>
</div>
