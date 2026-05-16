@extends('layouts/layoutMaster')

@section('title', 'Profil saya')

@section('content')
@php $user = auth()->user(); @endphp
<div class="row g-4">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Profil pengguna</h5>
      </div>
      <div class="card-body">
        @if($user)
        <dl class="row mb-0">
          <dt class="col-sm-4">Nama</dt>
          <dd class="col-sm-8">{{ $user->name }}</dd>
          <dt class="col-sm-4">E-mel</dt>
          <dd class="col-sm-8">{{ $user->email }}</dd>
          <dt class="col-sm-4">Peranan</dt>
          <dd class="col-sm-8">{{ $user->roleLabel() }}</dd>
        </dl>
        @else
        <p class="text-muted mb-0">Sila log masuk.</p>
        @endif
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card">
      <div class="card-body">
        <a href="{{ url('/') }}" class="btn btn-label-secondary w-100 mb-2">Papan pemuka</a>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="btn btn-outline-danger w-100">Log keluar</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
