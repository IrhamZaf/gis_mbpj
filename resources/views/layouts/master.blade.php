{{--
|--------------------------------------------------------------------------
| layouts/master.blade.php
|--------------------------------------------------------------------------
| PLASBid-style master layout powered by the Vuexy template.
| Usage:  @extends('layouts.master')   then   @section('content') … @endsection
| Optional stacks:  @push('styles')  and  @push('scripts')
|--------------------------------------------------------------------------
--}}
@isset($pageConfigs)
  {!! Helper::updatePageConfig($pageConfigs) !!}
@endisset

@php
  use Illuminate\Support\Str;
  use App\Helpers\Helpers;

  $configData = Helper::appClasses();

  /* Display elements */
  $contentNavbar = $contentNavbar ?? true;
  $containerNav  = $containerNav ?? 'container-xxl';
  $isNavbar      = $isNavbar ?? true;
  $isMenu        = $isMenu ?? true;
  $isFlex        = $isFlex ?? false;
  $isFooter      = $isFooter ?? true;
  $customizerHidden = $customizerHidden ?? '';

  /* HTML Classes */
  $navbarDetached = 'navbar-detached';
  $menuFixed      = isset($configData['menuFixed']) ? $configData['menuFixed'] : '';
  if (isset($navbarType)) {
      $configData['navbarType'] = $navbarType;
  }
  $navbarType    = isset($configData['navbarType']) ? $configData['navbarType'] : '';
  $footerFixed   = isset($configData['footerFixed']) ? $configData['footerFixed'] : '';
  $menuCollapsed = isset($configData['menuCollapsed']) ? $configData['menuCollapsed'] : '';

  /* Content classes */
  $container = isset($configData['contentLayout']) && $configData['contentLayout'] === 'compact'
      ? 'container-xxl'
      : 'container-fluid';

  /* Front layout flag */
  $menuFixedMaster = $configData['layout'] === 'vertical'
      ? ($menuFixed ?? '')
      : ($configData['layout'] === 'front' ? '' : $configData['headerType']);
  $navbarTypeMaster = $configData['layout'] === 'vertical'
      ? $configData['navbarType']
      : ($configData['layout'] === 'front' ? 'layout-navbar-fixed' : '');
  $isFront = false;
  $isFrontStr = '';
  $contentLayout = isset($container) ? ($container === 'container-xxl' ? 'layout-compact' : 'layout-wide') : '';

  $isAdminLayout = !Str::contains($configData['layout'] ?? '', 'front');
  $skinName = $isAdminLayout ? $configData['skinName'] ?? 'default' : 'default';
  $semiDarkEnabled = $isAdminLayout && filter_var($configData['semiDark'] ?? false, FILTER_VALIDATE_BOOLEAN);

  $primaryColorCSS = '';
  if (isset($configData['color']) && $configData['color']) {
      $primaryColorCSS = Helpers::generatePrimaryColorCSS($configData['color']);
  }
@endphp

<!DOCTYPE html>
<html lang="{{ session()->get('locale') ?? app()->getLocale() }}"
  class="{{ $navbarTypeMaster ?? '' }} {{ $contentLayout ?? '' }} {{ $menuFixedMaster ?? '' }} {{ $menuCollapsed ?? '' }} {{ $footerFixed ?? '' }} {{ $customizerHidden ?? '' }}"
  dir="{{ $configData['textDirection'] }}" data-skin="{{ $skinName }}" data-assets-path="{{ asset('/assets') . '/' }}"
  data-base-url="{{ url('/') }}" data-framework="laravel" data-template="{{ $configData['layout'] }}-menu-template"
  data-bs-theme="{{ $configData['theme'] }}" @if ($isAdminLayout && $semiDarkEnabled) data-semidark-menu="true" @endif>

<head>
  <meta charset="utf-8" />
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>@yield('title', config('variables.templateName')) | {{ config('variables.templateSuffix') }}</title>
  <meta name="description" content="{{ config('variables.templateDescription') }}" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

  <!-- Include Styles -->
  @include('layouts/sections/styles' . $isFrontStr)

  @if ($primaryColorCSS &&
      (config('custom.custom.primaryColor') ||
          isset($_COOKIE['admin-primaryColor']) ||
          isset($_COOKIE['front-primaryColor'])))
    <style id="primary-color-style">
      {!! $primaryColorCSS !!}
    </style>
  @endif

  <!-- Include Scripts for customizer, helper, analytics, config -->
  @include('layouts/sections/scriptsIncludes' . $isFrontStr)

  <!-- Page specific styles (PLASBid pattern) -->
  @stack('styles')
  @yield('vendor-style')
  @yield('page-style')

  <!-- Livewire Styles -->
  @livewireStyles
</head>

<body>

  <!-- Layout wrapper -->
  <div class="layout-wrapper layout-content-navbar {{ $isMenu ? '' : 'layout-without-menu' }}">
    <div class="layout-container">

      {{-- ============================================================== --}}
      {{-- Sidebar (PLASBid-style component include) --}}
      {{-- ============================================================== --}}
      @if ($isMenu)
        @include('component.sidebar')
      @endif

      <!-- Layout page -->
      <div class="layout-page">

        {{-- ============================================================== --}}
        {{-- Topbar (PLASBid-style component include) --}}
        {{-- ============================================================== --}}
        @if ($isNavbar)
          @include('component.topbar')
        @endif

        <!-- Content wrapper -->
        <div class="content-wrapper">

          <!-- Content -->
          @if ($isFlex)
            <div class="{{ $container }} d-flex align-items-stretch flex-grow-1 p-0">
          @else
            <div class="{{ $container }} flex-grow-1 container-p-y">
          @endif

          {{ $slot }}

          </div>
          <!-- / Content -->

          {{-- ============================================================== --}}
          {{-- Footer (PLASBid-style component include) --}}
          {{-- ============================================================== --}}
          @if ($isFooter)
            @include('component.footer')
          @endif

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

  <!-- Include Scripts -->
  @include('layouts/sections/scripts' . $isFrontStr)

  <!-- Page specific scripts (PLASBid pattern) -->
  @stack('scripts')

  <!-- Livewire Scripts -->
  @livewireScripts

  <!-- Scripts that depend on Livewire (maps, wire hooks, etc.) -->
  @stack('scripts-after-livewire')
</body>

</html>
