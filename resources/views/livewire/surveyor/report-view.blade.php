<div>
  <div class="row">
    <div class="col-md-6 mb-6">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between">
          <h5 class="mb-0">{{ $report->title }}</h5>
          {!! $report->status_badge !!}
        </div>
        <div class="card-body">
          <table class="table table-borderless">
            <tr><td class="fw-semibold" width="40%">No. Laporan</td><td><code>{{ $report->report_number }}</code></td></tr>
            <tr><td class="fw-semibold">Kategori</td><td>{{ $report->category->name ?? '-' }}</td></tr>
            <tr><td class="fw-semibold">Surveyor</td><td>{{ $report->user->name ?? '-' }}</td></tr>
            <tr><td class="fw-semibold">Lokasi</td><td>{{ $report->location_name ?? '-' }}</td></tr>
            <tr><td class="fw-semibold">Koordinat</td><td>{{ $report->latitude ?? '-' }}, {{ $report->longitude ?? '-' }}</td></tr>
            <tr><td class="fw-semibold">Tarikh Hantar</td><td>{{ $report->submitted_at?->format('d/m/Y H:i') ?? '-' }}</td></tr>
          </table>
          <hr>
          <h6 class="fw-semibold">Keterangan</h6>
          <p class="text-body">{{ $report->description ?? 'Tiada keterangan.' }}</p>
        </div>
      </div>
    </div>

    <div class="col-md-6 mb-6">
      <div class="card h-100">
        <div class="card-header"><h5 class="mb-0"><i class="ti tabler-map me-2"></i>Peta Survei</h5></div>
        <div class="card-body p-0" data-survey-map-card>
          <input type="hidden" id="report-anchor-lat" value="{{ $report->latitude }}" />
          <input type="hidden" id="report-anchor-lng" value="{{ $report->longitude }}" />
          <div id="report-map" style="height:450px;border-radius:0 0 8px 8px;" wire:ignore></div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="ti tabler-paperclip me-2"></i>Lampiran ({{ $report->attachments->count() }})</h5></div>
    <div class="card-body">
      @if ($report->attachments->count())
      <div class="table-responsive">
        <table class="table">
          <thead><tr><th>Fail</th><th>Jenis Dokumen</th><th>Status</th><th>Saiz</th><th></th></tr></thead>
          <tbody>
            @foreach ($report->attachments as $att)
            <tr>
              <td><i class="icon-base {{ $att->file_icon }} me-2"></i>{{ $att->file_name }}</td>
              <td>{!! $att->document_type_badge !!} {{ $att->document_type_label }}</td>
              <td>
                @if ($att->parse_status === 'ok')<span class="badge bg-label-success">OK</span>
                @elseif ($att->parse_status === 'failed')<span class="badge bg-label-danger" title="{{ $att->parse_message }}">Ralat</span>
                @else<span class="badge bg-label-secondary">-</span>@endif
              </td>
              <td>{{ $att->file_size_formatted }}</td>
              <td class="text-nowrap">
                @if (preg_match('/\.(pdf|jpg|jpeg|png)$/i', $att->file_name))
                  <button type="button" class="btn btn-sm btn-outline-info me-1" onclick="viewAttachment('{{ route('attachment.view', $att->id) }}', '{{ $att->file_name }}')" title="Lihat Lampiran">
                    <i class="ti tabler-eye"></i>
                  </button>
                @endif
                <a href="{{ route('attachment.download', $att->id) }}" class="btn btn-sm btn-outline-primary" title="Muat Turun"><i class="ti tabler-download"></i></a>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @else
      <p class="text-center text-muted py-4">Tiada lampiran.</p>
      @endif
    </div>
  </div>

  <div class="mt-4">
    <a href="{{ route('surveyor.reports') }}" class="btn btn-outline-secondary"><i class="ti tabler-arrow-left me-1"></i>Kembali</a>
  </div>

  <!-- Attachment Viewer Modal -->
  <div class="modal fade" id="attachmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="attachmentModalTitle">Lihat Lampiran</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-0">
          <div class="text-center p-4 d-none" id="attachmentLoading">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Memuatkan lampiran...</p>
          </div>
          <iframe id="attachmentViewer" src="" style="width:100%; height:80vh; border:none; display:none;"></iframe>
          <div id="imageViewerContainer" class="text-center p-3" style="display:none; max-height: 80vh; overflow: auto;">
             <img id="imageViewer" src="" style="max-width: 100%; height: auto;" />
          </div>
        </div>
      </div>
    </div>
  </div>

  @push('scripts')
  @vite(['resources/js/gis-map-picker.js'])
  <script>
    function viewAttachment(url, filename) {
      document.getElementById('attachmentModalTitle').innerText = filename;
      const iframe = document.getElementById('attachmentViewer');
      const imgContainer = document.getElementById('imageViewerContainer');
      const img = document.getElementById('imageViewer');
      const loading = document.getElementById('attachmentLoading');
      
      iframe.style.display = 'none';
      imgContainer.style.display = 'none';
      loading.classList.remove('d-none');
      
      const modal = new bootstrap.Modal(document.getElementById('attachmentModal'));
      modal.show();
      
      if (filename.toLowerCase().match(/\.(jpg|jpeg|png|gif)$/)) {
         img.onload = () => { loading.classList.add('d-none'); imgContainer.style.display = 'block'; };
         img.src = url;
      } else {
         iframe.onload = () => { loading.classList.add('d-none'); iframe.style.display = 'block'; };
         iframe.src = url;
      }
    }
    
    document.getElementById('attachmentModal').addEventListener('hidden.bs.modal', function () {
      document.getElementById('attachmentViewer').src = '';
      document.getElementById('imageViewer').src = '';
    });
  </script>
  @php
    $surveyLayersData = $report->attachments
      ->whereIn('document_type', ['survey_3d', 'survey_2d'])
      ->where('parse_status', 'ok')
      ->map(fn($a) => [
        'file_name'    => $a->file_name,
        'parse_status' => $a->parse_status,
        'parsed_data'  => $a->parsed_data,
      ])
      ->values();
  @endphp
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const surveyLayers = @json($surveyLayersData);
      window.gisMapPicker.initMapPicker({
        mapElementId: 'report-map',
        initialLat: {{ $report->latitude ?? 3.1073 }},
        initialLng: {{ $report->longitude ?? 101.6067 }},
        initialZoom: 16,
        initialGisData: @json($report->gis_data),
      });
      window.gisMapPicker.loadStoredSurveys(surveyLayers);
    });
  </script>
  @endpush
</div>
