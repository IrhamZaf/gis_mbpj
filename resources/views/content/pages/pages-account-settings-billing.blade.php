@extends('layouts/layoutMaster')

@section('title', 'Bil & pelan')

@section('content')
<div class="card" style="max-width: 42rem">
  <div class="card-body">
    <h5 class="mb-2">Tetapan bil &amp; pelan</h5>
    <p class="text-muted mb-3">
      Halaman contoh daripada tema asal. Aplikasi GIS MBPJ tidak menggunakan modul bil; gunakan menu utama untuk kerja harian.
    </p>
    <a href="{{ url('/') }}" class="btn btn-sm btn-label-secondary">Kembali ke papan pemuka</a>
    @if(auth()->check() && auth()->user()->isAdmin())
    <a href="{{ route('admin.settings') }}" class="btn btn-sm btn-primary ms-2">Tetapan sistem</a>
    @endif
  </div>
</div>
@endsection
