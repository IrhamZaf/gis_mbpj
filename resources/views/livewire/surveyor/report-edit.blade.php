<div>
  <div class="card mb-6">
    <div class="card-header"><h5 class="mb-0">Edit Laporan: {{ $report->report_number }}</h5></div>
    <div class="card-body">
      <form wire:submit="submit">
        <div class="row">
          <div class="col-md-6" data-survey-form-fields>
            @if ($surveyMetadataApplied)
            <div class="alert alert-info py-2 small mb-3">Kategori (CNÃ¢â€ â€™Cerun, SHÃ¢â€ â€™Sinkhole), tajuk, keterangan dan lokasi diisi automatik daripada fail survei.</div>
            @endif
            <div class="mb-4">
              <label class="form-label">Kategori Laporan <span class="text-danger">*</span></label>
              <select wire:model="category_id" class="form-select @error('category_id') is-invalid @enderror">
                <option value="0">-- Pilih Kategori --</option>
                @foreach ($categories as $cat)
                  <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
              </select>
              @error('category_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="mb-4">
              <label class="form-label">Tajuk Laporan <span class="text-danger">*</span></label>
              <input wire:model="title" type="text" class="form-control @error('title') is-invalid @enderror" />
              @error('title')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="mb-4">
              <label class="form-label">Keterangan <span class="text-danger">*</span></label>
              <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="4"></textarea>
              @error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="mb-4">
              <label class="form-label">Nama Lokasi</label>
              <input wire:model="location_name" type="text" class="form-control" />
            </div>
          </div>

          <div class="col-md-6" data-survey-map-card>
            <label class="form-label">Lokasi GIS</label>
            <div class="gis-location-search-wrap mb-2 position-relative">
              <div class="input-group">
                <span class="input-group-text"><i class="ti tabler-search"></i></span>
                <input type="text" id="gis-location-search" class="form-control" placeholder="Cari alamat di Petaling Jaya..." autocomplete="off" />
                <button type="button" id="gis-location-search-btn" class="btn btn-outline-primary">Cari</button>
              </div>
              <ul id="gis-location-results" class="list-group position-absolute w-100 shadow-sm d-none gis-location-results"></ul>
            </div>
            <small class="text-muted d-block mb-2">Cari lokasi di atas, atau klik peta untuk tandakan tapak.</small>
            <div id="gis-map-edit" style="height:400px;border-radius:8px;border:1px solid var(--bs-border-color);" wire:ignore></div>
            <div class="row g-2 mt-2 mb-2">
              <div class="col-6">
                <label class="form-label small mb-1">Latitud</label>
                <input id="report-anchor-lat" wire:model="latitude" type="number" step="0.0000001" class="form-control form-control-sm" readonly placeholder="—" />
              </div>
              <div class="col-6">
                <label class="form-label small mb-1">Longitud</label>
                <input id="report-anchor-lng" wire:model="longitude" type="number" step="0.0000001" class="form-control form-control-sm" readonly placeholder="—" />
              </div>
            </div>
            <div id="survey-anchor-warning" class="alert alert-warning py-2 small mt-2 mb-0 d-none">
              Sila cari lokasi atau klik peta untuk tetapkan tapak sebelum muat naik fail survei CSV/TXT.
            </div>
          </div>
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
                @if ($att->parse_status === 'failed')
                  <small class="text-danger d-block">{{ $att->parse_message }}</small>
                @endif
              </span>
              <button type="button" wire:click="deleteAttachment({{ $att->id }})" wire:confirm="Padam fail ini?" class="btn btn-sm btn-icon btn-text-danger"><i class="ti tabler-trash"></i></button>
            </li>
            @endforeach
          </ul>
        </div>
        @endif

        <div class="mb-4">
          <label class="form-label">Tambah Dokumen Survei <small class="text-muted">(Maks 20MB)</small></label>
          <input wire:model="newFiles" data-survey-files type="file" class="form-control" multiple accept=".csv,.txt,.pdf,.jpg,.jpeg,.png" />
          <div wire:loading wire:target="newFiles" class="text-muted small mt-1">Memuat naik...</div>
        </div>

        <div class="d-flex gap-2">
          <button type="button" wire:click="saveDraft" class="btn btn-outline-warning"><i class="ti tabler-device-floppy me-1"></i>Simpan Draf</button>
          <button type="submit" class="btn btn-primary"><i class="ti tabler-send me-1"></i>Hantar Laporan</button>
          <a href="{{ route('surveyor.reports') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
      </form>
    </div>
  </div>

  @push('scripts')
  @vite(['resources/js/gis-map-picker.js', 'resources/js/gis-survey-preview.js'])
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const livewire = @this;
      window.surveyReportLivewire = livewire;
      window.gisMapPicker.initMapPicker({
        mapElementId: 'gis-map-edit',
        initialLat: {{ $latitude ?? 3.1073 }},
        initialLng: {{ $longitude ?? 101.6067 }},
        initialZoom: 16,
        initialGisData: @json($gis_data),
        initialLocationLabel: @json($location_name ?: ''),
        onCoordinatesChange(lat, lng, label) { livewire.call('setCoordinates', lat, lng, label); },
        onGisDataChange(data) { livewire.call('setGisData', data); },
      });
      window.gisMapPicker.loadStoredSurveys(@json($surveyLayers));
      @foreach ($attachments->where('document_type', 'survey_1d') as $pdf)
      (function() {
        const panel = document.createElement('div');
        panel.id = 'survey-pdf-panel';
        panel.className = 'mt-3';
        panel.innerHTML = '<label class="form-label small text-muted">Pratonton PDF (1D)</label><div id="survey-pdf-list"></div>';
        document.querySelector('[data-survey-map-card]')?.appendChild(panel);
        const list = document.getElementById('survey-pdf-list');
        const item = document.createElement('div');
        item.className = 'mb-2';
        item.innerHTML = '<div class="small mb-1">{{ $pdf->file_name }}</div><iframe src="{{ asset('storage/' . $pdf->file_path) }}" style="width:100%;height:320px;border:1px solid var(--bs-border-color);border-radius:8px;"></iframe>';
        list.appendChild(item);
      })();
      @endforeach
    });
  </script>
  @endpush
</div>
