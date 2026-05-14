@extends('layouts/layoutMaster')

@section('title', 'Tambah Pengguna')

@section('content')
<div class="card" style="max-width: 32rem">
  <div class="card-body">
    <form method="POST" action="{{ route('admin.users.store') }}">
      @csrf
      <div class="mb-3">
        <label class="form-label">Nama</label>
        <input type="text" name="name" class="form-control" required value="{{ old('name') }}" />
      </div>
      <div class="mb-3">
        <label class="form-label">E-mel</label>
        <input type="email" name="email" class="form-control" required value="{{ old('email') }}" />
      </div>
      <div class="mb-3">
        <label class="form-label">Peranan</label>
        <select name="role" class="form-select" required>
          <option value="admin">Admin</option>
          <option value="surveyor">Surveyor</option>
          <option value="engineer">Engineer</option>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Kata laluan</label>
        <input type="password" name="password" class="form-control" required />
      </div>
      <div class="mb-3">
        <label class="form-label">Ulang kata laluan</label>
        <input type="password" name="password_confirmation" class="form-control" required />
      </div>
      <button type="submit" class="btn btn-primary">Simpan</button>
      <a href="{{ route('admin.users.index') }}" class="btn btn-label-secondary">Batal</a>
    </form>
  </div>
</div>
@endsection
