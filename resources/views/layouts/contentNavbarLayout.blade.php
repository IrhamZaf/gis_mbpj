@isset($pageConfigs)
  {!! Helper::updatePageConfig($pageConfigs) !!}
@endisset
@php
  $configData = Helper::appClasses();
@endphp
@extends('layouts/commonMaster')

@php
  /* Display elements */
  $contentNavbar = $contentNavbar ?? true;
  $containerNav = $containerNav ?? 'container-xxl';
  $isNavbar = $isNavbar ?? true;
  $isMenu = $isMenu ?? true;
  $isFlex = $isFlex ?? false;
  $isFooter = $isFooter ?? true;
  $customizerHidden = $customizerHidden ?? '';

  /* HTML Classes */
  $navbarDetached = 'navbar-detached';
  $menuFixed = isset($configData['menuFixed']) ? $configData['menuFixed'] : '';
  if (isset($navbarType)) {
      $configData['navbarType'] = $navbarType;
  }
  $navbarType = isset($configData['navbarType']) ? $configData['navbarType'] : '';
  $footerFixed = isset($configData['footerFixed']) ? $configData['footerFixed'] : '';
  $menuCollapsed = isset($configData['menuCollapsed']) ? $configData['menuCollapsed'] : '';

  /* Content classes */
  $container =
      isset($configData['contentLayout']) && $configData['contentLayout'] === 'compact'
          ? 'container-xxl'
          : 'container-fluid';

@endphp

@section('layoutContent')
  <div class="layout-wrapper layout-content-navbar {{ $isMenu ? '' : 'layout-without-menu' }}">
    <div class="layout-container">

      @if ($isMenu)
        @include('layouts/sections/menu/verticalMenu')
      @endif

      <!-- Layout page -->
      <div class="layout-page">

        {{-- Below commented code read by artisan command while installing jetstream. !! Do not remove if you want to use jetstream. --}}
        {{-- <x-banner /> --}}

        <!-- BEGIN: Navbar-->
        @if ($isNavbar)
          @include('layouts/sections/navbar/navbar')
        @endif
        <!-- END: Navbar-->

        <!-- Content wrapper -->
        <div class="content-wrapper">

          <!-- Content -->
          @if ($isFlex)
            <div class="{{ $container }} d-flex align-items-stretch flex-grow-1 p-0">
            @else
              <div class="{{ $container }} flex-grow-1 container-p-y">
          @endif

          @yield('content')

        </div>
        <!-- / Content -->

        <!-- Footer -->
        @if ($isFooter)
          @include('layouts/sections/footer/footer')
        @endif
        <!-- / Footer -->
        <div class="content-backdrop fade"></div>
      </div>
      <!--/ Content wrapper -->
    </div>
    <!-- / Layout page -->
  </div>

  @if ($isMenu)
    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>
  @endif
  <!-- Drag Target Area To SlideIn Menu On Small Screens -->
  <div class="drag-target"></div>
  </div>
  <!-- / Layout wrapper -->

  @auth
  <script>
    document.body.classList.add('gis-mobile-nav-pad');
  </script>
  <nav class="gis-mobile-bottom-nav" aria-label="Navigasi mudah alih GIS">
    <a class="nav-link {{ request()->routeIs('gis.dashboard') ? 'active' : '' }}" href="{{ url('/') }}">
      <i class="icon-base ti tabler-home d-block mb-1"></i> Utama
    </a>
    <a class="nav-link {{ request()->routeIs('gis.map') || request()->routeIs('gis.heatmap') ? 'active' : '' }}" href="{{ route('gis.map') }}">
      <i class="icon-base ti tabler-map d-block mb-1"></i> Peta
    </a>
    <a class="nav-link {{ request()->routeIs('incidents.*') ? 'active' : '' }}" href="{{ route('incidents.index') }}">
      <i class="icon-base ti tabler-alert-triangle d-block mb-1"></i> Insiden
    </a>
    @if(auth()->user()->isEngineer() || auth()->user()->isAdmin())
    <a class="nav-link {{ request()->routeIs('engineer.*') ? 'active' : '' }}" href="{{ route('engineer.index') }}">
      <i class="icon-base ti tabler-clipboard-check d-block mb-1"></i> Semakan
    </a>
    @endif
    @if(auth()->user()->canAccessSurveyUploadModule())
    <a class="nav-link {{ request()->routeIs('survey.*') ? 'active' : '' }}" href="{{ route('survey.index') }}">
      <i class="icon-base ti tabler-upload d-block mb-1"></i> Survey
    </a>
    @endif
  </nav>
  @endauth
@endsection
