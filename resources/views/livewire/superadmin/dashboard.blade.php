<div>
  <div class="row">
    <!-- Stats cards -->
    <div class="col-xl-3 col-sm-6 mb-6">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <p class="mb-1 text-body">Jumlah Pengguna</p>
              <h4 class="mb-0">{{ $totalUsers }}</h4>
            </div>
            <div class="avatar"><span class="avatar-initial rounded bg-label-primary"><i class="icon-base ti tabler-users icon-28px"></i></span></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-6">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <p class="mb-1 text-body">Jumlah Laporan</p>
              <h4 class="mb-0">{{ $totalReports }}</h4>
            </div>
            <div class="avatar"><span class="avatar-initial rounded bg-label-success"><i class="icon-base ti tabler-report icon-28px"></i></span></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-6">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <p class="mb-1 text-body">Dihantar</p>
              <h4 class="mb-0">{{ $submittedReports }}</h4>
            </div>
            <div class="avatar"><span class="avatar-initial rounded bg-label-info"><i class="icon-base ti tabler-send icon-28px"></i></span></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-6">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <p class="mb-1 text-body">Draf</p>
              <h4 class="mb-0">{{ $draftReports }}</h4>
            </div>
            <div class="avatar"><span class="avatar-initial rounded bg-label-warning"><i class="icon-base ti tabler-file-text icon-28px"></i></span></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
