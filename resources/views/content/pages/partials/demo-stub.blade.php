{{-- Halaman stub untuk laluan demo tema Vuexy yang tidak digunakan oleh GIS MBPJ --}}
<div class="card" style="max-width: 42rem">
  <div class="card-body">
    <h5 class="mb-2">{{ $heading ?? $title ?? 'Halaman' }}</h5>
    <p class="text-muted mb-3">{{ $message ?? 'Halaman contoh tema asal. Gunakan menu GIS untuk kerja harian.' }}</p>
    <a href="{{ url('/') }}" class="btn btn-sm btn-label-secondary">Kembali ke papan pemuka</a>
    @if(auth()->check() && auth()->user()->isAdmin())
    <a href="{{ route('admin.settings') }}" class="btn btn-sm btn-primary ms-2">Tetapan sistem</a>
    @endif
  </div>
</div>
