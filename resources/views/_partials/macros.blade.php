@php
  $width = $width ?? '40';
  $height = $height ?? '40';
@endphp

<img src="{{ asset('assets/img/branding/mbsj-logo.png') }}"
  alt="{{ config('variables.templateName') }}"
  width="{{ $width }}"
  height="{{ $height }}"
  class="app-brand-logo-img"
  style="object-fit:contain;display:block;" />
