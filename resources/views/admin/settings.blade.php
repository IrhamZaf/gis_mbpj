@extends('layouts/layoutMaster')

@section('title', 'Tetapan')

@section('content')
<div class="card" style="max-width: 40rem">
  <div class="card-body">
    <h5 class="mb-3">Tetapan aplikasi</h5>
    <p class="text-muted">Nama sistem: <strong>{{ config('variables.templateName') }}</strong></p>
    <p class="text-muted">Warna utama: <strong>{{ config('custom.custom.primaryColor') }}</strong></p>
    <p class="mb-0 small">Untuk konfigurasi lanjut (e-mel, pelayan peta), kemas kini fail <code>.env</code> dan <code>config/</code>.</p>
  </div>
</div>
@endsection
