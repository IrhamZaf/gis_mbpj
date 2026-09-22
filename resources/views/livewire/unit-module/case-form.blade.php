<div>
  @if (session()->has('message'))
    <div class="alert alert-success mb-4">{{ session('message') }}</div>
  @endif

  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
      <h4 class="mb-0">
        {{ $report ? __('app.edit_case', ['name' => $category->display_name]) : __('app.create_case', ['name' => $category->display_name]) }}
      </h4>
      <small class="text-muted">{{ __('app.unit_label', ['name' => $unit->name]) }} @if($report) · {{ $report->report_number }} @endif</small>
    </div>
    <a href="{{ $listUrl }}" class="btn btn-outline-secondary">{{ __('app.back') }}</a>
  </div>

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header border-bottom"><h6 class="mb-0">{{ __('app.basic_info') }}</h6></div>
    <div class="card-body row g-3">
      @if (! $report && $canPickUnit)
        <div class="col-md-6">
          <label class="form-label">{{ __('app.unit') }} <span class="text-danger">*</span></label>
          <select wire:model.live="unitCode" class="form-select @error('unitCode') is-invalid @enderror">
            @foreach ($selectableUnits as $u)
              <option value="{{ $u->code }}">{{ $u->name }} ({{ $u->code }})</option>
            @endforeach
          </select>
          @error('unitCode')<div class="invalid-feedback">{{ $message }}</div>@enderror
          <div class="form-text">{{ __('app.consultant_pick_unit_hint') }}</div>
        </div>
        <div class="col-md-6">
          <label class="form-label">{{ __('app.category') }}</label>
          <input type="text" class="form-control" value="{{ $category->display_name }}" readonly disabled>
        </div>
      @endif
      <div class="col-md-12">
        <label class="form-label">{{ __('app.title') }}</label>
        <input wire:model="title" type="text" class="form-control @error('title') is-invalid @enderror">
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="col-md-12">
        <label class="form-label">{{ __('app.description') }}</label>
        <textarea wire:model="description" rows="4" class="form-control @error('description') is-invalid @enderror"></textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="col-md-6">
        <label class="form-label">{{ __('app.vendor') }}</label>
        <input wire:model="vendor_name" type="text" class="form-control" placeholder="NZ Survey Consultant">
      </div>
      <div class="col-md-6">
        <label class="form-label">{{ __('app.address') }}</label>
        <input wire:model="address" type="text" class="form-control">
      </div>
    </div>
  </div>

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header border-bottom"><h6 class="mb-0">{{ __('app.gis_location') }}</h6></div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-12">
          <label class="form-label">{{ __('app.location_name') }}</label>
          <input wire:model="location_name" type="text" class="form-control">
        </div>
      </div>
      @livewire('surveyor.report-map-picker', [
        'latitude' => $latitude,
        'longitude' => $longitude,
      ], key('case-map-'.($report->id ?? 'new')))
    </div>
  </div>

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
      <h6 class="mb-0">{{ __('app.documents') }}</h6>
      @if ($report)
        <span class="badge bg-label-info">{{ $report->attachments->count() }} {{ __('app.uploaded') }}</span>
      @endif
    </div>
    <div class="card-body">
      @if ($report && $report->attachments->isNotEmpty())
        <ul class="list-group list-group-flush mb-3">
          @foreach ($report->attachments as $att)
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              <div class="small">
                <i class="ti tabler-paperclip me-1"></i>
                {{ $att->original_filename ?? $att->file_name }}
                <span class="text-muted">· {{ $att->file_size_formatted }}</span>
              </div>
              <a href="{{ route('attachment.download', $att) }}" class="btn btn-sm btn-outline-primary">{{ __('app.download') }}</a>
            </li>
          @endforeach
        </ul>
      @endif
      <label class="form-label small text-muted">{{ __('app.upload_file') }}</label>
      <input type="file" wire:model="looseFiles" class="form-control mb-2" multiple>
      <div wire:loading wire:target="looseFiles" class="small text-muted mb-2">{{ __('app.uploading') }}</div>
      @error('looseFiles') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
      <button type="button" wire:click="uploadLoose" class="btn btn-sm btn-outline-primary"
        wire:loading.attr="disabled" wire:target="looseFiles,uploadLoose">
        {{ __('app.upload_file') }}
      </button>
    </div>
  </div>

  <div class="d-flex flex-wrap gap-2 justify-content-end">
    @php
      $isDraftCase = ! $report || ($report->status === 'draft' && $report->workflow_status === null);
    @endphp
    <button type="button" wire:click="saveDraft" class="btn btn-outline-primary">
      {{ $isDraftCase ? __('app.save_draft') : __('app.save') }}
    </button>
    @if ($isDraftCase)
      <button type="button" wire:click="saveAndSubmit" class="btn btn-primary"
        wire:confirm="{{ __('app.confirm_submit_case') }}">
        {{ __('app.save_and_submit') }}
      </button>
    @endif
  </div>
</div>
