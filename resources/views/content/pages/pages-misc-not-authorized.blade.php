@extends('layouts/blankLayout')
@section('title', 'Tidak dibenarkan')
@section('content')
<div class="container-xxl container-p-y text-center">
  <h4 class="mb-2">Tidak dibenarkan</h4>
  <p class="text-muted mb-3">Anda tidak mempunyai kebenaran untuk halaman ini.</p>
  <a href="{{ url('/') }}" class="btn btn-primary">Papan pemuka</a>
</div>
@endsection
