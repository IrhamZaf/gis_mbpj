<div>
  @if (session()->has('message'))
    <div class="alert alert-success mb-4">{{ session('message') }}</div>
  @endif

  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
      <h4 class="mb-0">
        {{ $report ? __('app.edit_case', ['name' => $category->name]) : __('app.create_case', ['name' => $category->name]) }}
      </h4>
      <small class="text-muted">{{ __('app.unit_label', ['name' => $unit->name]) }} @if($report) · {{ $report->report_number }} @endif</small>
    </div>
    <a href="{{ route('saliran-cerun.'.($category->code === 'CERUN_RUNTUH' ? 'cerun' : 'sinkhole')) }}" class="btn btn-outline-secondary">{{ __('app.back') }}</a>
  </div>

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header border-bottom"><h6 class="mb-0">{{ __('app.basic_info') }}</h6></div>
    <div class="card-body row g-3">
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
        <input wire:model="vendor_name" type="text" class="form-control">
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
        <div class="col-md-4">
          <label class="form-label">{{ __('app.location_name') }}</label>
          <input wire:model="location_name" type="text" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">{{ __('app.latitude') }}</label>
          <input wire:model="latitude" type="text" class="form-control @error('latitude') is-invalid @enderror" readonly>
          @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
          <label class="form-label">{{ __('app.longitude') }}</label>
          <input wire:model="longitude" type="text" class="form-control @error('longitude') is-invalid @enderror" readonly>
          @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>
      @livewire('surveyor.report-map-picker', [
        'latitude' => $latitude,
        'longitude' => $longitude,
      ], key('case-map-'.($report->id ?? 'new')))
    </div>
  </div>

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header border-bottom d-flex justify-content-between">
      <h6 class="mb-0">{{ __('app.technical_docs') }}</h6>
      <span class="badge bg-label-{{ $progress['complete'] ? 'success' : 'warning' }}">
        {{ __('app.documents_uploaded', ['uploaded' => $progress['uploaded'], 'total' => $progress['total']]) }}
      </span>
    </div>
    <div class="card-body">
      <div class="row g-3">
        @foreach ($progress['items'] as $i => $item)
          @php $type = $item['type']; @endphp
          <div class="col-md-6">
            <div class="border rounded p-3 h-100">
              <div class="d-flex justify-content-between mb-2">
                <strong>{{ $i+1 }}. {{ strtoupper($item['display_name']) }}</strong>
                @if ($item['uploaded'])
                  <span class="text-success">✓ {{ __('app.uploaded') }}</span>
                @else
                  <span class="text-danger">✕ {{ __('app.missing') }}</span>
                @endif
              </div>
              @if ($item['attachment'])
                <div class="small mb-2">
                  {{ $item['attachment']->original_filename ?? $item['attachment']->file_name }}
                  · v{{ $item['attachment']->version }} {{ __('app.current_version') }}
                  · {{ $item['attachment']->file_size_formatted }}
                </div>
              @endif
              <input type="file" wire:model="uploads.{{ $type->id }}" class="form-control form-control-sm mb-2">
              <div wire:loading wire:target="uploads.{{ $type->id }}" class="small text-muted">{{ __('app.uploading') }}</div>
              <button type="button" wire:click="uploadType({{ $type->id }})" class="btn btn-sm btn-outline-primary"
                wire:loading.attr="disabled" wire:target="uploads.{{ $type->id }}">
                {{ __('app.upload_file') }}
              </button>
              @error('uploads.'.$type->id)<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  <div class="d-flex flex-wrap gap-2 justify-content-end">
    <button type="button" wire:click="saveDraft" class="btn btn-outline-primary">{{ __('app.save_draft') }}</button>
    <button type="button" wire:click="saveAndSubmit" class="btn btn-primary"
      wire:confirm="{{ __('app.confirm_submit_case') }}">
      {{ __('app.save_and_submit') }}
    </button>
  </div>
</div>
