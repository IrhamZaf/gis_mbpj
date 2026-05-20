<div>
  <div class="card mb-6">
    <div class="card-header"><h5 class="mb-0">Edit Laporan: {{ $report->report_number }}</h5></div>
    <div class="card-body">
      @if ($errors->any())
        <div class="alert alert-danger mb-4" role="alert">
          <strong>Sila betulkan ralat berikut:</strong>
          <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="row">
        <div class="col-md-6">
          <div class="mb-4">
            <label class="form-label">Kategori Laporan <span class="text-danger">*</span></label>
            <select wire:model.blur="category_id" class="form-select @error('category_id') is-invalid @enderror">
              <option value="">-- Pilih Kategori --</option>
              @foreach ($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
              @endforeach
            </select>
            @error('category_id')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
          </div>
          <div class="mb-4">
            <label class="form-label">Tajuk Laporan <span class="text-danger">*</span></label>
            <input wire:model.blur="title" type="text" class="form-control @error('title') is-invalid @enderror" />
            @error('title')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
          </div>
          <div class="mb-4">
            <label class="form-label">Keterangan <span class="text-danger">*</span></label>
            <textarea wire:model.blur="description" class="form-control @error('description') is-invalid @enderror" rows="4"></textarea>
            @error('description')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
          </div>
          <div class="mb-4">
            <label class="form-label">Nama Lokasi</label>
            <input wire:model.blur="location_name" type="text" class="form-control" />
          </div>

          @if ($attachments->count())
          <div class="mb-4">
            <label class="form-label">Lampiran Sedia Ada</label>
            <ul class="list-group">
              @foreach ($attachments as $att)
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>
                  {!! $att->document_type_badge !!}
                  <i class="icon-base {{ $att->file_icon }} me-2"></i>{{ $att->file_name }}
                  <small class="text-muted">({{ $att->file_size_formatted }})</small>
                </span>
                <button type="button" wire:click="deleteAttachment({{ $att->id }})" wire:confirm="Padam fail ini?" class="btn btn-sm btn-icon btn-text-danger"><i class="ti tabler-trash"></i></button>
              </li>
              @endforeach
            </ul>
          </div>
          @endif

          <div class="mb-4">
            <label class="form-label">Tambah Dokumen Survei <small class="text-muted">(Maks 20MB)</small></label>
            <input wire:model="newFiles" type="file" class="form-control" multiple accept=".csv,.txt,.pdf,.jpg,.jpeg,.png" />
            <div wire:loading wire:target="newFiles" class="text-muted small mt-1">Memuat naik...</div>
            @if (count($newFiles))
              <ul class="list-group mt-2">
                @foreach ($newFiles as $i => $f)
                  @php $preview = collect($mapPreviewLayers)->firstWhere('file_name', $f->getClientOriginalName()); @endphp
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>{{ $f->getClientOriginalName() }}</span>
                    <button type="button" wire:click="removeNewFile({{ $i }})" class="btn btn-sm btn-icon btn-text-danger"><i class="ti tabler-x"></i></button>
                  </li>
                @endforeach
              </ul>
            @endif
          </div>

          <div class="d-flex gap-2">
            <button type="button" wire:click="saveDraft" class="btn btn-outline-warning" wire:loading.attr="disabled" wire:target="saveDraft,submit">Simpan Draf</button>
            <button type="button" wire:click="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="saveDraft,submit">Hantar Laporan</button>
            <a href="{{ route('surveyor.reports') }}" class="btn btn-outline-secondary">Batal</a>
          </div>
        </div>

        <div class="col-md-6">
          <livewire:surveyor.report-map-picker wire:key="report-map-picker-edit-{{ $report->id }}" />
        </div>
      </div>
    </div>
  </div>
</div>
