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
      <li class="menu-header small"><span class="menu-header-text">{{ __("app.main") }}</span></li>
      <li class="menu-item {{ $currentRouteName === 'superadmin.dashboard' ? 'active' : '' }}">
        <a href="{{ route('superadmin.dashboard') }}" class="menu-link">
          <i class="icon-base ti tabler-smart-home"></i>
          <div>{{ __("app.dashboard") }}</div>
        </a>
      </li>
      <li class="menu-item {{ str_starts_with($currentRouteName, 'saliran-cerun.') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="icon-base ti tabler-building-tunnel"></i>
          <div>{{ __("app.saliran_cerun") }}</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item {{ $currentRouteName === 'saliran-cerun.dashboard' ? 'active' : '' }}">
            <a href="{{ route('saliran-cerun.dashboard') }}" class="menu-link"><div>{{ __("app.dashboard") }}</div></a>
          </li>
          <li class="menu-item {{ $currentRouteName === 'saliran-cerun.sinkhole' ? 'active' : '' }}">
            <a href="{{ route('saliran-cerun.sinkhole') }}" class="menu-link"><div>{{ __("app.sinkhole") }}</div></a>
          </li>
          <li class="menu-item {{ $currentRouteName === 'saliran-cerun.cerun' ? 'active' : '' }}">
            <a href="{{ route('saliran-cerun.cerun') }}" class="menu-link"><div>{{ __("app.cerun_runtuh") }}</div></a>
          </li>
        </ul>
      </li>
      <li class="menu-item {{ $currentRouteName === 'superadmin.map' ? 'active' : '' }}">
        <a href="{{ route('superadmin.map') }}" class="menu-link">
          <i class="icon-base ti tabler-map"></i>
          <div>{{ __("app.interactive_map") }}</div>
        </a>
      </li>

      <li class="menu-header small"><span class="menu-header-text">{{ __("app.management") }}</span></li>
      <li class="menu-item {{ $currentRouteName === 'superadmin.units' ? 'active' : '' }}">
        <a href="{{ route('superadmin.units') }}" class="menu-link">
          <i class="icon-base ti tabler-building-community"></i>
          <div>{{ __("app.units") }}</div>
        </a>
      </li>
      <li class="menu-item {{ $currentRouteName === 'superadmin.users' ? 'active' : '' }}">
        <a href="{{ route('superadmin.users') }}" class="menu-link">
          <i class="icon-base ti tabler-users"></i>
          <div>{{ __("app.users") }}</div>
        </a>
      </li>
      <li class="menu-item {{ $currentRouteName === 'superadmin.categories' ? 'active' : '' }}">
        <a href="{{ route('superadmin.categories') }}" class="menu-link">
          <i class="icon-base ti tabler-category"></i>
          <div>{{ __("app.categories") }}</div>
        </a>
      </li>

      <li class="menu-header small"><span class="menu-header-text">{{ __("app.monitoring") }}</span></li>
      <li class="menu-item {{ str_starts_with($currentRouteName, 'engineering.') ? 'active' : '' }}">
        <a href="{{ route('engineering.hub') }}" class="menu-link">
          <i class="icon-base ti tabler-building-community"></i>
          <div>{{ __("app.engineering") }}</div>
        </a>
      </li>
      <li class="menu-item {{ $currentRouteName === 'superadmin.reports' ? 'active' : '' }}">
        <a href="{{ route('superadmin.reports') }}" class="menu-link">
          <i class="icon-base ti tabler-report-analytics"></i>
          <div>{{ __("app.all_reports") }}</div>
        </a>
      </li>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- SURVEYOR MENU                                              --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @elseif ($user && $user->isSurveyor())
      <li class="menu-header small"><span class="menu-header-text">{{ __("app.main") }}</span></li>
      <li class="menu-item {{ $currentRouteName === 'surveyor.dashboard' ? 'active' : '' }}">
        <a href="{{ route('surveyor.dashboard') }}" class="menu-link">
          <i class="icon-base ti tabler-smart-home"></i>
          <div>{{ __("app.dashboard") }}</div>
        </a>
      </li>
      <li class="menu-item {{ str_starts_with($currentRouteName, 'saliran-cerun.') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="icon-base ti tabler-building-tunnel"></i>
          <div>{{ __("app.saliran_cerun") }}</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item {{ $currentRouteName === 'saliran-cerun.dashboard' ? 'active' : '' }}">
            <a href="{{ route('saliran-cerun.dashboard') }}" class="menu-link"><div>{{ __("app.dashboard") }}</div></a>
          </li>
          <li class="menu-item {{ $currentRouteName === 'saliran-cerun.sinkhole' ? 'active' : '' }}">
            <a href="{{ route('saliran-cerun.sinkhole') }}" class="menu-link"><div>{{ __("app.sinkhole") }}</div></a>
          </li>
          <li class="menu-item {{ $currentRouteName === 'saliran-cerun.cerun' ? 'active' : '' }}">
            <a href="{{ route('saliran-cerun.cerun') }}" class="menu-link"><div>{{ __("app.cerun_runtuh") }}</div></a>
          </li>
        </ul>
      </li>
      <li class="menu-item {{ $currentRouteName === 'surveyor.map' ? 'active' : '' }}">
        <a href="{{ route('surveyor.map') }}" class="menu-link">
          <i class="icon-base ti tabler-map"></i>
          <div>{{ __("app.interactive_map") }}</div>
        </a>
      </li>

      <li class="menu-header small"><span class="menu-header-text">{{ __("app.reports") }}</span></li>
      <li class="menu-item {{ $currentRouteName === 'surveyor.reports.create' ? 'active' : '' }}">
        <a href="{{ route('surveyor.reports.create') }}" class="menu-link">
          <i class="icon-base ti tabler-plus"></i>
          <div>{{ __("app.create_report") }}</div>
        </a>
      </li>
      <li class="menu-item {{ str_starts_with($currentRouteName, 'surveyor.reports') && $currentRouteName !== 'surveyor.reports.create' ? 'active' : '' }}">
        <a href="{{ route('surveyor.reports') }}" class="menu-link">
          <i class="icon-base ti tabler-list"></i>
          <div>{{ __("app.my_reports") }}</div>
        </a>
      </li>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- ENGINEER MENU                                              --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @elseif ($user && $user->isEngineer())
      <li class="menu-header small"><span class="menu-header-text">{{ __("app.main") }}</span></li>
      <li class="menu-item {{ $currentRouteName === 'engineer.dashboard' ? 'active' : '' }}">
        <a href="{{ route('engineer.dashboard') }}" class="menu-link">
          <i class="icon-base ti tabler-smart-home"></i>
          <div>{{ __("app.dashboard") }}</div>
        </a>
      </li>
      <li class="menu-item {{ str_starts_with($currentRouteName, 'saliran-cerun.') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="icon-base ti tabler-building-tunnel"></i>
          <div>{{ __("app.saliran_cerun") }}</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item {{ $currentRouteName === 'saliran-cerun.dashboard' ? 'active' : '' }}">
            <a href="{{ route('saliran-cerun.dashboard') }}" class="menu-link"><div>{{ __("app.dashboard") }}</div></a>
          </li>
          <li class="menu-item {{ $currentRouteName === 'saliran-cerun.sinkhole' ? 'active' : '' }}">
            <a href="{{ route('saliran-cerun.sinkhole') }}" class="menu-link"><div>{{ __("app.sinkhole") }}</div></a>
          </li>
          <li class="menu-item {{ $currentRouteName === 'saliran-cerun.cerun' ? 'active' : '' }}">
            <a href="{{ route('saliran-cerun.cerun') }}" class="menu-link"><div>{{ __("app.cerun_runtuh") }}</div></a>
          </li>
        </ul>
      </li>
      <li class="menu-item {{ $currentRouteName === 'engineer.map' ? 'active' : '' }}">
        <a href="{{ route('engineer.map') }}" class="menu-link">
          <i class="icon-base ti tabler-map"></i>
          <div>{{ __("app.interactive_map") }}</div>
        </a>
      </li>

      <li class="menu-header small"><span class="menu-header-text">{{ __("app.reports") }}</span></li>
      <li class="menu-item {{ str_starts_with($currentRouteName, 'engineer.reports') ? 'active' : '' }}">
        <a href="{{ route('engineer.reports') }}" class="menu-link">
          <i class="icon-base ti tabler-report-search"></i>
          <div>{{ __("app.verification") }}</div>
        </a>
      </li>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- TA MENU                                                    --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @elseif ($user && $user->isTa())
      <li class="menu-header small"><span class="menu-header-text">{{ __("app.main") }}</span></li>
      <li class="menu-item {{ $currentRouteName === 'ta.dashboard' ? 'active' : '' }}">
        <a href="{{ route('ta.dashboard') }}" class="menu-link">
          <i class="icon-base ti tabler-smart-home"></i>
          <div>{{ __("app.dashboard") }}</div>
        </a>
      </li>
      <li class="menu-item {{ str_starts_with($currentRouteName, 'saliran-cerun.') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="icon-base ti tabler-building-tunnel"></i>
          <div>{{ __("app.saliran_cerun") }}</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item {{ $currentRouteName === 'saliran-cerun.dashboard' ? 'active' : '' }}">
            <a href="{{ route('saliran-cerun.dashboard') }}" class="menu-link"><div>{{ __("app.dashboard") }}</div></a>
          </li>
          <li class="menu-item {{ $currentRouteName === 'saliran-cerun.sinkhole' ? 'active' : '' }}">
            <a href="{{ route('saliran-cerun.sinkhole') }}" class="menu-link"><div>{{ __("app.sinkhole") }}</div></a>
          </li>
          <li class="menu-item {{ $currentRouteName === 'saliran-cerun.cerun' ? 'active' : '' }}">
            <a href="{{ route('saliran-cerun.cerun') }}" class="menu-link"><div>{{ __("app.cerun_runtuh") }}</div></a>
          </li>
        </ul>
      </li>
      <li class="menu-item {{ $currentRouteName === 'ta.map' ? 'active' : '' }}">
        <a href="{{ route('ta.map') }}" class="menu-link">
          <i class="icon-base ti tabler-map"></i>
          <div>{{ __("app.interactive_map") }}</div>
        </a>
      </li>
      <li class="menu-header small"><span class="menu-header-text">{{ __("app.site_visits") }}</span></li>
      <li class="menu-item {{ str_starts_with($currentRouteName, 'ta.') && $currentRouteName !== 'ta.dashboard' && $currentRouteName !== 'ta.map' ? 'active' : '' }}">
        <a href="{{ route('ta.reports') }}" class="menu-link">
          <i class="icon-base ti tabler-map-pin"></i>
          <div>{{ __("app.visit_list") }}</div>
        </a>
      </li>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- DIRECTOR MENU                                              --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @elseif ($user && $user->isDirector())
      <li class="menu-header small"><span class="menu-header-text">{{ __("app.main") }}</span></li>
      <li class="menu-item {{ $currentRouteName === 'director.dashboard' ? 'active' : '' }}">
        <a href="{{ route('director.dashboard') }}" class="menu-link">
          <i class="icon-base ti tabler-smart-home"></i>
          <div>{{ __("app.dashboard") }}</div>
        </a>
      </li>
      <li class="menu-item {{ str_starts_with($currentRouteName, 'saliran-cerun.') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="icon-base ti tabler-building-tunnel"></i>
          <div>{{ __("app.saliran_cerun") }}</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item {{ $currentRouteName === 'saliran-cerun.dashboard' ? 'active' : '' }}">
            <a href="{{ route('saliran-cerun.dashboard') }}" class="menu-link"><div>{{ __("app.dashboard") }}</div></a>
          </li>
          <li class="menu-item {{ $currentRouteName === 'saliran-cerun.sinkhole' ? 'active' : '' }}">
            <a href="{{ route('saliran-cerun.sinkhole') }}" class="menu-link"><div>{{ __("app.sinkhole") }}</div></a>
          </li>
          <li class="menu-item {{ $currentRouteName === 'saliran-cerun.cerun' ? 'active' : '' }}">
            <a href="{{ route('saliran-cerun.cerun') }}" class="menu-link"><div>{{ __("app.cerun_runtuh") }}</div></a>
          </li>
        </ul>
      </li>
      <li class="menu-item {{ str_starts_with($currentRouteName, 'engineering.') ? 'active' : '' }}">
        <a href="{{ route('engineering.hub') }}" class="menu-link">
          <i class="icon-base ti tabler-building-community"></i>
          <div>{{ __("app.engineering") }}</div>
        </a>
      </li>
      <li class="menu-item {{ $currentRouteName === 'director.map' ? 'active' : '' }}">
        <a href="{{ route('director.map') }}" class="menu-link">
          <i class="icon-base ti tabler-map"></i>
          <div>{{ __("app.interactive_map") }}</div>
        </a>
      </li>
      <li class="menu-header small"><span class="menu-header-text">{{ __("app.approvals") }}</span></li>
      <li class="menu-item {{ str_starts_with($currentRouteName, 'director.reports') ? 'active' : '' }}">
        <a href="{{ route('director.reports') }}" class="menu-link">
          <i class="icon-base ti tabler-stamp"></i>
          <div>{{ __("app.pending_approval") }}</div>
        </a>
      </li>
    @endif

  </ul>
</aside>
