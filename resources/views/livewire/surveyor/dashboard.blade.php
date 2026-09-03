<div>
  @include('livewire.partials.dashboard-welcome', [
    'user' => $user,
    'roleLabel' => 'Admin Surveyor',
    'subtitle' => 'Urus laporan lapangan, lampiran survei dan lokasi GIS dengan pantas.',
    'heroIcon' => 'tabler-clipboard-list',
    'actions' => [
      ['label' => 'Cipta Laporan', 'url' => route('surveyor.reports.create'), 'icon' => 'tabler-plus', 'class' => 'btn-primary'],
      ['label' => 'Senarai Laporan', 'url' => route('surveyor.reports'), 'icon' => 'tabler-list', 'class' => 'btn-outline-primary'],
      ['label' => 'Peta', 'url' => route('surveyor.map'), 'icon' => 'tabler-map', 'class' => 'btn-outline-secondary'],
    ],
  ])

  {{-- Alert draf --}}
  @if ($draftReports > 0)
    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4" role="alert">
      <div class="d-flex align-items-center gap-2">
        <i class="ti tabler-alert-triangle fs-5 text-warning"></i>
        <div>
          <strong>{{ $draftReports }} draf belum dihantar.</strong>
          Lengkapkan dan hantar supaya engineer boleh semak.
        </div>
      </div>
      <a href="{{ route('surveyor.reports') }}" class="btn btn-sm btn-warning">
        <i class="ti tabler-pencil me-1"></i>Semak draf
      </a>
    </div>
  @endif

  {{-- Stat Cards --}}
  <div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="ti tabler-report icon-26px"></i>
              </span>
            </div>
            <span class="badge bg-label-primary">+{{ $reportsThisWeek }} minggu ini</span>
          </div>
          <h3 class="mb-1 fw-bold">{{ $totalReports }}</h3>
          <p class="mb-2 text-muted fw-medium">Jumlah laporan saya</p>
          <hr class="my-2">
          <div class="d-flex justify-content-between small text-muted">
            <span><i class="ti tabler-send me-1 text-success"></i>{{ $submittedReports }} dihantar</span>
            <span><i class="ti tabler-file me-1 text-warning"></i>{{ $draftReports }} draf</span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="ti tabler-file-text icon-26px"></i>
              </span>
            </div>
            @if ($draftReports > 0)
              <span class="badge bg-label-warning">Perlu tindakan</span>
            @else
              <span class="badge bg-label-success">Kemas kini</span>
            @endif
          </div>
          <h3 class="mb-1 fw-bold">{{ $draftReports }}</h3>
          <p class="mb-2 text-muted fw-medium">Draf belum dihantar</p>
          <hr class="my-2">
          <div class="small text-muted">
            @if ($draftReports > 0)
              <i class="ti tabler-clock me-1 text-warning"></i>Menunggu untuk dihantar
            @else
              <i class="ti tabler-circle-check me-1 text-success"></i>Tiada draf tertangguh
            @endif
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-success">
                <i class="ti tabler-send icon-26px"></i>
              </span>
            </div>
            <span class="badge bg-label-success">Dihantar</span>
          </div>
          <h3 class="mb-1 fw-bold">{{ $submittedReports }}</h3>
          <p class="mb-2 text-muted fw-medium">Sudah dihantar</p>
          <hr class="my-2">
          <div class="small text-muted">
            <i class="ti tabler-checks me-1 text-success"></i>Dalam semakan engineer
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-info">
                <i class="ti tabler-map-pin icon-26px"></i>
              </span>
            </div>
            <span class="badge bg-label-info">GIS</span>
          </div>
          <h3 class="mb-1 fw-bold">{{ $mappedReports }}</h3>
          <p class="mb-2 text-muted fw-medium">Ada koordinat GIS</p>
          <hr class="my-2">
          <div class="small text-muted">
            <i class="ti tabler-map-2 me-1 text-info"></i>Direkod pada peta
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Laporan Terkini + Tindakan Pantas --}}
  <div class="row g-4">
    <div class="col-xl-8">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
          <h6 class="mb-0 fw-semibold">
            <i class="ti tabler-clock me-2 text-primary"></i>Laporan Terkini Saya
          </h6>
          <a href="{{ route('surveyor.reports.create') }}" class="btn btn-primary btn-sm">
            <i class="ti tabler-plus me-1"></i>Cipta Laporan
          </a>
        </div>
        <div class="table-responsive">
          <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
              <tr>
                <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">No. Laporan</th>
                <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">Tajuk / Lokasi</th>
                <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">Kategori</th>
                <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">Status</th>
                <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">Tarikh</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              @forelse ($recentReports as $r)
                <tr>
                  <td>
                    <code class="bg-light px-2 py-1 rounded small">{{ $r->report_number }}</code>
                  </td>
                  <td>
                    <div class="fw-medium">{{ $r->title }}</div>
                    @if ($r->location_name)
                      <div class="small text-muted d-flex align-items-center gap-1" style="font-size:12px;">
                        <i class="ti tabler-map-pin" style="font-size:11px;"></i>{{ Str::limit($r->location_name, 30) }}
                      </div>
                    @endif
                  </td>
                  <td>
                    @if ($r->category)
                      <span class="badge bg-label-secondary">{{ $r->category->name }}</span>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                  <td>{!! $r->status_badge !!}</td>
                  <td class="text-nowrap small text-muted">{{ $r->created_at->format('d/m/Y') }}</td>
                  <td class="text-end text-nowrap">
                    <a href="{{ route('surveyor.reports.view', $r) }}" class="btn btn-sm btn-icon btn-text-secondary" title="Lihat">
                      <i class="ti tabler-eye"></i>
                    </a>
                    @if ($r->status === 'draft')
                      <a href="{{ route('surveyor.reports.edit', $r) }}" class="btn btn-sm btn-icon btn-text-primary" title="Edit">
                        <i class="ti tabler-pencil"></i>
                      </a>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center py-5 text-muted">
                    <i class="ti tabler-inbox icon-32px d-block mb-2 text-muted"></i>
                    Belum ada laporan. <a href="{{ route('surveyor.reports.create') }}">Cipta sekarang</a>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-xl-4">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-header border-bottom">
          <h6 class="mb-0 fw-semibold">
            <i class="ti tabler-bolt me-2 text-warning"></i>Tindakan Pantas
          </h6>
        </div>
        <div class="card-body d-flex flex-column gap-3">
          <a href="{{ route('surveyor.reports.create') }}"
            class="d-flex align-items-center gap-3 p-3 rounded border border-primary border-opacity-25 text-decoration-none text-body"
            style="transition:.2s;background:rgba(13,110,253,.03);">
            <span class="avatar flex-shrink-0">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="ti tabler-plus"></i>
              </span>
            </span>
            <div>
              <div class="fw-semibold">Laporan baharu</div>
              <div class="small text-muted">Rekod tapak & lampiran survei</div>
            </div>
            <i class="ti tabler-chevron-right ms-auto text-muted"></i>
          </a>

          <a href="{{ route('surveyor.map') }}"
            class="d-flex align-items-center gap-3 p-3 rounded border border-success border-opacity-25 text-decoration-none text-body"
            style="transition:.2s;background:rgba(25,135,84,.03);">
            <span class="avatar flex-shrink-0">
              <span class="avatar-initial rounded bg-label-success">
                <i class="ti tabler-map"></i>
              </span>
            </span>
            <div>
              <div class="fw-semibold">Buka peta</div>
              <div class="small text-muted">Lihat semua lokasi laporan</div>
            </div>
            <i class="ti tabler-chevron-right ms-auto text-muted"></i>
          </a>

          <a href="{{ route('surveyor.reports') }}"
            class="d-flex align-items-center gap-3 p-3 rounded border border-info border-opacity-25 text-decoration-none text-body"
            style="transition:.2s;background:rgba(13,202,240,.03);">
            <span class="avatar flex-shrink-0">
              <span class="avatar-initial rounded bg-label-info">
                <i class="ti tabler-folder"></i>
              </span>
            </span>
            <div>
              <div class="fw-semibold">Semua laporan</div>
              <div class="small text-muted">Tapis, cari dan kemaskini</div>
            </div>
            <i class="ti tabler-chevron-right ms-auto text-muted"></i>
          </a>

          @if ($draftReports > 0)
            <a href="{{ route('surveyor.reports') }}"
              class="d-flex align-items-center gap-3 p-3 rounded border border-warning border-opacity-50 text-decoration-none text-body"
              style="transition:.2s;background:rgba(255,193,7,.06);">
              <span class="avatar flex-shrink-0">
                <span class="avatar-initial rounded bg-label-warning">
                  <i class="ti tabler-file-alert"></i>
                </span>
              </span>
              <div>
                <div class="fw-semibold">Draf tertangguh</div>
                <div class="small text-warning fw-semibold">{{ $draftReports }} laporan perlu dihantar</div>
              </div>
              <i class="ti tabler-chevron-right ms-auto text-warning"></i>
            </a>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
