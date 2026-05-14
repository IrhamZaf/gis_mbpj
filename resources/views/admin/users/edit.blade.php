@extends('layouts/layoutMaster')

@section('title', 'Edit Pengguna')

@section('content')
<div class="card" style="max-width: 32rem">
  <div class="card-body">
    <form method="POST" action="{{ route('admin.users.update', $user) }}">
      @csrf
      @method('PUT')
      <div class="mb-3">
        <label class="form-label">Nama</label>
        <input type="text" name="name" class="form-control" required value="{{ old('name', $user->name) }}" />
      </div>
      <div class="mb-3">
        <label class="form-label">E-mel</label>
        <input type="email" name="email" class="form-control" required value="{{ old('email', $user->email) }}" />
      </div>
      <div class="mb-3">
        <label class="form-label">Peranan</label>
        <select name="role" class="form-select" required>
          @foreach (['admin','surveyor','engineer'] as $r)
          <option value="{{ $r }}" @selected($user->role === $r)>{{ $r }}</option>
          @endforeach
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Kata laluan baharu (kosongkan jika tidak berubah)</label>
        <input type="password" name="password" class="form-control" />
      </div>
      <div class="mb-3">
        <label class="form-label">Ulang kata laluan</label>
        <input type="password" name="password_confirmation" class="form-control" />
      </div>
      <button type="submit" class="btn btn-primary">Kemas kini</button>
      <a href="{{ route('admin.users.index') }}" class="btn btn-label-secondary">Batal</a>
    </form>
  </div>
</div>
@endsection
