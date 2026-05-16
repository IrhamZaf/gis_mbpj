@extends('layouts/layoutMaster')

@section('title', 'Soalan lazim')

@section('content')
<div class="card" style="max-width: 48rem">
  <div class="card-body">
    <h5 class="mb-3">Soalan lazim (GIS MBPJ)</h5>
    <p class="mb-2"><strong>Bagaimana surveyor dilantik menghantar laporan?</strong><br>Gunakan menu <em>Hantaran surveyor dilantik → Upload Data</em> dan pautkan kepada no. insiden.</p>
    <p class="mb-2"><strong>Siapa boleh mencipta insiden?</strong><br>Pentadbir, jurutera, atau akaun surveyor dilantik yang diberi akses.</p>
    <p class="mb-3"><strong>Bagaimana jurutera menyemak fail?</strong><br>Menu <em>Engineer Review → Fail laporan surveyor</em> atau semakan pada halaman insiden.</p>
    <a href="{{ url('/') }}" class="btn btn-sm btn-label-secondary">Kembali ke papan pemuka</a>
  </div>
</div>
@endsection
