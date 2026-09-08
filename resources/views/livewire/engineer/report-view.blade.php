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
        <div class="card-header border-bottom d-flex justify-content-between">
          <h5 class="mb-0">{{ $report->title }}</h5>
          {!! $report->status_badge !!}
        </div>
        <div class="card-body">
          <table class="table table-borderless mb-0">
            <tr><td class="fw-semibold" width="40%">No. Fail</td><td><code>{{ $report->file_number ?? $report->report_number }}</code></td></tr>
            <tr><td class="fw-semibold">Unit</td><td>{{ $report->unit->name ?? '-' }}</td></tr>
            <tr><td class="fw-semibold">Kategori</td><td>{{ $report->category->name ?? '-' }}</td></tr>
            <tr><td class="fw-semibold">Surveyor</td><td>{{ $report->user->name ?? '-' }}</td></tr>
            <tr><td class="fw-semibold">Vendor</td><td>{{ $report->vendor_name ?? '-' }}</td></tr>
            <tr><td class="fw-semibold">Lokasi</td><td>{{ $report->location_name ?? '-' }}</td></tr>
            <tr><td class="fw-semibold">Koordinat Surveyor</td><td><code>{{ $report->latitude }}, {{ $report->longitude }}</code></td></tr>
          </table>
          <hr>
          <h6>Keterangan Surveyor</h6>
          <p>{{ $report->description }}</p>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header border-bottom"><h5 class="mb-0">Lawatan Tapak (TA)</h5></div>
        <div class="card-body">
          @if ($report->siteVisit)
            <table class="table table-borderless mb-3">
              <tr><td class="fw-semibold" width="40%">TA</td><td>{{ $report->siteVisit->ta->name ?? '-' }}</td></tr>
              <tr><td class="fw-semibold">Tarikh</td><td>{{ $report->siteVisit->visit_date?->format('d/m/Y') }} {{ $report->siteVisit->visit_time }}</td></tr>
              <tr><td class="fw-semibold">GPS Lawatan</td><td><code>{{ $report->siteVisit->latitude }}, {{ $report->siteVisit->longitude }}</code></td></tr>
              @if ($distanceKm !== null)
                <tr><td class="fw-semibold">Jarak</td><td>{{ $distanceKm }} km dari titik surveyor</td></tr>
              @endif
            </table>
            <h6>Laporan PJ/PJK</h6>
            <p style="white-space:pre-wrap;">{{ $report->siteVisit->laporan_pj_pjk }}</p>
            @if ($report->siteVisit->photos->count())
              <h6 class="mt-3">Foto Lawatan</h6>
              <div class="row g-2">
                @foreach ($report->siteVisit->photos as $photo)
                  <div class="col-4">
                    <img src="{{ $photo->url }}" class="img-fluid rounded" alt="" style="height:90px;width:100%;object-fit:cover;">
                    <div class="small text-muted">{{ $photo->caption }}</div>
                  </div>
                @endforeach
              </div>
            @endif
          @else
            <p class="text-muted mb-0">Belum ada laporan lawatan tapak.</p>
          @endif
        </div>
      </div>
    </div>
  </div>

  @if ($canReview)
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header border-bottom"><h5 class="mb-0">ULASAN PP/PPK/TPKJ</h5></div>
      <div class="card-body">
        <textarea wire:model="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="4" placeholder="Ulasan / pengesahan / sebab pemulangan..."></textarea>
        @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="d-flex flex-wrap gap-2 mt-3">
          @if ($report->workflow_status === 'director_rejected')
            <button type="button" wire:click="resubmitToDirector" class="btn btn-outline-primary">Hantar Semula ke Proses Semakan</button>
          @endif
          <button type="button" wire:click="verify" class="btn btn-success" wire:confirm="Sahkan laporan ini?">
            <i class="ti tabler-check me-1"></i>Sahkan
          </button>
          <button type="button" wire:click="returnToTa" class="btn btn-outline-danger" wire:confirm="Kembalikan kepada TA?">
            <i class="ti tabler-arrow-back-up me-1"></i>Kembalikan
          </button>
        </div>
      </div>
    </div>
  @endif

  @include('livewire.partials.workflow-audit', ['report' => $report])

  <div class="d-flex gap-2">
    <a href="{{ route('engineer.reports') }}" class="btn btn-outline-secondary">Kembali</a>
    @can('downloadPdf', $report)
      <a href="{{ route('reports.site-visit-pdf', $report) }}" class="btn btn-outline-primary" target="_blank">
        <i class="ti tabler-file-type-pdf me-1"></i>PDF
      </a>
    @endcan
  </div>
</div>
