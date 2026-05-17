{{-- Standalone blank layout for Livewire full-page components (auth pages) --}}
@php
  use Illuminate\Support\Str;
  use App\Helpers\Helpers;

  $configData = Helper::appClasses();
  $customizerHidden = 'customizer-hide';

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
  class="{{ $customizerHidden }}"
  dir="{{ $configData['textDirection'] }}" data-skin="{{ $skinName }}"
  data-assets-path="{{ asset('/assets') . '/' }}"
  data-base-url="{{ url('/') }}" data-framework="laravel"
  data-template="{{ $configData['layout'] }}-menu-template"
  data-bs-theme="{{ $configData['theme'] }}">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>@yield('title', 'Log Masuk') | {{ config('variables.templateName') }}</title>
  <meta name="description" content="{{ config('variables.templateDescription') }}" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

  <!-- Styles -->
  @include('layouts/sections/styles')

  <!-- Page Auth SCSS -->
  @vite(['resources/assets/vendor/scss/pages/page-auth.scss'])

  @if ($primaryColorCSS)
    <style id="primary-color-style">{!! $primaryColorCSS !!}</style>
  @endif

  @include('layouts/sections/scriptsIncludes')

  @livewireStyles
</head>

<body>
  {{ $slot }}

  @include('layouts/sections/scripts')
  @livewireScripts
</body>
</html>
