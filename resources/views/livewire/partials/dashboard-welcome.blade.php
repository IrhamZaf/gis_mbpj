@php
  $greeting = now()->hour < 12 ? 'Selamat pagi' : (now()->hour < 18 ? 'Selamat petang' : 'Selamat malam');
  $mapRoute = $mapRoute ?? '#';
@endphp
<div class="card mb-6 overflow-hidden border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="row g-0 align-items-stretch">
      <div class="col-lg-8">
        <div class="p-5 p-lg-6">
          <div class="d-flex align-items-center gap-3 mb-3">
            <img src="{{ asset('assets/img/branding/mbsj-logo.png') }}" alt="MBSJ"
              width="52" height="52" class="rounded-circle border"
              style="object-fit:cover;background:#111;">
            <div>
              <span class="badge bg-label-primary fs-tiny">{{ $roleLabel }}</span>
              <div class="text-muted small mt-1">
                <i class="ti tabler-calendar-event me-1"></i>{{ now()->translatedFormat('l, d F Y') }}
              </div>
            </div>
          </div>
          <h4 class="mb-1 fw-bold">{{ $greeting }}, {{ $user->name }} 👋</h4>
          <p class="mb-4 text-body-secondary" style="max-width:38rem;">{{ $subtitle }}</p>
          <div class="d-flex flex-wrap gap-2">
            @foreach ($actions as $action)
              <a href="{{ $action['url'] }}" class="btn {{ $action['class'] ?? 'btn-primary' }}">
                @if (!empty($action['icon']))
                  <i class="ti {{ $action['icon'] }} me-1"></i>
                @endif
                {{ $action['label'] }}
              </a>
            @endforeach
          </div>
        </div>
      </div>
      <div class="col-lg-4 d-none d-lg-flex align-items-center justify-content-center"
        style="background:linear-gradient(145deg, rgba(13,110,253,.1), rgba(25,135,84,.07));min-height:190px;position:relative;overflow:hidden;">
        <div class="position-absolute" style="top:-30px;right:-30px;width:140px;height:140px;background:rgba(13,110,253,.06);border-radius:50%;"></div>
        <div class="position-absolute" style="bottom:-20px;left:-20px;width:100px;height:100px;background:rgba(25,135,84,.06);border-radius:50%;"></div>
        <div class="text-center px-4 py-5" style="position:relative;z-index:1;">
          <div class="avatar avatar-xl mx-auto mb-3">
            <span class="avatar-initial rounded-circle bg-label-primary" style="width:64px;height:64px;">
              <i class="icon-base ti {{ $heroIcon ?? 'tabler-map-2' }} icon-36px"></i>
            </span>
          </div>
          <h6 class="mb-1 fw-semibold">{{ config('variables.templateName') }}</h6>
          <p class="mb-0 small text-muted">Sistem Maklumat Geografi</p>
          <div class="mt-2">
            <span class="badge bg-label-success small">
              <i class="ti tabler-activity me-1"></i>Aktif
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
