<div>
  @include('livewire.partials.dashboard-welcome', [
    'user' => $user,
    'roleLabel' => 'Engineering',
    'subtitle' => 'Pilih unit untuk melihat dashboard, staf dan laporan khusus unit tersebut.',
    'heroIcon' => 'tabler-building-community',
    'actions' => [
      ['label' => 'Semua Laporan', 'url' => auth()->user()->isSuperadmin() ? route('superadmin.reports') : route('director.reports'), 'icon' => 'tabler-list', 'class' => 'btn-primary'],
    ],
  ])

  <div class="row g-4">
    @foreach ($units as $row)
      @php
        $u = $row['unit'];
        $theme = $row['theme'];
      @endphp
      <div class="col-md-6 col-xl-4">
        <a href="{{ route('engineering.unit', $u) }}" class="text-decoration-none">
          <div class="card h-100 border-0 shadow-sm" style="border-top:4px solid {{ $theme['color'] }} !important;">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                  <span class="badge bg-label-{{ $theme['label'] }} mb-2">{{ $theme['code'] }}</span>
                  <h5 class="mb-0 fw-bold text-body">Unit {{ $u->name }}</h5>
                  <p class="small text-muted mb-0 mt-1">{{ $u->description }}</p>
                </div>
                <span class="avatar">
                  <span class="avatar-initial rounded" style="background:{{ $theme['soft'] }};color:{{ $theme['color'] }};">
                    <i class="ti tabler-building"></i>
                  </span>
                </span>
              </div>
              <div class="row g-2 text-center">
                <div class="col-4">
                  <div class="fw-bold">{{ $row['reports'] }}</div>
                  <div class="small text-muted">Laporan</div>
                </div>
                <div class="col-4">
                  <div class="fw-bold">{{ $row['pending'] }}</div>
                  <div class="small text-muted">Aktif</div>
                </div>
                <div class="col-4">
                  <div class="fw-bold">{{ $row['staff'] }}</div>
                  <div class="small text-muted">Staf</div>
                </div>
              </div>
              <div class="mt-3 pt-2 border-top d-flex justify-content-between small text-muted">
                <span>{{ $row['approved'] }} diluluskan</span>
                <span class="fw-medium" style="color:{{ $theme['color'] }};">Buka dashboard →</span>
              </div>
            </div>
          </div>
        </a>
      </div>
    @endforeach
  </div>
</div>
