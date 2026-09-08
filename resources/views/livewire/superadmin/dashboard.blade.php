<div>
  @include('livewire.partials.dashboard-welcome', [
    'user' => $user,
    'roleLabel' => 'Superadmin',
    'subtitle' => 'Pantau pengguna, unit, kategori dan laporan GIS MBSJ dari satu skrin.',
    'heroIcon' => 'tabler-shield-check',
    'actions' => [
      ['label' => 'Peta Interaktif', 'url' => route('superadmin.map'), 'icon' => 'tabler-map', 'class' => 'btn-primary'],
      ['label' => 'Pemantauan Laporan', 'url' => route('superadmin.reports'), 'icon' => 'tabler-report-analytics', 'class' => 'btn-outline-primary'],
      ['label' => 'Unit', 'url' => route('superadmin.units'), 'icon' => 'tabler-building-community', 'class' => 'btn-outline-secondary'],
    ],
  ])

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <div class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label small text-muted mb-1">Unit</label>
          <select wire:model.live="filterUnit" class="form-select">
            <option value="">Semua Unit</option>
            @foreach ($units as $unit)
              <option value="{{ $unit->id }}">{{ $unit->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small text-muted mb-1">Status</label>
          <select wire:model.live="filterStatus" class="form-select">
            <option value="">Semua Status</option>
            <option value="draft">Draf</option>
            <option value="submitted">Dihantar</option>
            <option value="pending_site_visit">Menunggu Lawatan</option>
            <option value="site_visit_in_progress">Lawatan Berjalan</option>
            <option value="pending_engineer_verification">Menunggu Engineer</option>
            <option value="engineer_returned">Dikembalikan Engineer</option>
            <option value="pending_director_approval">Menunggu Pengarah</option>
            <option value="approved">Diluluskan</option>
            <option value="director_rejected">Ditolak Pengarah</option>
            <option value="completed">Selesai</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small text-muted mb-1">Kategori</label>
          <select wire:model.live="filterCategory" class="form-select">
            <option value="">Semua Kategori</option>
            @foreach ($categories as $cat)
              <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3 text-muted small">
          <i class="ti tabler-filter me-1"></i>{{ $totalUnits }} unit · {{ $totalReports }} laporan (ditapis)
        </div>
      </div>
    </div>
  </div>

  {{-- Stat Cards --}}
  <div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="ti tabler-users icon-26px"></i>
              </span>
            </div>
            <div class="text-end">
              <span class="badge bg-label-primary">Sistem</span>
            </div>
          </div>
          <h3 class="mb-1 fw-bold">{{ $totalUsers }}</h3>
          <p class="mb-2 text-muted fw-medium">Jumlah pengguna</p>
          <hr class="my-2">
          <div class="d-flex justify-content-between small text-muted">
            <span><i class="ti tabler-user-search me-1 text-primary"></i>{{ $totalSurveyors }} surveyor</span>
            <span><i class="ti tabler-tools me-1 text-success"></i>{{ $totalEngineers }} engineer</span>
          </div>
          <div class="small text-muted mt-1">
            <i class="ti tabler-building-community me-1"></i>{{ $totalUnits }} unit
            · {{ $totalTa ?? 0 }} TA
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
                <i class="ti tabler-report icon-26px"></i>
              </span>
            </div>
            <div class="text-end">
              <span class="badge bg-label-success">+{{ $reportsThisWeek }} minggu ini</span>
            </div>
          </div>
          <h3 class="mb-1 fw-bold">{{ $totalReports }}</h3>
          <p class="mb-2 text-muted fw-medium">Jumlah laporan</p>
          <hr class="my-2">
          <div class="d-flex justify-content-between small text-muted">
            <span><i class="ti tabler-send me-1 text-info"></i>{{ $submittedReports }} dihantar+</span>
            <span><i class="ti tabler-file me-1 text-warning"></i>{{ $draftReports }} draf</span>
          </div>
          <div class="small text-muted mt-1"><i class="ti tabler-circle-check me-1 text-success"></i>{{ $completedReports }} selesai</div>
          <div class="small text-muted mt-1">
            Lawatan {{ $pendingSiteVisit ?? 0 }} · Engineer {{ $pendingEngineer ?? 0 }} · Pengarah {{ $pendingDirector ?? 0 }}
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
                <i class="ti tabler-send icon-26px"></i>
              </span>
            </div>
            <div class="text-end">
              <span class="badge bg-label-info">+{{ $submittedThisWeek }} minggu ini</span>
            </div>
          </div>
          <h3 class="mb-1 fw-bold">{{ $submittedReports }}</h3>
          <p class="mb-2 text-muted fw-medium">Laporan dihantar</p>
          <hr class="my-2">
          <div class="small text-muted">
            <i class="ti tabler-checks me-1 text-success"></i>Sedia untuk semakan engineer
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
                <i class="ti tabler-map-pin icon-26px"></i>
              </span>
            </div>
            <div class="text-end">
              <span class="badge bg-label-warning">{{ $totalCategories }} kategori</span>
            </div>
          </div>
          <h3 class="mb-1 fw-bold">{{ $mappedReports }}</h3>
          <p class="mb-2 text-muted fw-medium">Berkoordinat GIS</p>
          <hr class="my-2">
          <div class="small text-muted">
            <i class="ti tabler-map-2 me-1 text-warning"></i>Boleh dipaparkan pada peta
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Chart + Category --}}
  <div class="row g-4 mb-4">
    <div class="col-lg-4">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
          <h6 class="mb-0 fw-semibold">
            <i class="ti tabler-building-community me-2 text-primary"></i>Laporan Mengikut Unit
          </h6>
        </div>
        <div class="card-body">
          @forelse ($reportsByUnit as $unit)
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span class="fw-medium">{{ $unit->name }}</span>
              <span class="badge bg-label-primary">{{ $unit->reports_count }}</span>
            </div>
          @empty
            <div class="text-muted text-center py-4">Tiada unit.</div>
          @endforelse
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
          <h6 class="mb-0 fw-semibold">
            <i class="ti tabler-chart-bar me-2 text-primary"></i>Aktiviti 7 Hari
          </h6>
          @php $weekTotal = $trendDays->sum('total'); @endphp
          <span class="badge bg-label-primary">{{ $weekTotal }} jumlah</span>
        </div>
        <div class="card-body">
          <div class="d-flex align-items-end gap-2" style="height:120px;">
            @foreach ($trendDays as $day)
              @php $pct = max(8, (int) round(($day['total'] / $trendMax) * 100)); @endphp
              <div class="flex-fill text-center d-flex flex-column justify-content-end align-items-center" style="height:100%;">
                <div class="fw-bold text-primary small mb-1" style="font-size:11px;">
                  {{ $day['total'] ?: '' }}
                </div>
                <div class="w-100 mx-1 rounded-top"
                  style="height:{{ $pct }}%;min-height:6px;background:{{ $day['total'] ? 'var(--bs-primary)' : '#e0e0e0' }};transition:height .3s;cursor:default;"
                  title="{{ $day['total'] }} laporan pada {{ $day['label'] }}"></div>
                <div class="small text-muted mt-2" style="font-size:11px;">{{ $day['label'] }}</div>
              </div>
            @endforeach
          </div>
          <div class="mt-3 pt-2 border-top d-flex justify-content-between small text-muted">
            <span>Purata: <strong class="text-body">{{ $weekTotal > 0 ? round($weekTotal / 7, 1) : 0 }}/hari</strong></span>
            <span>Tertinggi: <strong class="text-body">{{ $trendMax }}</strong></span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom">
          <h6 class="mb-0 fw-semibold">
            <i class="ti tabler-category me-2 text-success"></i>Mengikut Kategori
          </h6>
          <a href="{{ route('superadmin.categories') }}" class="btn btn-sm btn-outline-primary">
            <i class="ti tabler-settings me-1"></i>Urus
          </a>
        </div>
        <div class="card-body">
          @php
            $colors = ['primary','success','info','warning','danger','secondary'];
          @endphp
          @forelse ($reportsByCategory as $i => $cat)
            @php
              $pct = $totalReports > 0 ? round(($cat->reports_count / $totalReports) * 100) : 0;
              $color = $colors[$i % count($colors)];
            @endphp
            <div class="mb-3">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="d-flex align-items-center gap-2">
                  <span class="badge bg-label-{{ $color }}" style="width:10px;height:10px;padding:0;border-radius:50%;display:inline-block;"></span>
                  <span class="fw-medium small">{{ $cat->name }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <span class="fw-semibold small">{{ $cat->reports_count }}</span>
                  <span class="text-muted small" style="min-width:36px;text-align:right;">{{ $pct }}%</span>
                </div>
              </div>
              <div class="progress" style="height:7px;border-radius:4px;">
                <div class="progress-bar bg-{{ $color }}" role="progressbar"
                  style="width:{{ $pct }}%;border-radius:4px;"
                  aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div>
          @empty
            <div class="text-center text-muted py-5">
              <i class="ti tabler-folder-off icon-40px d-block mb-2 text-muted"></i>
              <p class="mb-0">Tiada kategori lagi.</p>
            </div>
          @endforelse
        </div>
      </div>
    </div>
  </div>

  {{-- Recent Reports --}}
  <div class="card border-0 shadow-sm">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
      <h6 class="mb-0 fw-semibold">
        <i class="ti tabler-clock me-2 text-primary"></i>Laporan Terkini
      </h6>
      <a href="{{ route('superadmin.reports') }}" class="btn btn-sm btn-outline-primary">
        <i class="ti tabler-list me-1"></i>Lihat semua
      </a>
    </div>
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">No. Laporan</th>
            <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">Tajuk / Lokasi</th>
            <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">Unit</th>
            <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">Kategori</th>
            <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">Surveyor</th>
            <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">Status</th>
            <th class="fw-semibold small text-uppercase text-muted" style="font-size:11px;">Tarikh</th>
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
                  <div class="small text-muted text-truncate d-flex align-items-center gap-1" style="max-width:220px;">
                    <i class="ti tabler-map-pin" style="font-size:12px;"></i>{{ $r->location_name }}
                  </div>
                @endif
              </td>
              <td>{{ $r->unit->name ?? '-' }}</td>
              <td>
                @if ($r->category)
                  <span class="badge bg-label-secondary">{{ $r->category->name }}</span>
                @else
                  <span class="text-muted">-</span>
                @endif
              </td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="avatar avatar-xs">
                    <span class="avatar-initial rounded-circle bg-label-primary small">
                      {{ strtoupper(substr($r->user->name ?? 'U', 0, 1)) }}
                    </span>
                  </div>
                  <span class="small">{{ $r->user->name ?? '-' }}</span>
                </div>
              </td>
              <td>{!! $r->status_badge !!}</td>
              <td class="text-nowrap small text-muted">{{ $r->created_at->format('d/m/Y') }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-5">
                <i class="ti tabler-inbox icon-32px d-block mb-2 text-muted"></i>
                Belum ada laporan dalam sistem.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
