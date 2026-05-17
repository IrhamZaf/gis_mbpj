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
          @php $pdfAttachments = $report->attachments->where('document_type', 'survey_1d'); @endphp
          @if ($pdfAttachments->count())
          <div class="p-3 border-top">
            <label class="form-label small text-muted">Laporan 1D (PDF)</label>
            @foreach ($pdfAttachments as $pdf)
            <div class="mb-3">
              <div class="small mb-1">{!! $pdf->document_type_badge !!} {{ $pdf->file_name }}</div>
              <iframe src="{{ asset('storage/' . $pdf->file_path) }}" style="width:100%;height:280px;border:1px solid var(--bs-border-color);border-radius:8px;"></iframe>
            </div>
            @endforeach
          </div>
          @endif
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
              <td><a href="{{ asset('storage/' . $att->file_path) }}" class="btn btn-sm btn-outline-primary" download><i class="ti tabler-download"></i></a></td>
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
    <a href="{{ route('engineer.reports') }}" class="btn btn-outline-secondary"><i class="ti tabler-arrow-left me-1"></i>Kembali</a>
  </div>

  @push('scripts')
  @vite(['resources/js/gis-map-picker.js'])
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const surveyLayers = @json($report->attachments->whereIn('document_type', ['survey_3d', 'survey_2d'])->where('parse_status', 'ok')->map(fn($a) => [
        'file_name' => $a->file_name, 'parse_status' => $a->parse_status, 'parsed_data' => $a->parsed_data,
      ])->values());
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
