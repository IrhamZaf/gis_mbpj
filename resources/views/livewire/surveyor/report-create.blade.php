<div>
  <div class="card mb-6">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h5 class="mb-0"><i class="ti tabler-file-plus me-2"></i>Cipta Laporan Baru</h5>
      <a href="{{ route('surveyor.reports') }}" class="btn btn-sm btn-label-secondary">
        <i class="ti tabler-arrow-left me-1"></i>Kembali
      </a>
    </div>

    <div class="card-body">
      {{-- Error summary --}}
      @if ($errors->any())
        <div class="alert alert-danger alert-dismissible mb-4" role="alert">
          <strong>Sila betulkan ralat berikut:</strong>
          <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
      @endif

      <form wire:submit="submit">
        <div class="row g-5">
          {{-- ═══ LEFT: Form fields ═══ --}}
          <div class="col-lg-7">

            {{-- Kategori --}}
            <div class="mb-4">
              <label for="rpt-category" class="form-label">
                Kategori Laporan <span class="text-danger">*</span>
              </label>
              <select wire:model.change="category_id" id="rpt-category"
                      class="form-select @error('category_id') is-invalid @enderror">
                <option value="">-- Pilih Kategori --</option>
                @foreach ($categories as $cat)
                  <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
              </select>
              @error('category_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Tajuk --}}
            <div class="mb-4">
              <label for="rpt-title" class="form-label">
                Tajuk Laporan <span class="text-danger">*</span>
              </label>
              <input wire:model="title" type="text" id="rpt-title"
                     class="form-control @error('title') is-invalid @enderror"
                     placeholder="cth: Sinkhole di Jalan SS2/24">
              @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Keterangan --}}
            <div class="mb-4">
              <label for="rpt-desc" class="form-label">
                Keterangan <span class="text-danger">*</span>
              </label>
              <textarea wire:model="description" id="rpt-desc" rows="4"
                        class="form-control @error('description') is-invalid @enderror"
                        placeholder="Keterangan ringkas tentang isu yang dilaporkan..."></textarea>
              @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Nama Lokasi --}}
            <div class="mb-4">
              <label for="rpt-location" class="form-label">Nama Lokasi</label>
              <input wire:model="location_name" type="text" id="rpt-location"
                     class="form-control"
                     placeholder="cth: ATC5A, Persimpangan Jalan SS2/24">
              <small class="form-text text-muted">
                Pilihan — boleh juga klik peta di sebelah kanan untuk tetapkan lokasi.
              </small>
            </div>

            <hr class="my-4">

            {{-- ── File Attachments ── --}}
            <div class="mb-4">
              <label for="rpt-files" class="form-label">
                <i class="ti tabler-paperclip me-1"></i>Lampiran Fail
                <small class="text-muted fw-normal">(Maks 20 MB setiap fail)</small>
              </label>

              <div class="mb-2">
                <small class="text-muted">
                  Format disokong: CSV, TXT, PDF, JPG, PNG
                </small>
              </div>

              <input wire:model="attachments" type="file" id="rpt-files"
                     class="form-control @error('attachments.*') is-invalid @enderror"
                     multiple
                     accept=".csv,.txt,.pdf,.jpg,.jpeg,.png">

              {{-- Upload spinner --}}
              <div wire:loading wire:target="attachments" class="mt-2">
                <div class="d-flex align-items-center text-primary small">
                  <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                  Memuat naik fail...
                </div>
              </div>

              @error('attachments.*')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror

              {{-- Uploaded file list --}}
              @if (!empty($attachments))
                <ul class="list-group mt-3">
                  @foreach ($attachments as $i => $file)
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                      <span class="small">
                        <i class="ti tabler-file me-1 text-primary"></i>
                        {{ $file->getClientOriginalName() }}
                        <span class="text-muted ms-1">({{ number_format($file->getSize() / 1024, 1) }} KB)</span>
                      </span>
                      <button type="button" wire:click="removeAttachment({{ $i }})"
                              class="btn btn-sm btn-icon btn-text-danger" title="Buang fail">
                        <i class="ti tabler-x"></i>
                      </button>
                    </li>
                  @endforeach
                </ul>
              @endif
            </div>

            {{-- Submit-level error --}}
            @error('submit')
              <div class="alert alert-danger small mb-4">
                <i class="ti tabler-alert-circle me-1"></i>{{ $message }}
              </div>
            @enderror

            {{-- ── Action buttons ── --}}
            <div class="d-flex gap-2 pt-2">
              <button type="button" wire:click="saveDraft"
                      class="btn btn-outline-warning"
                      wire:loading.attr="disabled" wire:target="saveDraft,submit,attachments">
                <span wire:loading.remove wire:target="saveDraft">
                  <i class="ti tabler-device-floppy me-1"></i>Simpan Draf
                </span>
                <span wire:loading wire:target="saveDraft">
                  <span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...
                </span>
              </button>

              <button type="submit"
                      class="btn btn-primary"
                      wire:loading.attr="disabled" wire:target="saveDraft,submit,attachments">
                <span wire:loading.remove wire:target="submit">
                  <i class="ti tabler-send me-1"></i>Hantar Laporan
                </span>
                <span wire:loading wire:target="submit">
                  <span class="spinner-border spinner-border-sm me-1"></span>Menghantar...
                </span>
              </button>
            </div>
          </div>

          {{-- ═══ RIGHT: Map picker (optional visual aid) ═══ --}}
          <div class="col-lg-5">
            <livewire:surveyor.report-map-picker wire:key="report-map-picker-create" />
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
