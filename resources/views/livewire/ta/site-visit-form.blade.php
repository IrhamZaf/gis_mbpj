<div>
  @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible mb-4"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('message') }}</div>
  @endif
  @error('workflow')
    <div class="alert alert-danger mb-4">{{ $message }}</div>
  @enderror

  @include('livewire.partials.workflow-progress', ['report' => $report])

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-start gap-2">
      <div>
        <h5 class="mb-0">{{ __('app.site_visit_report_title') }}</h5>
        <small class="text-muted">{{ __('app.site_visit_ref_note') }}</small>
      </div>
      @can('downloadPdf', $report)
        <a href="{{ route('reports.site-visit-pdf', $report) }}" class="btn btn-outline-danger" target="_blank">
          <i class="ti tabler-printer me-1"></i>{{ __('app.print_pdf') }}
        </a>
      @endcan
    </div>
    <div class="card-body">
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <label class="form-label">{{ __('app.file_number') }}</label>
          <input type="text" class="form-control" value="{{ $report->file_number }}" readonly>
        </div>
        <div class="col-md-4">
          <label class="form-label">{{ __('app.reference') }}</label>
          <input wire:model="reference" type="text" class="form-control" @disabled(!$canEdit)>
        </div>
        <div class="col-md-4">
          <label class="form-label">{{ __('app.unit') }}</label>
          <input type="text" class="form-control" value="{{ $report->unit->name ?? '-' }}" readonly>
        </div>
        <div class="col-md-8">
          <label class="form-label">{{ __('app.work_title') }}</label>
          <input type="text" class="form-control" value="{{ $report->title }}" readonly>
        </div>
        <div class="col-md-4">
          <label class="form-label">{{ __('app.vendor') }}</label>
          <input type="text" class="form-control" value="{{ $report->vendor_name ?: ($report->user->name ?? '-') }}" readonly>
        </div>
        <div class="col-md-4">
          <label class="form-label">{{ __('app.visit_date') }}</label>
          <input wire:model="visit_date" type="date" class="form-control" @disabled(!$canEdit)>
        </div>
        <div class="col-md-4">
          <label class="form-label">{{ __('app.visit_time') }}</label>
          <input wire:model="visit_time" type="time" class="form-control" @disabled(!$canEdit)>
        </div>
        <div class="col-md-4">
          <label class="form-label">{{ __('app.ta_designation') }}</label>
          <input wire:model="ta_designation" type="text" class="form-control" @disabled(!$canEdit)>
        </div>
      </div>

      <hr>
      <h6 class="mb-3">{{ __('app.gis_location') }}</h6>
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <div class="small text-muted mb-1">{{ __('app.surveyor_location') }}</div>
          <div>{{ $report->location_name ?? '-' }}</div>
          <code class="small">{{ $report->latitude }}, {{ $report->longitude }}</code>
        </div>
        <div class="col-md-6">
          <div class="small text-muted mb-1">{{ __('app.ta_visit_location') }}</div>
          <code class="small">{{ $latitude ?? '—' }}, {{ $longitude ?? '—' }}</code>
          @if ($distanceKm !== null)
            <div class="small text-primary mt-1">{{ __('app.distance_from_surveyor', ['km' => $distanceKm]) }}</div>
          @endif
          @if ($canEdit)
            <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="captureTaGps()">
              <i class="ti tabler-current-location me-1"></i>{{ __('app.use_current_location') }}
            </button>
          @endif
        </div>
      </div>

      <hr>
      <h6 class="mb-3">{{ __('app.pj_pjk_report') }}</h6>
      <textarea wire:model="laporan_pj_pjk" class="form-control @error('laporan_pj_pjk') is-invalid @enderror" rows="8"
        placeholder="{{ __('app.pj_pjk_placeholder') }}" @disabled(!$canEdit)></textarea>
      @error('laporan_pj_pjk')<div class="invalid-feedback">{{ $message }}</div>@enderror

      <div class="mt-3">
        <label class="form-label">{{ __('app.additional_notes') }}</label>
        <textarea wire:model="visit_notes" class="form-control" rows="2" @disabled(!$canEdit)></textarea>
      </div>

      <hr>
      <h6 class="mb-3">{{ __('app.site_visit_photos') }}</h6>
      <div class="row g-3 mb-3">
        @foreach ($savedPhotos as $photo)
          <div class="col-md-3">
            <div class="border rounded p-2">
              <img src="{{ $photo->url }}" alt="" class="img-fluid rounded mb-2" style="height:120px;width:100%;object-fit:cover;">
              <div class="small">{{ $photo->caption ?: $photo->file_name }}</div>
              <div class="small text-muted">{{ $photo->taken_at?->format('d/m/Y H:i') }}</div>
              @if ($canEdit)
                <button type="button" wire:click="removePhoto({{ $photo->id }})" class="btn btn-xs btn-link text-danger p-0">{{ __('app.delete') }}</button>
              @endif
            </div>
          </div>
        @endforeach
      </div>
      @if ($canEdit)
        <input type="file" wire:model="photos" class="form-control" multiple accept="image/*">
        <div wire:loading wire:target="photos" class="small text-muted mt-1">{{ __('app.uploading') }}</div>
      @endif

      <hr>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">{{ __('app.signature_name') }}</label>
          <input wire:model="ta_signature" type="text" class="form-control" @disabled(!$canEdit)>
        </div>
        <div class="col-md-6 d-flex align-items-end justify-content-end gap-2">
          <a href="{{ $backUrl }}" class="btn btn-outline-secondary">{{ __('app.back') }}</a>
          @if ($canEdit)
            <button type="button" wire:click="saveDraft" class="btn btn-outline-primary">{{ __('app.save_draft') }}</button>
            <button type="button" wire:click="submit" class="btn btn-primary" wire:confirm="{{ __('app.confirm_submit_site_visit') }}">
              {{ __('app.submit_for_engineer') }}
            </button>
          @endif
        </div>
      </div>
    </div>
  </div>

  @include('livewire.partials.workflow-audit', ['report' => $report])
</div>

@script
<script>
  window.captureTaGps = function () {
    if (!navigator.geolocation) {
      alert(@json(__('app.gps_unsupported')));
      return;
    }
    navigator.geolocation.getCurrentPosition(function (pos) {
      $wire.setGps(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy ?? null);
    }, function () {
      alert(@json(__('app.gps_failed')));
    }, { enableHighAccuracy: true, timeout: 15000 });
  };
</script>
@endscript
