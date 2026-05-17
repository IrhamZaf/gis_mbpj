{{-- component/sidebar.blade.php — Role-aware sidebar --}}
@php
  use Illuminate\Support\Facades\Route;
  use Illuminate\Support\Facades\Auth;
  $configData = Helper::appClasses();
  $user = Auth::user();
  $currentRouteName = Route::currentRouteName() ?? '';
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu" @foreach ($configData['menuAttributes'] as $attribute =>
  $value)
  {{ $attribute }}="{{ $value }}" @endforeach>

  <!-- App Brand / Logo -->
  @if (!isset($navbarFull))
  <div class="app-brand demo">
    <a href="{{ url('/') }}" class="app-brand-link">
      <span class="app-brand-logo demo">@include('_partials.macros')</span>
      <span class="app-brand-text demo menu-text fw-bold ms-3">{{ config('variables.templateName') }}</span>
    </a>
    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
      <i class="icon-base ti menu-toggle-icon d-none d-xl-block"></i>
      <i class="icon-base ti tabler-x d-block d-xl-none"></i>
    </a>
  </div>
  @endif

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- SUPERADMIN MENU                                            --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @if ($user && $user->isSuperadmin())
      <li class="menu-header small"><span class="menu-header-text">Utama</span></li>
      <li class="menu-item {{ $currentRouteName === 'superadmin.dashboard' ? 'active' : '' }}">
        <a href="{{ route('superadmin.dashboard') }}" class="menu-link">
          <i class="icon-base ti tabler-smart-home"></i>
          <div>Dashboard</div>
        </a>
      </li>
      <li class="menu-item {{ $currentRouteName === 'superadmin.map' ? 'active' : '' }}">
        <a href="{{ route('superadmin.map') }}" class="menu-link">
          <i class="icon-base ti tabler-map"></i>
          <div>Peta Interaktif</div>
        </a>
      </li>

      <li class="menu-header small"><span class="menu-header-text">Pengurusan</span></li>
      <li class="menu-item {{ $currentRouteName === 'superadmin.users' ? 'active' : '' }}">
        <a href="{{ route('superadmin.users') }}" class="menu-link">
          <i class="icon-base ti tabler-users"></i>
          <div>Pengguna</div>
        </a>
      </li>
      <li class="menu-item {{ $currentRouteName === 'superadmin.categories' ? 'active' : '' }}">
        <a href="{{ route('superadmin.categories') }}" class="menu-link">
          <i class="icon-base ti tabler-category"></i>
          <div>Kategori Laporan</div>
        </a>
      </li>

      <li class="menu-header small"><span class="menu-header-text">Pemantauan</span></li>
      <li class="menu-item {{ $currentRouteName === 'superadmin.reports' ? 'active' : '' }}">
        <a href="{{ route('superadmin.reports') }}" class="menu-link">
          <i class="icon-base ti tabler-report-analytics"></i>
          <div>Semua Laporan</div>
        </a>
      </li>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- SURVEYOR MENU                                              --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @elseif ($user && $user->isSurveyor())
      <li class="menu-header small"><span class="menu-header-text">Utama</span></li>
      <li class="menu-item {{ $currentRouteName === 'surveyor.dashboard' ? 'active' : '' }}">
        <a href="{{ route('surveyor.dashboard') }}" class="menu-link">
          <i class="icon-base ti tabler-smart-home"></i>
          <div>Dashboard</div>
        </a>
      </li>
      <li class="menu-item {{ $currentRouteName === 'surveyor.map' ? 'active' : '' }}">
        <a href="{{ route('surveyor.map') }}" class="menu-link">
          <i class="icon-base ti tabler-map"></i>
          <div>Peta Interaktif</div>
        </a>
      </li>

      <li class="menu-header small"><span class="menu-header-text">Laporan</span></li>
      <li class="menu-item {{ str_starts_with($currentRouteName, 'surveyor.reports') ? 'active' : '' }}">
        <a href="{{ route('surveyor.reports') }}" class="menu-link">
          <i class="icon-base ti tabler-list"></i>
          <div>Senarai Laporan</div>
        </a>
      </li>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- ENGINEER MENU                                              --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @elseif ($user && $user->isEngineer())
      <li class="menu-header small"><span class="menu-header-text">Utama</span></li>
      <li class="menu-item {{ $currentRouteName === 'engineer.dashboard' ? 'active' : '' }}">
        <a href="{{ route('engineer.dashboard') }}" class="menu-link">
          <i class="icon-base ti tabler-smart-home"></i>
          <div>Dashboard</div>
        </a>
      </li>
      <li class="menu-item {{ $currentRouteName === 'engineer.map' ? 'active' : '' }}">
        <a href="{{ route('engineer.map') }}" class="menu-link">
          <i class="icon-base ti tabler-map"></i>
          <div>Peta Interaktif</div>
        </a>
      </li>

      <li class="menu-header small"><span class="menu-header-text">Laporan</span></li>
      <li class="menu-item {{ str_starts_with($currentRouteName, 'engineer.reports') ? 'active' : '' }}">
        <a href="{{ route('engineer.reports') }}" class="menu-link">
          <i class="icon-base ti tabler-report-search"></i>
          <div>Senarai Laporan</div>
        </a>
      </li>
    @endif

  </ul>
</aside>
