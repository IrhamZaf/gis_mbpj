@php
  $logoMaxH = isset($height) ? (int) $height : 44;
  $logoMaxW = isset($width) ? (int) $width : 56;
@endphp

<img
  src="{{ asset('img/mbpj-logo.png') }}"
  alt="Logo Majlis Bandaraya Petaling Jaya (MBPJ)"
  class="gis-mbpj-brand-logo"
  width="{{ $logoMaxW }}"
  height="{{ $logoMaxH }}"
  decoding="async"
/>
